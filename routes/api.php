<?php

use App\Http\Controllers\ShopController;
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

        Route::resource('shops', ShopController::class);
    });
});
