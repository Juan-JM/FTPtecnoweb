<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsumirServicioController;
use App\Http\Controllers\ProductoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/productos', [ProductoController::class, 'apiIndex']);
Route::post('/urlcallback', [ConsumirServicioController::class, 'urlCallback']);
Route::post('/consultartransaccion', [ConsumirServicioController::class, 'ConsultarEstado']);

// Callback para PagoFacil - Esta ruta NO tiene protección CSRF
Route::post('/notificaciones-pagofacil', [ConsumirServicioController::class, 'urlCallback']);
