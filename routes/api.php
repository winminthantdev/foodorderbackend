<?php

// Public Controllers
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
use App\Http\Controllers\Api\Admin\AdminUserInfosController;
use App\Http\Controllers\Api\Admin\AdminUsersController;
use App\Http\Controllers\Api\Admin\AdminVariantsController;
use App\Http\Controllers\Api\Admin\AdminStagesController;
use App\Http\Controllers\Api\Admin\AdminStatusesController;

Route::prefix('v1')->group(function () {

    // PUBLIC ROUTES
    Route::prefix('public')->group(function () {
        Route::get('/categories', [CategoriesController::class, 'index']);
        Route::get('/subcategories', [SubCategoriesController::class, 'index']);
        Route::get('/menus', [MenusController::class, 'index']);
        Route::get('/menus/{id}', [MenusController::class, 'show']);
        Route::get('/drivers', [DriversController::class, 'index']);
        Route::get('/drivers/{id}', [DriversController::class, 'show']);
    });

    // USER ROUTES
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {

        Route::get('/profile', [UserInfosController::class, 'show']);
        Route::put('/profile', [UserInfosController::class, 'update']);

        Route::get('/order-types', [UserOrderTypesController::class, 'index']);

        Route::resource('/orders', UserOrdersController::class);
        Route::resource('/addresses', UserAddressesController::class);

        Route::get('/promotions', [UserPromotionsController::class, 'index']);

        Route::post('/payments', [UserPaymentsController::class, 'store']);

        Route::get('/payment-types', [UserPaymentTypesController::class, 'index']);

    });

    // ADMIN ROUTES
    Route::prefix('admin')->group(function () {
        Route::resource('addons', AdminAddonsController::class);
        Route::resource('categories', AdminCategoriesController::class);
        Route::resource('subcategories', AdminSubCategoriesController::class);
        Route::resource('menus', AdminMenusController::class);
        Route::resource('drivers', AdminDriversController::class);
        Route::resource('order-types', AdminOrderTypesController::class);
        Route::resource('orders', AdminOrdersController::class);
        Route::resource('order-items', AdminOrderItemsController::class);
        Route::resource('payments', AdminPaymentsController::class);
        Route::resource('payment-types', AdminPaymentTypesController::class);
        Route::resource('promotions', AdminPromotionsController::class);
        Route::resource('menu-promotions', AdminMenuPromotionsController::class);
        Route::resource('menu-addons', AdminMenuAddonsController::class);
        Route::resource('stages', AdminStagesController::class);
        Route::resource('statuses', AdminStatusesController::class);
        Route::resource('users', AdminUsersController::class);
        Route::resource('user-infos', AdminUserInfosController::class);
        Route::resource('variants', AdminVariantsController::class);
    });
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {});

});






// php artisan make:resource Admin/StatusesResource
// php artisan make:resource Admin/StagesResource
// php artisan make:resource Admin/VariantsResource
// php artisan make:resource Admin/UserInfosResource
// php artisan make:resource Admin/UsersResource
// php artisan make:resource Admin/PromotionsResource
// php artisan make:resource Admin/PaymentTypesResource
// php artisan make:resource Admin/PaymentsResource
// php artisan make:resource Admin/OrderTypesResource
// php artisan make:resource Admin/OrdersResource
// php artisan make:resource Admin/OrderItemsResource
// php artisan make:resource Admin/MenusResource
// php artisan make:resource Admin/MenuPromotionsResource
// php artisan make:resource Admin/MenuAddonsResource
// php artisan make:resource Admin/DriversResource
// php artisan make:resource Admin/SubCategoriesResource
// php artisan make:resource Admin/CategoriesResource
// php artisan make:resource Admin/AddonsResource

// php artisan make:resource User/PromotionsResource
// php artisan make:resource User/PaymentTypesResource
// php artisan make:resource User/PaymentsResource
// php artisan make:resource User/OrderTypesResource
// php artisan make:resource User/OrdersResource
// php artisan make:resource User/AddressesResource
// php artisan make:resource User/InfosResource

// php artisan make:resource Public/DriversResource
// php artisan make:resource Public/MenusResource
// php artisan make:resource Public/CategoriesResource
// php artisan make:resource Public/SubCategoriesResource
