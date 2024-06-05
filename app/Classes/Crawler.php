<?php

namespace App\Classes;

use App\Http\Controllers\NcdbScrapperController;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\Manufacturer;
use App\Models\ScrappedData;
use Goutte\Client;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spekulatius\PHPScraper\PHPScraper;
use Illuminate\Support\Str;


use function App\Helpers\toSnakeCase;

class Crawler
{

  protected $model =   PHPScraper::class;

  public const  url = 'https://www.ncm-pcdb.org.uk/sap/pcdbsearchresults.jsp?pid=26';
  public const  Baseurl = 'https://www.ncm-pcdb.org.uk/sap/';

  protected $object;

  protected $dataCollection;  // each boiler data;

  protected $listsCollection;   // boilder name and urls
  protected $Client;
  public function __construct(...$params)
  {
    $this->dataCollection = collect();
    $this->listsCollection = collect();
    $this->Client = new Client(HttpClient::create(['timeout' => 300]));

    $this->object = resolve($this->model, $params);
  }

  /**
   * Crawl the list of boilers from the given URL
   *
   * @param string $url
   * @return Crawler|null
   */
  public function crawlList(?string $url = self::url): ?Crawler
  {
    try {
      $crawler = $this->Client->request('GET', $url);
      $crawler->filter('table tbody > tr')->each(function ($node) {
        $this->listsCollection->push([
          'name' => $node->filter('td:nth-child(1)')->innertext(),
          'url' => self::Baseurl . $node->filter('td:last-child > a')->attr('href'),
        ]);
      });
      return $this;
    } catch (Exception $e) {
      echo "Exception Found in file :  " . __FILE__ . ", Method: " . __FUNCTION__ . ", Message:" . $e->getMessage() . ", Line:" . $e->getLine();
      return null;
    }
  }

  public function showlist(): ?Collection
  {
    return $this->listsCollection;
  }

  public function showNCDBData(): ?Collection
  {
    return $this->dataCollection;
  }

  public function CrawlNCDBData(?string $url = self::url,int $skip=0,int $take=1000)
  {
    try {
      $data = [];
      foreach ($this->listsCollection->skip($skip)->take($take) as $list) {
        if (!($list['url'] ?? false))
          continue;
        $crawler = $this->Client->request('GET', $list['url']);
        $crawler->filter('table > tr')->each(function ($node) use (&$data,) {
          try {
            if ($node->filter('*:first-child')->innertext('null') == "\u{A0}") {
              // nothing to do here since it is an empty table tr 
            } elseif ($node->filter('*:last-child')->innertext('null') != "\u{A0}" &&  $node->children()->count() > 2) {

              // parent k seocnd child main serires s 

              if ($node->filter('*:first-child')->attr('class') == 'bg') {
                //skip this tr as we have already fetched values in other iteration 
              } else {
                $node->children()->each(function ($childnode) use (&$data) {
                  $childNumber = count($childnode->previousAll());  //current child number going on

                  $data[Str::snake(preg_replace('/[^\p{L}\p{N}\s]/u', '', $childnode->innertext('null')))] =  ($childnode->closest('tr')->nextAll()->first()->children()->eq($childNumber)->innertext('null')  ?: null);
                });
              }
            } else {

              // is main s hm n sirf 1 sath agly wala ka krna means first one is heading and the second is value               
              $data[Str::snake(preg_replace('/[^\p{L}\p{N}\s]/u', '', $node->filter('*:first-child')->innertext('null')))] =  ($node->filter(':nth-child(2)')->innertext('null') ?: null);
            }
          } catch (Exception $e) {
            echo "ecception caught at :" . $e->getMessage();
          }
        });
        $this->dataCollection->push($data);
      }
      return $this;
    } catch (Exception $e) {
      echo "Exception Found in file :  " . __FILE__ . ", Method: " . __FUNCTION__ . ", Message:" . $e->getMessage() . ", Line:" . $e->getLine();
      return null;
    }
  }


  public function storeDataForManufacture()
  {
    try {

      $uniqueManufacturers = $this->dataCollection->unique('original_manufacturer_name')->map(function ($item) {
        return [
          'original_manufacturer_name' => $item['original_manufacturer_name'],
          'manufacturer_address' => $item['manufacturer_address'],
          'manufacturer_phone' => $item['manufacturer_phone'],
          'manufacturer_website' => $item['manufacturer_website'],
        ];
      })->toArray();
      Manufacturer::upsert($uniqueManufacturers, ['original_manufacturer_name',], ['manufacturer_website', 'manufacturer_phone', 'manufacturer_address']);
      return $this;
    } catch (Exception $e) {
      echo "Exception Found in file :  " . __FILE__ . ", Method: " . __FUNCTION__ . ", Message:" . $e->getMessage() . ", Line:" . $e->getLine();
      return null;
    }
  }


  public function storeScrapedData()
  {
    try {
    $AllManufacturers =  Manufacturer::pluck('id','original_manufacturer_name');
      // Define the fields to be ignored from extra_data
      $ignoredFields = ['s_a_p_winter_seasonal_efficiency','index_number', 's_a_p_summer_seasonal_efficiency', 'fuel', 'main_type', 'condensing', 'boiler_i_d',];
      // Initialize an array to store the schema definition for each item
      $StoringData = [];


      // Iterate over each item in the collection
      $this->dataCollection->each(function ($item) use ($ignoredFields, &$StoringData,$AllManufacturers) {
        // Remove ignored fields from the item
        $filteredItem = Arr::only($item,$ignoredFields);

        // Add extra_data containing the remaining fields as JSON
        $filteredItem['extra_data'] = json_encode(Arr::except($item,$ignoredFields));

        $filteredItem['Model_data'] = Arr::join(Arr::only($item,['brand','model_name','model_qualifier']),' ');
        $isCondesing ??= Str::lower($item['condensing'] ?? 'no');

        $filteredItem['condensing'] =  $isCondesing == 'yes'; 
        
        // manufacturer id 
        $filteredItem['manufacturer_id'] = $AllManufacturers[Arr::except($item,$ignoredFields)['original_manufacturer_name']] ?? null;
        // Add the filtered item to the schema array
        $StoringData[] = $filteredItem;
      });
      ScrappedData::upsert($StoringData,['index_number','manufacturer_id'],$ignoredFields);
      return $this;
    } catch (Exception $e) {
      echo "Exception Found in file :  " . __FILE__ . ", Method: " . __FUNCTION__ . ", Message:" . $e->getMessage() . ", Line:" . $e->getLine();
      return null;
    }
  }
}
