<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::prefix('api')->group(function(){
//    Route::get('shops',function(){
//
//    });
//});
Route::prefix('api')->group(function(){
    Route::get('shops','ShopsController@index');
    Route::get('shop','ShopsController@shop');
    Route::get('sms','ShopsController@sms');//短信
    Route::post('regist','ShopsController@regist');//注册
    Route::post('login','ShopsController@login');//登录
});




