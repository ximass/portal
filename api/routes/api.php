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
use App\Http\Controllers\MercosurCommonNomenclatureController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\PdfController;

#LOGIN# - Rotas públicas para autenticação
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
});

#ROTAS PROTEGIDAS - Requerem autenticação via Sanctum#
Route::middleware('auth:sanctum')->group(function () {
    #GET#
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/set-parts/types', [SetPartController::class, 'getPartTypes']);
    Route::get('/sets/{set}/parts', [SetPartController::class, 'getSetParts']);

    #POST#
    Route::post('/upload-set-part', [SetPartController::class, 'upload']);
    Route::post('/set-parts/calculateProperties', [SetPartController::class, 'calculatePartProperties']);
    Route::post('/set-parts/calculateProcessValue', [ProcessController::class, 'calculateProcessValue']);
    Route::post('/sets/{set}/parts', [SetPartController::class, 'store']);
    Route::post('/sets/{set}/update', [SetController::class, 'update']);

    #PUT#
    Route::put('/sets/{set}/parts/{part}', [SetPartController::class, 'update']);
    Route::put('/orders/{order}/on-markup-change', [OrderController::class, 'onMarkupChange']);

    #PDF#
    Route::get('/orders/{id}/pdf', [PdfController::class, 'generateOrderPdf']);

    #RESOURCES#
    Route::apiResource('/groups', GroupController::class);
    Route::apiResource('/users', UserController::class);
    Route::apiResource('/orders', OrderController::class);
    Route::apiResource('/sets', SetController::class);
    Route::apiResource('/set-parts', SetPartController::class);
    Route::apiResource('/processes', ProcessController::class);
    Route::apiResource('/materials', MaterialController::class);
    Route::apiResource('/sheets', SheetController::class);
    Route::apiResource('/bars', BarController::class);
    Route::apiResource('/components', ComponentController::class);
    Route::apiResource('/customers', CustomerController::class);
    Route::apiResource('/mercosur-common-nomenclatures', MercosurCommonNomenclatureController::class);
    Route::apiResource('/states', StateController::class);
});