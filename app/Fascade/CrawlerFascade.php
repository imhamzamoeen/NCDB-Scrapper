<?php
namespace App\Fascade;

use Illuminate\Support\Facades\Facade;

class CrawlerFascade extends Facade
{
     protected static function getFacadeAccessor()
     {
          return 'crawler';
     }
}