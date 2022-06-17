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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/error', function () {
    return view('error');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('install', [\App\Http\Controllers\BigcommerceController::class, 'install']);

    Route::get('load', [\App\Http\Controllers\BigcommerceController::class, 'load']);

    Route::get('uninstall', [\App\Http\Controllers\BigcommerceController::class, 'uninstall']);

    Route::get('remove-user', function () {
        echo 'remove-user';
        return app()->version();
    });
});

Route::any('/bc-api/{endpoint}', [\App\Http\Controllers\BigcommerceController::class, 'proxyBigCommerceAPIRequest'])
    ->where('endpoint', 'v2\/.*|v3\/.*');

Route::middleware(['auth.bc'])->group(function() {
    Route::get('stores/{storeHash}', [\App\Http\Controllers\OverviewController::class, 'index']);
    Route::get('stores/{storeHash}/products', [\App\Http\Controllers\ProductController::class, 'index']);
    Route::get('stores/{storeHash}/products/{id}', [\App\Http\Controllers\ProductController::class, 'edit']);
});
