<?php

use App\Http\Controllers\Api\Auth\UserController;
use Illuminate\Support\Facades\Route;

// Public Controllers
use App\Http\Controllers\Api\Auth\AdminController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\DriversController;
use App\Http\Controllers\Api\MenusController;
use App\Http\Controllers\Api\SubCategoriesController;

// User Controllers
use App\Http\Controllers\Api\User\UserAddressesController;
use App\Http\Controllers\Api\User\UserInfosController;
use App\Http\Controllers\Api\User\UserOrdersController;
use App\Http\Controllers\Api\User\UserOrderTypesController;
use App\Http\Controllers\Api\User\UserPaymentsController;
use App\Http\Controllers\Api\User\UserPaymentTypesController;
use App\Http\Controllers\Api\User\UserPromotionsController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\AdminAddonsController;
use App\Http\Controllers\Api\Admin\AdminCategoriesController;
use App\Http\Controllers\Api\Admin\AdminDriversController;
use App\Http\Controllers\Api\Admin\AdminMenuAddonsController;
use App\Http\Controllers\Api\Admin\AdminMenuPromotionsController;
use App\Http\Controllers\Api\Admin\AdminMenusController;
use App\Http\Controllers\Api\Admin\AdminOrderItemsController;
use App\Http\Controllers\Api\Admin\AdminOrdersController;
use App\Http\Controllers\Api\Admin\AdminOrderTypesController;
use App\Http\Controllers\Api\Admin\AdminPaymentsController;
use App\Http\Controllers\Api\Admin\AdminPaymentTypesController;
use App\Http\Controllers\Api\Admin\AdminPromotionsController;
use App\Http\Controllers\Api\Admin\AdminSubCategoriesController;
use App\Http\Controllers\Api\Admin\AdminUsersController;
use App\Http\Controllers\Api\Admin\AdminVariantsController;
use App\Http\Controllers\Api\Admin\AdminStagesController;
use App\Http\Controllers\Api\Admin\AdminStatusesController;

Route::prefix('v1')->group(function () {


    // =======================
    // PUBLIC ROUTES
    // =======================
    Route::get('/categories', [CategoriesController::class, 'index']);
    Route::get('/subcategories', [SubCategoriesController::class, 'index']);
    Route::get('/menus', [MenusController::class, 'index']);
    Route::get('/menus/{id}', [MenusController::class, 'show']);
    Route::get('/menus/{id}/related', [MenusController::class, 'show']);
    Route::get('/drivers', [DriversController::class, 'index']);
    Route::get('/drivers/{id}', [DriversController::class, 'show']);

    // =======================
    // USER ROUTES (LOGIN REQUIRED)
    // =======================
    Route::post('user/login', [UserController::class,'login']);
    Route::post('user/register', [UserController::class, 'register']);


    Route::middleware(['auth:sanctum', 'user'])->prefix('user')->as('user.')->group(function () {

        Route::post('/logout', [UserController::class, 'logout']);


        // Profile
        Route::get('/profile', [UserInfosController::class, 'show']);
        Route::post('/profile', [UserInfosController::class, 'store']);
        Route::put('/profile', [UserInfosController::class, 'update']);
        Route::patch('/profile/avatar', [UserInfosController::class, 'updateAvatar']);
        Route::patch('/profile/notification', [UserInfosController::class, 'notifyUpdate']);

        // Orders & Addresses
        Route::resource('/orders', UserOrdersController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('/addresses', UserAddressesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::patch('/addresses/{id}/default', [UserAddressesController::class,'setDefault']);

        // Order types, promotions, payments
        Route::get('/ordertypes', [UserOrderTypesController::class, 'index']);
        Route::get('/promotions', [UserPromotionsController::class, 'index']);
        Route::post('/payments', [UserPaymentsController::class, 'store']);
        Route::get('/payment-types', [UserPaymentTypesController::class, 'index']);
    });

    // =======================
    // ADMIN ROUTES (ADMIN ONLY)
    // =======================
    Route::post('admin/login', [AdminController::class,'login']);


    Route::middleware(['auth:sanctum','admin'])->prefix('admin')->as('admin.')->group(function () {

        // CRUD Resources
        Route::resource('addons', AdminAddonsController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('categories', AdminCategoriesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('subcategories', AdminSubCategoriesController::class);
        Route::resource('menus', AdminMenusController::class,['only' =>[ 'index', 'store', 'update', 'show']]);
        Route::resource('drivers', AdminDriversController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('order-types', AdminOrderTypesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('orders', AdminOrdersController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::patch('orders/{id}/stage', [AdminOrdersController::class, 'stage']);

        // Order Items (fixed path)
        Route::prefix('order-items')->group(function () {
            Route::get('/', [AdminOrderItemsController::class, 'index']); // admin/order-items
            Route::get('/{id}', [AdminOrderItemsController::class, 'show']);
            Route::patch('/{id}', [AdminOrderItemsController::class, 'update']);
            Route::delete('/{id}', [AdminOrderItemsController::class, 'destroy']);
        });

        Route::resource('payments', AdminPaymentsController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('payment-types', AdminPaymentTypesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('promotions', AdminPromotionsController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('menu-promotions', AdminMenuPromotionsController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('menu-addons', AdminMenuAddonsController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('stages', AdminStagesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('statuses', AdminStatusesController::class,['only' => ['index', 'store', 'update', 'show']]);
        Route::resource('variants', AdminVariantsController::class,['only' => ['index', 'store', 'update', 'show']]);

        // Users management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUsersController::class, 'index']);
            Route::get('/{id}', [AdminUsersController::class, 'show']);
            Route::patch('/{id}/block', [AdminUsersController::class, 'block']);
            Route::patch('/{id}/unblock', [AdminUsersController::class, 'unblock']);
        });

        // Logout
        Route::post('/logout', [AdminController::class,'logout']);
    });
});
