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

// API para obtener información de un almacén por ID (sin autenticación, para integración con Trazabilidad)
Route::get('/almacenes/{id}', function ($id) {
    $almacen = \App\Models\Almacen::find($id);
    
    if (!$almacen) {
        return response()->json([
            'success' => false,
            'message' => 'Almacén no encontrado'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => [
            'id' => $almacen->id,
            'nombre' => $almacen->nombre,
            'latitud' => $almacen->latitud,
            'longitud' => $almacen->longitud,
            'ubicacion' => $almacen->ubicacion,
            'direccion' => $almacen->ubicacion, // Alias para compatibilidad
            'estado' => $almacen->estado,
        ]
    ]);
})->name('api.almacenes.show');

