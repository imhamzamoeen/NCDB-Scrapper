<?php

use App\Http\Controllers\JWTTokenController;
use App\Http\Controllers\NcdbScrapperController;
use App\Models\ScrappedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('GetToken',[JWTTokenController::class,'generateToken'])->name('generate_token');

Route::get('get-data',function(){
   return ScrappedData::take(10)->get();
});

Route::get('getData',[NcdbScrapperController::class,'getData'])->name('get_data');
Route::get('get-manufacturer',[NcdbScrapperController::class,'getManufacturer'])->name('get_manufacturer');
