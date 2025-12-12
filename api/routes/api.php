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
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ErrorLogController;

#LOGIN# - Rotas públicas para autenticação
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
});

#PDF# - Rotas públicas para geração de PDF
Route::middleware('web')->group(function () {
    Route::get('/orders/{id}/pdf', [PdfController::class, 'generateOrderPdf']);
    Route::get('/orders/{id}/pdf-sets', [PdfController::class, 'generateOrderSetsPdf']);
    Route::get('/orders/{id}/pdf-parts', [PdfController::class, 'generateOrderPartsPdf']);
    Route::get('/orders/{id}/pdf-print', [PdfController::class, 'generateOrderPrintPdf']);
    Route::get('/set-parts/{id}/pdf', [PdfController::class, 'generateSetPartPdf']);
});

#ROTAS PROTEGIDAS - Requerem autenticação via Sanctum#
Route::middleware('auth:sanctum')->group(function () {
    #GET#
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/permissions', [UserController::class, 'permissions']);
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/set-parts/types', [SetPartController::class, 'getPartTypes']);
    Route::get('/sets/{set}/parts', [SetPartController::class, 'getSetParts']);
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/orders/{id}/calculate-values', [OrderController::class, 'calculateValues']);

    #POST#
    Route::post('/upload-set-part', [SetPartController::class, 'upload'])
        ->middleware('order.permission:edit');
    Route::post('/set-parts/calculateProperties', [SetPartController::class, 'calculatePartProperties']);
    Route::post('/set-parts/calculateProcessValue', [ProcessController::class, 'calculateProcessValue']);
    Route::post('/sets/{set}/parts', [SetPartController::class, 'store'])
        ->middleware('order.permission:edit');
    Route::post('/sets/{set}/update', [SetController::class, 'update'])
        ->middleware('order.permission:edit');

    #PUT#
    Route::put('/sets/{set}/parts/{part}', [SetPartController::class, 'update'])
        ->middleware('order.permission:edit');
    Route::put('/set-parts/{part}/status', [SetPartController::class, 'updateStatus'])
        ->middleware('order.permission:edit');
    Route::put('/orders/{order}/on-markup-change', [OrderController::class, 'onMarkupChange'])
        ->middleware('order.permission:edit');
    Route::put('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])
        ->middleware('order.permission:edit');

    #DUPLICATE#
    Route::post('/orders/{order}/duplicate', [OrderController::class, 'duplicate']);

    #OS FILE#
    Route::get('/orders/{order}/download-os', [OrderController::class, 'downloadOsFile']);
    Route::delete('/orders/{order}/remove-os', [OrderController::class, 'removeOsFile']);

    #RESOURCES#
    Route::apiResource('/groups', GroupController::class);
    Route::apiResource('/users', UserController::class);
    
    // Orders resource com validação de permissão personalizada
    Route::apiResource('/orders', OrderController::class)->except(['update', 'destroy']);
    Route::put('/orders/{order}', [OrderController::class, 'update'])
        ->middleware('order.permission:edit');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])
        ->middleware('order.permission:delete');
    
    // Sets resource com validação de permissão personalizada
    Route::apiResource('/sets', SetController::class)->except(['store', 'update', 'destroy']);
    Route::post('/sets', [SetController::class, 'store'])
        ->middleware('order.permission:edit');
    Route::put('/sets/{set}', [SetController::class, 'update'])
        ->middleware('order.permission:edit');
    Route::delete('/sets/{set}', [SetController::class, 'destroy'])
        ->middleware('order.permission:delete');
    
    // SetParts resource com validação de permissão personalizada
    Route::apiResource('/set-parts', SetPartController::class)->except(['store', 'update', 'destroy']);
    Route::post('/set-parts', [SetPartController::class, 'store'])
        ->middleware('order.permission:edit');
    Route::put('/set-parts/{part}', [SetPartController::class, 'update'])
        ->middleware('order.permission:edit');
    Route::delete('/set-parts/{part}', [SetPartController::class, 'destroy'])
        ->middleware('order.permission:delete');
    
    Route::apiResource('/processes', ProcessController::class);
    Route::apiResource('/materials', MaterialController::class);
    Route::apiResource('/sheets', SheetController::class);
    Route::apiResource('/bars', BarController::class);
    Route::apiResource('/components', ComponentController::class);
    Route::apiResource('/customers', CustomerController::class);
    Route::apiResource('/mercosur-common-nomenclatures', MercosurCommonNomenclatureController::class);
    Route::apiResource('/states', StateController::class);
    
    Route::get('/error-logs/statistics', [ErrorLogController::class, 'statistics']);
    Route::delete('/error-logs/destroy-all', [ErrorLogController::class, 'destroyAll']);
    Route::apiResource('/error-logs', ErrorLogController::class)->only(['index', 'show', 'destroy']);
});