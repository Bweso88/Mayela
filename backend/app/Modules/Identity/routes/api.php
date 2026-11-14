<?php

use App\Modules\Identity\Http\Controllers\AuthController;
use App\Modules\Identity\Http\Controllers\MeController;
use App\Modules\Identity\Http\Controllers\ParentChildController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('register-parent', [AuthController::class, 'registerParent']);
        Route::post('register-student', [AuthController::class, 'registerStudent']);
        Route::post('login', [AuthController::class, 'login']);
    });
    Route::post('student-login', [AuthController::class, 'studentLogin'])->middleware('throttle:student-login');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('me', MeController::class);

    Route::prefix('parent')->middleware('abilities:parent')->group(function () {
        Route::get('children', [ParentChildController::class, 'index']);
        Route::post('children', [ParentChildController::class, 'store']);
        Route::get('children/{student}', [ParentChildController::class, 'show']);
    });
});
