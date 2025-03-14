<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\SetPartController;
use App\Http\Controllers\ProcessController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users/search', [UserController::class, 'search']);

Route::apiResource('/groups', GroupController::class);
Route::apiResource('/users', UserController::class);
Route::apiResource('/orders', OrderController::class);
Route::apiResource('/orders/{order}/order_parts', OrderPartController::class);
Route::apiResource('processes', ProcessController::class);
Route::apiResource('sets', SetController::class);
Route::apiResource('sets/{set}/parts', SetPartController::class);

##POST##
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/upload-set-part', [SetPartController::class, 'upload']);