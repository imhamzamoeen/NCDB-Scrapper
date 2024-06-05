<?php

namespace App\Http\Controllers;

use App\Classes\Crawler as ClassesCrawler;
use App\Models\Manufacturer;
use App\Models\ScrappedData;
use App\trait\MessageTrait;
use Illuminate\Http\Request;
use Crawler;
use Exception;
use Illuminate\Support\Arr;

class NcdbScrapperController extends Controller
{

    use MessageTrait;
    public const url = 'https://www.ncm-pcdb.org.uk/sap/pcdbsearchresults.jsp?pid=26';
    /**
     * Handle the incoming request.
     */
    public function getData(Request $request)
    {
        try {
            $query = ScrappedData::query();
            $query->when($request->get('Model_data'), function ($query) use ($request) {
                $query->where('Model_data', 'like', '%' . $request->Model_data . '%');
            });
            $query->when($request->get('condensing'), function ($query) use ($request) {
                $query->where('condensing', $request->condensing);
            });
            $query->when($request->get('manufacturer'), function ($query) use ($request) {
                // later on if it needs manufacturer details then we can do relationship query as well 
                $data = Manufacturer::pluck('id', 'original_manufacturer_name')->toArray();
                $allowedManufacturers  = [];
                $filteredArray =  Arr::where($data, function ($value, $key) use ($request, &$allowedManufacturers) {
                    return in_array($key, $request->get('manufacturer', []));
                });
                $allowedManufacturers =  array_values($filteredArray);

                $query->wherein('manufacturer_id', $allowedManufacturers);
            });


            return $this->successResponse(data: $query->paginate($request->get('per_page',10), $request->get('specific_columns',['*']), 'page', $request->get('page_number',1)));
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }


    public function getManufacturer(Request $request)
    {
        try {
            return $this->successResponse(data: Manufacturer::all());
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
