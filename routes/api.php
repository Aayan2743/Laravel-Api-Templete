<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\demoController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::POST('/login',[AuthController::class,'login']);
Route::POST('/sendMessage',[AuthController::class,'sendMessage']);
Route::POST('/sendMessageWithCurl',[AuthController::class,'sendMessageWithCurl']);
Route::POST('/verifyOtp',[AuthController::class,'verifyOtp']);




// Route::get('/list',[AuthController::class,'list']);

Route::group(['middleware'=>'jwt.verify'],function($routes){

    Route::get('/list',[AuthController::class,'list']);
    Route::POST('/register',[AuthController::class,'register']);
    Route::get('/get_data/{id}',[AuthController::class,'get_data']);
    Route::post('/update_data',[AuthController::class,'update_data']);


    //     Route::post('/profile',[ApiUserController::class,'profile']);
//  Route::post('/refresh',[ApiUserController::class,'refresh']);
//  Route::post('/logout',[ApiUserController::class,'logout']);


});
