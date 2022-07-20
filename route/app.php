<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::group('api/:version/token', function() {
    Route::post('/user', 'api/:version.TokenController/getToken');
    Route::post('/app', 'api/:version.TokenController/getAppToken');
    Route::post('/verify', 'api/:version.TokenController/verifyToken');
});

Route::get('api/:version/banner/:id', 'api/:version.BannerController/getBanner');

Route::group('api/:version/theme', function() {
    Route::get('', 'api/:version.ThemeController/getSimpleList');
    Route::get('/:id', 'api/:version.ThemeController/getComplexOne');
    Route::post(':t_id/product/:p_id', 'api/:version.ThemeController/addThemeProduct');
    Route::delete(':t_id/product/:p_id', 'api/:version.ThemeController/deleteThemeProduct');
});

Route::group('api/:version/product', function() {
    Route::post('', 'api/:version.ProductController/createOne');
    Route::delete('/:id', 'api/:version.ProductController/deleteOne');
    Route::get('/by_category/paginate', 'api/:version.ProductController/getByCategory');
    Route::get('/by_category', 'api/:version.ProductController/getAllInCategory');
    Route::get('/:id', 'api/:version.ProductController/getOne')->pattern(['id'=>'\d+']);
    Route::get('/recent', 'api/:version.ProductController/getRecent');
});

Route::get('api/:version/category', 'api/:version.CategoryController/getCategories');
Route::get('api/:version/category/all', 'api/:version.CategoryController/getAllCategories');

Route::group('api/:version/order', function() {
    Route::post('', 'api/:version.OrderController/placeOrder');
    Route::get('/:id', 'api/:version.OrderController/getDetail')->pattern(['id'=>'\d+']);
    Route::put('/delivery', 'api/:version.OrderController/delivery');
    Route::get('/by_user', 'api/:version.OrderController/getSummaryByUser');
    Route::get('/paginate', 'api/:version.OrderController/getSummary');
});

Route::group('api/:version/pay', function() {
    Route::post('/pre_order', 'api/:version.PayController/getPreOrder');
    Route::post('/notify', 'api/:version.PayController/receiveNotify');
    Route::post('/re_notify', 'api/:version.PayController/redirectNotify');
    Route::post('/concurrency', 'api/:version.PayController/notifyConcurrency');
});

Route::post('api/:version/address', 'api/:version.AddressController/createOrUpdateAddress');
Route::get('api/:version/address', 'api/:version.AddressController/getUserAddress');
