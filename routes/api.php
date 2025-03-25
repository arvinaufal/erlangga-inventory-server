<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;

// Route untuk autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify', [AuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/report', [ReportController::class, 'index']);
    Route::get('/report/export/excel', [ReportController::class, 'exportExcel']);
    Route::get('/report/export/pdf', [ReportController::class, 'exportPdf']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('stocks', StockController::class);
    
});


Route::get('/user', function (Request $request) {
    dd('masuk lolos sanctum');
    return $request->user();
})->middleware('auth:sanctum');
