<?php

use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/cart', [CartController::class, 'index']);

    Route::post('/cart', [CartController::class, 'store']);

    Route::put('/cart/{id}', [CartController::class, 'update']);

    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    Route::delete('/cart', [CartController::class, 'clear']);
});

Route::middleware(['auth:sanctum','check.token.expiration', 'level:1'])->group(function () {
    
});
Route::middleware(['auth:sanctum','check.token.expiration', 'level:0'])->group(function () {
   
});
