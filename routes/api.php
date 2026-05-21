<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\OcrController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post(
        '/update-qris',
        [AuthController::class, 'updateQris']
    );

    Route::post(
        '/bills',
        [BillController::class, 'store']
    );

    Route::get(
        '/bills',
        [BillController::class, 'index']
    );

    Route::get(
        '/bills/{id}',
        [BillController::class, 'show']
    );

    Route::patch(
        '/bill-items/{itemId}/status',
        [BillController::class, 'updateItemStatus']
    );

    Route::post(
        '/ocr-receipt',
        [OcrController::class, 'scanReceipt']
    );

});