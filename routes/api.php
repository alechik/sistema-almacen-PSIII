<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PedidoStatusController;
use App\Http\Controllers\Api\PedidoAsignacionController;
use App\Http\Controllers\PedidoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Webhook desde Trazabilidad para actualizar estado de pedido (sin autenticación)
Route::post('/pedidos/{pedido}/actualizar-estado', [PedidoStatusController::class, 'updateStatus'])
    ->name('api.pedidos.actualizar-estado');

// Webhook desde plantaCruds para notificar asignación de envío (sin autenticación)
Route::post('/pedidos/{pedido}/asignacion-envio', [PedidoAsignacionController::class, 'asignacionEnvio'])
    ->name('api.pedidos.asignacion-envio');

