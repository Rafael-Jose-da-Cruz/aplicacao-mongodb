<?php

use App\Http\Controllers\Api\CharacterController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/characters/sync', [CharacterController::class, 'sync']);

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/characters', [CharacterController::class, 'index']);
Route::post('/characters', [CharacterController::class, 'store']);
Route::get('/characters/{id}', [CharacterController::class, 'show'])->where('id', '[a-fA-F0-9]{24}');
Route::put('/characters/{id}', [CharacterController::class, 'update'])->where('id', '[a-fA-F0-9]{24}');
Route::patch('/characters/{id}', [CharacterController::class, 'update'])->where('id', '[a-fA-F0-9]{24}');
Route::delete('/characters/{id}', [CharacterController::class, 'destroy'])->where('id', '[a-fA-F0-9]{24}');
