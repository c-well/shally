<?php

use App\Http\Controllers\LittleLightTrackingController;
use App\Http\Controllers\AdminLittleLightController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/little-light/tracking')->group(function () {
    Route::get('/bootstrap', [LittleLightTrackingController::class, 'bootstrap'])->middleware('throttle:60,1');
    Route::post('/start', [LittleLightTrackingController::class, 'start'])->middleware('throttle:120,10');
    Route::post('/progress', [LittleLightTrackingController::class, 'progress'])->middleware('throttle:240,1');
});
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/admin/games/little-light', [AdminLittleLightController::class, 'index'])->name('admin.games.little-light');
    Route::patch('/admin/games/little-light/{run}', [AdminLittleLightController::class, 'review'])->name('admin.games.little-light.review');
});
