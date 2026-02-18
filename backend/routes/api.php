<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\TravelController;
use Illuminate\Support\Facades\Route;

Route::get('/travels', [TravelController::class, 'index']);
Route::get('/travels/{travel:slug}', [TravelController::class, 'show']);

Route::post('/carts', [CartController::class, 'store']);
Route::get('/carts/{cart}', [CartController::class, 'show']);
Route::post('/carts/{cart}/pay', [CartController::class, 'pay']);
