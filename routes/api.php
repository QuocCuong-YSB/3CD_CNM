<?php

use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\Member\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\Member\CheckoutController;
use App\Http\Controllers\Member\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Member\MemberController as MemberMemberController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify', [AuthController::class, 'verifyCode']);
Route::post('/resend', [AuthController::class, 'resendCode']);
Route::get('/member/country', [CountryController::class, 'index']);


Route::middleware(['auth:sanctum', 'check.token.expiration', 'level:1'])->prefix('admin')->group(function () {
    
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/trash-product', [ProductController::class, 'trash']);
    Route::get('/product/show/{id}', [ProductController::class, 'show']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::put('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'destroy']);
    Route::patch('/trash-product/restore/{id}', [ProductController::class, 'restore']);
    Route::delete('/trash-product/force-delete/{id}', [ProductController::class, 'forceDelete']);

    Route::post('/brand', [BrandController::class, 'store']);
    Route::get('/brand', [BrandController::class, 'index']);
    Route::put('/brand/update/{id}', [BrandController::class, 'update']);
    Route::delete('/brand/delete/{id}', [BrandController::class, 'destroy']);
    Route::get('/brand/show/{id}', [BrandController::class, 'show']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::get('/category', [CategoryController::class, 'index']);
    Route::put('/category/update/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/delete/{id}', [CategoryController::class, 'destroy']);
    Route::get('/category/show/{id}', [CategoryController::class, 'show']);
    Route::get('/member', [MemberController::class, 'index']);
    Route::patch('/member/{id}', [MemberController::class, 'updateStatus']);
    // Admin Orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/order/{id}/status', [AdminOrderController::class, 'updateStatus']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'check.token.expiration', 'level:0'])->prefix('member')->group(function () {
    // Checkout
    Route::post('/order', [CheckoutController::class, 'placeOrder']);

    // Member Orders
    Route::get('/order/user/{id}', [OrderController::class, 'index']);
    Route::put('/order/{id}/cancel', [OrderController::class, 'cancel']);
    Route::put('/order/{id}/delivered', [OrderController::class, 'markAsDelivered']);

    // Member Carts
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    Route::get('/user', [MemberMemberController::class, 'getProfile']);
    Route::post('/user', [MemberMemberController::class, 'update']);

    Route::post('/logout', [AuthController::class, 'logout']);
});


