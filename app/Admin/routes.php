<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('user', \App\Admin\Controllers\UserController::class);
    $router->resource('user_channel', \App\Admin\Controllers\UserChannelController::class);
    $router->resource('channel', \App\Admin\Controllers\ChannelController::class);
    $router->resource('channel_account', \App\Admin\Controllers\ChannelAccountController::class);
    $router->resource('order', \App\Admin\Controllers\OrderController::class);
    $router->resource('withdraw', \App\Admin\Controllers\WithdrawController::class);
    $router->resource('complaint', \App\Admin\Controllers\ComplaintController::class);
    $router->resource('complaint_list', \App\Admin\Controllers\ComplaintListController::class);

    $router->resource('black', \App\Admin\Controllers\BlackController::class);
});
