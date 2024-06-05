<?php

use App\Classes\Crawler;
use Firebase\JWT\Key;
use App\Http\Controllers\NcdbScrapperController;
use App\Models\Manufacturer;
use App\Models\ScrappedData;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {

    
      return "App Working";

});

Route::get('middleware-test',function(Request $request){
 return "reached";
});