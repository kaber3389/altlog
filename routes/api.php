<?php

use App\Http\Controllers\HoldController;
use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

Route::get('/slots/availability', SlotController::class);
Route::post('/slots/{id}/hold', [HoldController::class, 'hold']);
Route::post('/holds/{id}/confirm', [HoldController::class, 'confirm']);
Route::delete('/holds/{id}', [HoldController::class, 'cancel']);