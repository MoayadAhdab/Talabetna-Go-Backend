<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('/register', [
            AuthController::class,
            'register',
        ]);

        Route::post('/login', [
            AuthController::class,
            'login',
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    */

    Route::get('/business-types', [
        CatalogController::class,
        'businessTypes',
    ]);

    Route::get('/businesses', [
        CatalogController::class,
        'businesses',
    ]);

    Route::get('/businesses/{business}', [
        CatalogController::class,
        'business',
    ]);

    Route::get('/businesses/{business}/categories', [
        CatalogController::class,
        'categories',
    ]);

    Route::get('/businesses/{business}/products', [
        CatalogController::class,
        'products',
    ]);

    Route::get('/products/{product}', [
        CatalogController::class,
        'product',
    ]);
    Route::get('/banners/top', [
    \App\Http\Controllers\Api\V1\CatalogController::class,
    'topBanners',
]);
Route::get('/merchants/featured', [
    \App\Http\Controllers\Api\V1\CatalogController::class,
    'featuredMerchants',
]);
    /*
    |--------------------------------------------------------------------------
    | Authenticated Customer
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/auth/logout', [
            AuthController::class,
            'logout',
        ]);
    });
});
