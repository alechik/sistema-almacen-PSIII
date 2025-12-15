<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incidente;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidenteController extends Controller
{
    /**
     * Recibir notificación de incidente desde plantaCruds
     * POST /api/pedidos/{pedidoId}/incidente
     */
    public function recibirIncidente(Request $request, int $pedidoId): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'envio_id' => 'required|integer',
                'envio_codigo' => 'required|string',
                'incidente_id' => 'required|integer',
                'tipo_incidente' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'accion' => 'required|in:cancelar,continuar',
                'transportista' => 'required|array',
                'transportista.id' => 'nullable|integer',
                'transportista.nombre' => 'nullable|string',
                'fecha_reporte' => 'required|date',
                'ubicacion' => 'nullable|array',
                'ubicacion.lat' => 'nullable|numeric',
                'ubicacion.lng' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pedido = Pedido::find($pedidoId);

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            // Guardar foto si viene en base64
            $fotoUrl = null;
            if ($request->has('foto_base64') && $request->foto_base64) {
                try {
                    $directorio = "incidentes/{$pedidoId}";
                    if (!Storage::exists($directorio)) {
                        Storage::makeDirectory($directorio);
                    }
                    
                    $nombreArchivo = 'incidente_' . $request->incidente_id . '_' . time() . '.jpg';
                    $fotoContent = base64_decode($request->foto_base64);
                    
                    if ($fotoContent) {
                        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
                        Storage::put($rutaCompleta, $fotoContent);
                        $fotoUrl = $rutaCompleta;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error guardando foto de incidente', [
                        'pedido_id' => $pedidoId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Crear o actualizar el incidente
            $incidente = Incidente::updateOrCreate(
                [
                    'pedido_id' => $pedidoId,
                    'incidente_id_planta' => $request->incidente_id,
                ],
                [
                    'envio_id' => $request->envio_id,
                    'envio_codigo' => $request->envio_codigo,
                    'transportista_id' => $request->transportista['id'] ?? null,
                    'transportista_nombre' => $request->transportista['nombre'] ?? null,
                    'tipo_incidente' => $request->tipo_incidente,
                    'descripcion' => $request->descripcion,
                    'foto_url' => $fotoUrl,
                    'accion' => $request->accion,
                    'estado' => 'pendiente',
                    'ubicacion_lat' => $request->ubicacion['lat'] ?? null,
                    'ubicacion_lng' => $request->ubicacion['lng'] ?? null,
                    'fecha_reporte' => $request->fecha_reporte,
                ]
            );

            // Si la acción es cancelar, actualizar el estado del pedido
            if ($request->accion === 'cancelar') {
                $pedido->estado = 'cancelado';
                $pedido->save();
            }

            DB::commit();

            Log::warning('🚨 INCIDENTE RECIBIDO EN ALMACENES', [
                'incidente_id' => $incidente->id,
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'tipo' => $request->tipo_incidente,
                'accion' => $request->accion,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Incidente recibido correctamente',
                'data' => [
                    'incidente_id' => $incidente->id,
                    'pedido_id' => $pedidoId,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al recibir incidente', [
                'pedido_id' => $pedidoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
