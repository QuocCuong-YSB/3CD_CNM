<?php

use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CountryController;
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

Route::middleware(['auth:sanctum','check.token.expiration', 'level:1'])->group(function () {
    Route::get('/admin/member', [MemberController::class, 'index']);
    Route::patch('/admin/member/{id}', [MemberController::class, 'updateStatus']);
});
Route::middleware(['auth:sanctum','check.token.expiration', 'level:0'])->group(function () {
   
});
