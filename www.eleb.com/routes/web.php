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
    Route::get('shops','ShopsController@index');//商家列表
    Route::get('shop','ShopsController@shop');//指定商家
    Route::get('sms','ShopsController@sms');//短信
    Route::post('regist','ShopsController@regist');//注册
    Route::post('login','ShopsController@login');//登录

    Route::get('addressList','ShopsController@addressList');//添加地址接口
    Route::post('addAddress','ShopsController@addAddress');//保存新增地址接口
    Route::get('address','ShopsController@address');//指定地址接口
    Route::post('editAddress','ShopsController@editAddress');//保存修改地址接口

    Route::post('addCart','ShopsController@addCart');//保存购物车接口
    Route::get('cart','ShopsController@cart');//获取购物车数据接口

    Route::post('addorder','ShopsController@addorder');//添加订单接口
    Route::get('order','ShopsController@order');//获得指定订单接口
    Route::get('orderList','ShopsController@orderList');//获得订单列表接口

    Route::post('changePassword','ShopsController@changePassword');//修改密码接口
    Route::post('forgetPassword','ShopsController@forgetPassword');//忘记密码接口


});




