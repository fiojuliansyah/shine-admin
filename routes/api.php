<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Auth\AuthController;

Route::get('login', [AuthController::class, 'loginForm']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/logs', [HomeController::class, 'logs']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/account', [ProfileController::class, 'updateAccount']);
        Route::get('/esign', [ProfileController::class, 'esign']);
        Route::post('/updateEsign', [ProfileController::class, 'updateEsign']);
    });

    Route::get('/ping', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'Server is alive',
            'timestamp' => now()
        ]);
    });
});
