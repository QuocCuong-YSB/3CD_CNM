<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CartController;
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
