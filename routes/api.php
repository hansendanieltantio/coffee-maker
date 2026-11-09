<?php

use App\Http\Controllers\EdgeCloudController;
use App\Http\Controllers\EdgeOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\EnsureCoffeeMakerToken;
use Illuminate\Support\Facades\Route;

Route::middleware(EnsureCoffeeMakerToken::class)->group(function (): void {
    if (config('order_nodes.role') === 'edge') {
        Route::get('/next-order', [EdgeOrderController::class, 'nextOrder']);
        Route::post('/update-status/{id}', [EdgeOrderController::class, 'updateStatus']);
    } else {
        Route::get('/next-order', [OrderController::class, 'getNextOrder']);
        Route::post('/update-status/{id}', [OrderController::class, 'updateStatus']);
    }
});

Route::get('/check-queue/{id}', [OrderController::class, 'checkQueue']);

Route::get('/edge/orders', [EdgeCloudController::class, 'pendingOrders']);
Route::post('/edge/orders/{id}/claim', [EdgeCloudController::class, 'claim']);
Route::post('/edge/orders/{id}/status', [EdgeCloudController::class, 'updateStatus']);
Route::post('/edge/orders/{id}/schedule', [EdgeCloudController::class, 'updateSchedule']);
