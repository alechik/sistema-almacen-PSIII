<?php

use App\Http\Controllers\Api\PedidoApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/pedido/actualizar-estado', [PedidoApiController::class, 'actualizarEstado']);