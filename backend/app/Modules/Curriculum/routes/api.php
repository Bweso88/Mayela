<?php

use App\Modules\Curriculum\Http\Controllers\CatalogController;
use App\Modules\Curriculum\Http\Controllers\SchoolingController;
use Illuminate\Support\Facades\Route;

Route::get('catalog/{country}/grades', [CatalogController::class, 'grades'])->middleware('throttle:api');

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::middleware('abilities:student')->group(function () {
        Route::put('me/schooling', [SchoolingController::class, 'updateOwn']);
        Route::get('me/subjects', [SchoolingController::class, 'subjects']);
    });

    Route::put('parent/children/{student}/schooling', [SchoolingController::class, 'updateChild'])
        ->middleware('abilities:parent');
});
