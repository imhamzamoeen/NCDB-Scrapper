<?php

namespace App\Console\Commands;

use App\Classes\Crawler;
use Exception;
use Illuminate\Console\Command;

class scrapHalfMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-ncm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command Scraps the data from the ncm url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $index = 0;
        $chunkSize = 1000;
        try {
            $this->info("Starting Crawler to get data ");
            $this->newLine(2);
            $object = (new Crawler())->crawlList();
            $bar = $this->output->createProgressBar(count($object->showlist()));
            $this->info("List Scrapped Successfully.. total enteries found: " . count($object->showlist()));

            $bar->start();
            $totalIndex = ceil(count($object->showlist()) / $chunkSize);
            for ($index; $index < $totalIndex; $index++) {
                rescue(function () use ($chunkSize, $index, $bar, $object) {
                    $bar->advance($index * $chunkSize);
                    $object = $object->CrawlNCDBData(skip: $index, take: $chunkSize);
                    $this->newLine(2);
                    $this->info("Saving  Details ");

                    $object?->storeDataForManufacture()?->storeScrapedData();
                }, null);
            }

            $bar->finish();
            return 0;
        } catch (Exception $e) {
            $this->error("Exception Found in file :  " . __FILE__ . ", Method: " . __FUNCTION__ . ", Message:" . $e->getMessage() . ", Line:" . $e->getLine());
            return 1;
        }
    }
}
