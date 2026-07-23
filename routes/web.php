<?php

use Illuminate\Support\Facades\Route;
use Farukcoder\FeatureHeatmap\Http\Controllers\FeatureHeatmapController;

Route::prefix(config('feature-heatmap.route_prefix', 'feature-heatmap'))
    ->middleware(config('feature-heatmap.route_middleware', ['web']))
    ->group(function () {
        // Main dashboard view
        Route::get('/', [FeatureHeatmapController::class, 'index'])->name('feature-heatmap.index');

        // Global heatmap data JSON (existing)
        Route::get('/data', [FeatureHeatmapController::class, 'data'])->name('feature-heatmap.data');

        // User-wise tracking: list of all tracked users
        Route::get('/users', [FeatureHeatmapController::class, 'userIndex'])->name('feature-heatmap.users');

        // User-wise tracking: module heatmap detail for a single user
        Route::get('/users/{userId}', [FeatureHeatmapController::class, 'userDetail'])->name('feature-heatmap.user-detail');

        // User-wise tracking: CSV report download for a single user
        Route::get('/users/{userId}/report', [FeatureHeatmapController::class, 'userReport'])->name('feature-heatmap.user-report');

        // Package authentication routes
        Route::post('/login', [FeatureHeatmapController::class, 'login'])->name('feature-heatmap.login');
        Route::post('/logout', [FeatureHeatmapController::class, 'logout'])->name('feature-heatmap.logout');
    });
