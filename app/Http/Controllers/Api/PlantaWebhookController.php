<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\EnvioPlanta;
use App\Models\EnvioPlantaProducto;
use App\Models\IncidentePlanta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlantaWebhookController extends Controller
{
    /**
     * Recibir notificación de nuevo envío desde Planta
     * POST /api/webhook/planta/envio
     */
    public function envioCreado(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|integer',
                'codigo' => 'required|string',
                'almacen_destino' => 'required|string',
                'estado' => 'required|string',
                'fecha_creacion' => 'nullable|date',
                'fecha_estimada_entrega' => 'nullable|date',
                'hora_estimada' => 'nullable|string',
                'total_cantidad' => 'nullable|integer',
                'total_peso' => 'nullable|numeric',
                'total_precio' => 'nullable|numeric',
                'observaciones' => 'nullable|string',
                'productos' => 'nullable|array',
                'origen_lat' => 'nullable|numeric',
                'origen_lng' => 'nullable|numeric',
                'origen_direccion' => 'nullable|string',
                'destino_lat' => 'nullable|numeric',
                'destino_lng' => 'nullable|numeric',
                'destino_direccion' => 'nullable|string',
            ]);

            // Buscar almacén local por nombre
            $almacen = Almacen::where('nombre', 'like', '%' . $data['almacen_destino'] . '%')->first();

            if (!$almacen) {
                Log::warning("Webhook: Almacén no encontrado - {$data['almacen_destino']}");
                return response()->json([
                    'success' => false,
                    'message' => 'Almacén no encontrado en el sistema local'
                ], 404);
            }

            // Crear o actualizar envío
            $envio = EnvioPlanta::updateOrCreate(
                ['envio_planta_id' => $data['id']],
                [
                    'codigo' => $data['codigo'],
                    'almacen_id' => $almacen->id,
                    'estado' => $data['estado'],
                    'fecha_creacion' => $data['fecha_creacion'] ?? null,
                    'fecha_estimada_entrega' => $data['fecha_estimada_entrega'] ?? null,
                    'hora_estimada' => $data['hora_estimada'] ?? null,
                    'total_cantidad' => $data['total_cantidad'] ?? 0,
                    'total_peso' => $data['total_peso'] ?? 0,
                    'total_precio' => $data['total_precio'] ?? 0,
                    'observaciones' => $data['observaciones'] ?? null,
                    'origen_lat' => $data['origen_lat'] ?? null,
                    'origen_lng' => $data['origen_lng'] ?? null,
                    'origen_direccion' => $data['origen_direccion'] ?? null,
                    'destino_lat' => $data['destino_lat'] ?? null,
                    'destino_lng' => $data['destino_lng'] ?? null,
                    'destino_direccion' => $data['destino_direccion'] ?? null,
                    'sincronizado_at' => now(),
                    'visto' => false,
                ]
            );

            // Sincronizar productos
            if (isset($data['productos']) && is_array($data['productos'])) {
                $envio->productos()->delete();
                
                foreach ($data['productos'] as $prod) {
                    EnvioPlantaProducto::create([
                        'envio_planta_id' => $envio->id,
                        'producto_nombre' => $prod['producto_nombre'] ?? 'Producto',
                        'cantidad' => $prod['cantidad'] ?? 0,
                        'peso_unitario' => $prod['peso_unitario'] ?? 0,
                        'precio_unitario' => $prod['precio_unitario'] ?? 0,
                        'total_peso' => $prod['total_peso'] ?? 0,
                        'total_precio' => $prod['total_precio'] ?? 0,
                    ]);
                }
            }

            Log::info("Webhook: Envío sincronizado - {$data['codigo']}");

            return response()->json([
                'success' => true,
                'message' => 'Envío sincronizado correctamente',
                'envio_id' => $envio->id
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error procesando webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir actualización de estado del envío
     * POST /api/webhook/planta/envio/estado
     */
    public function envioEstadoActualizado(Request $request)
    {
        try {
            $data = $request->validate([
                'envio_id' => 'required|integer',
                'codigo' => 'required|string',
                'estado' => 'required|string',
                'fecha_asignacion' => 'nullable|date',
                'fecha_inicio_transito' => 'nullable|date',
                'fecha_entrega' => 'nullable|date',
                'transportista_nombre' => 'nullable|string',
                'transportista_telefono' => 'nullable|string',
                'vehiculo_placa' => 'nullable|string',
                'vehiculo_descripcion' => 'nullable|string',
            ]);

            $envio = EnvioPlanta::where('envio_planta_id', $data['envio_id'])->first();

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Envío no encontrado'
                ], 404);
            }

            $envio->update([
                'estado' => $data['estado'],
                'fecha_asignacion' => $data['fecha_asignacion'] ?? $envio->fecha_asignacion,
                'fecha_inicio_transito' => $data['fecha_inicio_transito'] ?? $envio->fecha_inicio_transito,
                'fecha_entrega' => $data['fecha_entrega'] ?? $envio->fecha_entrega,
                'transportista_nombre' => $data['transportista_nombre'] ?? $envio->transportista_nombre,
                'transportista_telefono' => $data['transportista_telefono'] ?? $envio->transportista_telefono,
                'vehiculo_placa' => $data['vehiculo_placa'] ?? $envio->vehiculo_placa,
                'vehiculo_descripcion' => $data['vehiculo_descripcion'] ?? $envio->vehiculo_descripcion,
                'sincronizado_at' => now(),
                'visto' => false,
            ]);

            Log::info("Webhook: Estado actualizado - {$data['codigo']} -> {$data['estado']}");

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir ubicación actualizada del envío
     * POST /api/webhook/planta/envio/ubicacion
     */
    public function envioUbicacion(Request $request)
    {
        try {
            $data = $request->validate([
                'envio_id' => 'required|integer',
                'latitud' => 'required|numeric',
                'longitud' => 'required|numeric',
            ]);

            $envio = EnvioPlanta::where('envio_planta_id', $data['envio_id'])->first();

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Envío no encontrado'
                ], 404);
            }

            $envio->update([
                'ubicacion_lat' => $data['latitud'],
                'ubicacion_lng' => $data['longitud'],
                'ubicacion_actualizada_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir notificación de incidente
     * POST /api/webhook/planta/incidente
     */
    public function incidenteCreado(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|integer',
                'envio_id' => 'required|integer',
                'tipo_incidente' => 'required|string',
                'descripcion' => 'nullable|string',
                'foto_url' => 'nullable|string',
                'estado' => 'required|string',
                'fecha_reporte' => 'nullable|date',
            ]);

            $envio = EnvioPlanta::where('envio_planta_id', $data['envio_id'])->first();

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Envío no encontrado'
                ], 404);
            }

            $incidente = IncidentePlanta::updateOrCreate(
                ['incidente_planta_id' => $data['id']],
                [
                    'envio_planta_id' => $envio->id,
                    'tipo_incidente' => $data['tipo_incidente'],
                    'descripcion' => $data['descripcion'] ?? null,
                    'foto_url' => $data['foto_url'] ?? null,
                    'estado' => $data['estado'],
                    'fecha_reporte' => $data['fecha_reporte'] ?? now(),
                    'visto' => false,
                ]
            );

            Log::info("Webhook: Incidente recibido - Envío {$envio->codigo}");

            return response()->json([
                'success' => true,
                'message' => 'Incidente registrado correctamente',
                'incidente_id' => $incidente->id
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir actualización de estado de incidente
     * POST /api/webhook/planta/incidente/estado
     */
    public function incidenteEstadoActualizado(Request $request)
    {
        try {
            $data = $request->validate([
                'incidente_id' => 'required|integer',
                'estado' => 'required|string',
                'fecha_resolucion' => 'nullable|date',
                'notas_resolucion' => 'nullable|string',
            ]);

            $incidente = IncidentePlanta::where('incidente_planta_id', $data['incidente_id'])->first();

            if (!$incidente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidente no encontrado'
                ], 404);
            }

            $incidente->update([
                'estado' => $data['estado'],
                'fecha_resolucion' => $data['fecha_resolucion'] ?? null,
                'notas_resolucion' => $data['notas_resolucion'] ?? null,
                'visto' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del incidente actualizado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping para verificar conexión
     * GET /api/webhook/planta/ping
     */
    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'Sistema Almacén conectado',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}

