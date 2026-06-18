<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\AddressController as ApiAddressController;
use App\Http\Controllers\Api\CouponController as ApiCouponController;
use App\Http\Controllers\Api\WishlistController as ApiWishlistController;

// Public endpoints
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

Route::get('/products', [ApiProductController::class, 'index']);
Route::get('/products/{slug}', [ApiProductController::class, 'show']);
Route::get('/categories', [ApiCategoryController::class, 'index']);
Route::get('/categories/{slug}', [ApiCategoryController::class, 'show']);

// Cart and Coupons (guest or auth)
Route::get('/cart', [ApiCartController::class, 'show']);
Route::post('/cart/add', [ApiCartController::class, 'add']);
Route::put('/cart/items/{item}', [ApiCartController::class, 'update']);
Route::delete('/cart/items/{item}', [ApiCartController::class, 'remove']);
Route::post('/cart/clear', [ApiCartController::class, 'clear']);

// Wishlist (guest toggle + guest/auth list)
Route::get('/wishlist', [ApiWishlistController::class, 'index']);
Route::post('/wishlist/toggle', [ApiWishlistController::class, 'toggle']);

Route::post('/coupons/apply', [ApiCouponController::class, 'apply']);
Route::post('/coupons/remove', [ApiCouponController::class, 'remove']);
Route::post('/coupons/validate', [ApiCouponController::class, 'validateCode']);

// Floral AI Assistant
Route::post('/ai/consult', [App\Http\Controllers\Api\AIClientController::class, 'consult']);

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'me']);
    Route::put('/user', [ApiAuthController::class, 'update']);
    Route::put('/user/password', [ApiAuthController::class, 'changePassword']);

    // Addresses
    Route::get('/user/addresses', [ApiAddressController::class, 'index']);
    Route::post('/user/addresses', [ApiAddressController::class, 'store']);
    Route::put('/user/addresses/{address}', [ApiAddressController::class, 'update']);
    Route::delete('/user/addresses/{address}', [ApiAddressController::class, 'destroy']);
    Route::post('/user/addresses/{address}/set-default', [ApiAddressController::class, 'setDefault']);

    // Orders
    Route::get('/orders', [ApiOrderController::class, 'index']);
    Route::get('/orders/{id}', [ApiOrderController::class, 'show']);
    Route::post('/checkout', [ApiOrderController::class, 'place']);

    Route::post('/ai/consult', [App\Http\Controllers\Api\AIClientController::class, 'consult']);
});



