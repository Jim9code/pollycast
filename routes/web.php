<?php

use App\Http\Controllers\ElectionController;
use Illuminate\Support\Facades\Route;

// Question 1: Display results for any individual Polling Unit
Route::get('/', [ElectionController::class, 'pollingUnitResults'])->name('polling-unit.index');

// Question 2: Display summed total results of all Polling Units under any selected LGA
Route::get('/lga-results', [ElectionController::class, 'lgaResults'])->name('lga-results.index');

// Question 3: Create and store results for ALL parties for a new Polling Unit
Route::get('/polling-unit/create', [ElectionController::class, 'createPollingUnitResult'])->name('polling-unit.create');
Route::post('/polling-unit/store', [ElectionController::class, 'storePollingUnitResult'])->name('polling-unit.store');

// AJAX JSON routes for Chained Combo Boxes (LGA -> Ward -> Polling Unit)
Route::get('/api/lgas/{lgaId}/wards', [ElectionController::class, 'getWardsByLga'])->name('api.wards');
Route::get('/api/wards/{uniqueWardId}/polling-units', [ElectionController::class, 'getPollingUnitsByWard'])->name('api.polling-units');

// Hidden Visitor & Assessor Activity Log Page
Route::get('/activity-logs', [ElectionController::class, 'activityLogs'])->name('activity.logs');
