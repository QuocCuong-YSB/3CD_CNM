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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify', [AuthController::class, 'verifyCode']);
Route::post('/resend', [AuthController::class, 'resendCode']);
Route::get('/member/country', [CountryController::class, 'index']);

Route::post('admin/brand', [BrandController::class, 'store']);
Route::get('/admin/brand', [BrandController::class, 'index']);
Route::put('/admin/brand/update/{id}', [BrandController::class, 'update']);
Route::delete('/admin/brand/delete/{id}', [BrandController::class, 'destroy']);
Route::get('/admin/brand/show/{id}', [BrandController::class, 'show']);
Route::post('admin/category', [CategoryController::class, 'store']);
Route::get('/admin/category', [CategoryController::class, 'index']);
Route::put('/admin/category/update/{id}', [CategoryController::class, 'update']);
Route::delete('/admin/category/delete/{id}', [CategoryController::class, 'destroy']);
Route::get('/admin/category/show/{id}', [CategoryController::class, 'show']);

Route::middleware(['auth:sanctum', 'check.token.expiration', 'level:1'])->prefix('admin')->group(function () {
    Route::get('/member', [MemberController::class, 'index']);
    Route::patch('/member/{id}', [MemberController::class, 'updateStatus']);

    // Admin Orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/order/{id}/status', [AdminOrderController::class, 'updateStatus']);
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
});


