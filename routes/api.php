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

    Route::post('/businesses/details', [
    CatalogController::class,
    'businessDetailsFromBody',
]);


    /*
    | Mobile body-based compatibility routes
    |----------------------------------------
    | These POST routes accept IDs in a JSON body for the Flutter client.
    | The existing REST-style GET routes above remain supported.
    */
    Route::post('/catalog/categories', [
        CatalogController::class,
        'categoriesFromBody',
    ]);

    Route::post('/catalog/products', [
        CatalogController::class,
        'productsFromBody',
    ]);

    Route::post('/catalog/product-details', [
        CatalogController::class,
        'productFromBody',
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
            Route::get('/merchants/{business}/categories', [
        CatalogController::class,
        'categories',
    ]);

    Route::get(
        '/merchants/{business}/categories/{category}/products',
        [
            CatalogController::class,
            'categoryProducts',
        ]
    );
    });
});
