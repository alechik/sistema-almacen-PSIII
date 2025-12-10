<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlantaWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para integración con PlantaCruds
|
*/

// Ping de prueba
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Sistema Almacén funcionando',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Webhooks desde PlantaCruds
Route::prefix('webhook/planta')->group(function () {
    // Ping de conexión
    Route::get('/ping', [PlantaWebhookController::class, 'ping']);
    
    // Envíos
    Route::post('/envio', [PlantaWebhookController::class, 'envioCreado']);
    Route::post('/envio/estado', [PlantaWebhookController::class, 'envioEstadoActualizado']);
    Route::post('/envio/ubicacion', [PlantaWebhookController::class, 'envioUbicacion']);
    
    // Incidentes
    Route::post('/incidente', [PlantaWebhookController::class, 'incidenteCreado']);
    Route::post('/incidente/estado', [PlantaWebhookController::class, 'incidenteEstadoActualizado']);
});

