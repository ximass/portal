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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderPartController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\BarController;
use App\Http\Controllers\ComponentController;

#GET#
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users/search', [UserController::class, 'search']);
Route::get('/materials/types', [MaterialController::class, 'getMaterialsType']);

#POST#
Route::post('/upload-set-part', [SetPartController::class, 'upload']);
Route::post('/set-parts/calculateProperties', [SetPartController::class, 'calculatePartProperties']);
Route::post('/set-parts/calculateProcessValue', [ProcessController::class, 'calculateProcessValue']);

#PUT#
// Route::put('/sets/{set}/parts/{}/', [SetPartController::class, 'upload']);

#RESOURCES#
Route::apiResource('/groups', GroupController::class);
Route::apiResource('/users', UserController::class);
Route::apiResource('/orders', OrderController::class);
Route::apiResource('/orders/{order}/order_parts', OrderPartController::class);
Route::apiResource('/sets', SetController::class);
Route::apiResource('/sets/{set}/parts', SetPartController::class);
Route::apiResource('/processes', ProcessController::class);
Route::apiResource('/materials', MaterialController::class);
Route::apiResource('/sheets', SheetController::class);
Route::apiResource('/bars', BarController::class);
Route::apiResource('/components', ComponentController::class);
Route::apiResource('/customers', CustomerController::class);

#LOGIN#
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
});