<?php

use App\Http\Controllers\Api\PelangganJsonController;
use App\Http\Controllers\JSONFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('pelanggan')->group(function () {
    Route::get('{id}/json', [PelangganJsonController::class, 'getJsonData']);
    Route::get('json/{nomor_hp}', [PelangganJsonController::class, 'getJsonDataByPhone']);

    Route::get('{id}/categories', [PelangganJsonController::class, 'getCategories']);
    Route::get('{id}/category/{category}', [PelangganJsonController::class, 'getCategoryItems']);
});
