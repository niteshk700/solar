<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or the Application bootstrap
| and will be assigned the "api" middleware group. Make something great!
|
*/

Route::post('/weather-data', [ApiController::class, 'storeWeatherData']);
Route::get('/device/{id}/latest', [ApiController::class, 'latest']);
Route::get('/device/{id}/history', [ApiController::class, 'history']);
