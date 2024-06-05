<?php

namespace App\Providers;

use App\Classes\Crawler;
use Illuminate\Support\ServiceProvider;
use Spekulatius\PHPScraper\PHPScraper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // $this->app->bind(PHPScraper::class, function ($app) {
        //     return new PHPScraper();
        // });

        $this->app->bind('crawler',function(){
            return new Crawler();
       });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
