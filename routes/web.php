<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function (){
    exit();
});


Route::name('pay')->group(function(){
    Route::post('submitOrder', [\App\Http\Controllers\PayController::class, 'index']);
    Route::post('checkOrder', [\App\Http\Controllers\PayController::class, 'check']);
    Route::get('testOrder', [\App\Http\Controllers\PayController::class, 'test']);
    Route::post('testOrder', [\App\Http\Controllers\PayController::class, 'test']);
    Route::any('testNotify', [\App\Http\Controllers\PayController::class, 'testNotify'] );


    Route::get('show/{order}', [\App\Http\Controllers\ShowController::class, 'show']);
    Route::get('order/{order}', [\App\Http\Controllers\ShowController::class, 'order']);
    Route::get('jumpPay/{order}', [\App\Http\Controllers\ShowController::class, 'jump']);
    Route::post('check_status', [\App\Http\Controllers\ShowController::class, 'check']);
    Route::get('wxNotify', [\App\Http\Controllers\ShowController::class, 'notify']);
    Route::post('wxNotify', [\App\Http\Controllers\ShowController::class, 'notify']);



    Route::get("union_order/{order}", [\App\Http\Controllers\UnionController::class, 'order'] );
    Route::get('union_show/{order}', [\App\Http\Controllers\UnionController::class, 'show']);
    Route::get('union_pay/{order}', [\App\Http\Controllers\UnionController::class, 'jump']);


    Route::get('unionNotify', [\App\Http\Controllers\UnionController::class, 'notify']);
    Route::post('unionNotify', [\App\Http\Controllers\UnionController::class, 'notify']);

    Route::get('wxCheck', [\App\Http\Controllers\ShowController::class, 'checkNotify']);


    Route::get('unionTest', [\App\Http\Controllers\UnionController::class, 'test']);


    Route::get('autoFail', [\App\Http\Controllers\KongController::class, 'autoFail']);


    Route::any('ceshi', [\App\Http\Controllers\TestController::class, 'show']);


    Route::get('clearAmount', [\App\Http\Controllers\TestController::class, 'clearAmount']);

    Route::get('loadShow/{order}', [\App\Http\Controllers\LoadController::class, 'show']);
    Route::any('loadPay/{order}', [\App\Http\Controllers\LoadController::class, 'pay']);
    Route::get("loadGet/{order}", [\App\Http\Controllers\LoadController::class, 'status']);
    Route::any('loadNotify', [\App\Http\Controllers\LoadController::class, 'notify']);
});


Route::name('account')->group(function(){
    Route::post('createAccount', [\App\Http\Controllers\AccountController::class, 'create']);
    Route::post('checkGroup', [\App\Http\Controllers\AccountController::class, 'check']);
    Route::get('createTest', [\App\Http\Controllers\AccountController::class, 'test']);
});


//投诉
Route::name('complaint')->group(function(){
    Route::get('complaintRun', [\App\Http\Controllers\ComplaintController::class, 'run']);
    Route::get('complaintReply', [\App\Http\Controllers\ComplaintController::class, 'reply']);
    Route::get('complaintApprove', [\App\Http\Controllers\ComplaintController::class, 'approve']);
    Route::get('complaintRefund', [\App\Http\Controllers\ComplaintController::class, 'refund']);
    Route::get('complaintDone', [\App\Http\Controllers\ComplaintController::class, 'done']);
});

Route::any('reqNotify', [\App\Http\Controllers\TestController::class, 'req']);
Route::any('tradeNotify', [\App\Http\Controllers\TestController::class, 'trade']);



Route::any('zfNotify', [\App\Http\Controllers\KaiController::class, 'notify']);



Route::get('yunShow/{order}', [\App\Http\Controllers\KaiController::class, 'show']);

