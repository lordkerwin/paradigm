<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('cms')->group(function () {
    Route::post('login', [\App\Http\Controllers\CMS\AuthController::class, 'login'])->name('cms.auth.login');

    // The below routes require you to be logged in, authenticated with Sanctum and have the admin
    Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
        Route::resource('shops', \App\Http\Controllers\CMS\ShopController::class);
        Route::resource('categories', \App\Http\Controllers\CMS\CategoryController::class);
        Route::resource('products', \App\Http\Controllers\CMS\ProductController::class);
        Route::post('products/{product}/attach-to-category',
            [\App\Http\Controllers\CMS\ProductController::class, 'attachToCategory'])->name('products.attach-to-category');
        Route::post('products/{product}/attach-to-shop',
            [\App\Http\Controllers\CMS\ProductController::class, 'attachToShop'])->name('products.attach-to-shop');

        Route::post('categories/{category}/attach-to-shop',
            [\App\Http\Controllers\CMS\CategoryController::class, 'attachToShop'])->name('categories.attach-to-shop');
    });
});
