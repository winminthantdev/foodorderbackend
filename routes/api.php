<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\AdminAuthController;


use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\StatusesController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER AUTH (OAuth + JWT)
    |--------------------------------------------------------------------------
    */
    Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect']);
    Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    /*
    |--------------------------------------------------------------------------
    | USER PROTECTED ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile/update', [ProfileController::class, 'update']);
        Route::post('profile/password', [ProfileController::class, 'changePassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN LOGIN ONLY (NO REGISTER)
    |--------------------------------------------------------------------------
    */
    Route::post('admin/login', [AdminAuthController::class, 'login']);
    Route::post('admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth:admin');

    /*
    |--------------------------------------------------------------------------
    | ADMIN PROTECTED ROUTES (ONLY ADMIN CAN UPDATE PRODUCTS)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:admin')->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    Route::resource('/statuses', StatusesController::class);

    /*
    |--------------------------------------------------------------------------
    | PUBLIC ROUTES (NO AUTH NEED)
    |--------------------------------------------------------------------------
    */



});
