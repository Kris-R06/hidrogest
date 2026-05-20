<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BombaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/bombas', [BombaController::class, 'index']); // GET para el mapa
Route::post('/sensor-data', [App\Http\Controllers\Api\BombaController::class, 'guardarLectura']);