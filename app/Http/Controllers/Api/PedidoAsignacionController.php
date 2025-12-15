<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PedidoAsignacionController extends Controller
{
    /**
     * Recibe notificación de asignación de envío desde plantaCruds
     * 
     * @param Request $request
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function asignacionEnvio(Request $request, int $pedidoId): JsonResponse
    {
            $validator = Validator::make($request->all(), [
            'envio_id' => 'required|integer',
            'envio_codigo' => 'required|string',
            'estado' => 'required|string|in:asignado,aceptado,en_proceso,en_transito,entregado,cancelado',
            'transportista' => 'required|array',
            'transportista.id' => 'required|integer|min:1',
            'transportista.nombre' => 'nullable|string',
            'transportista.email' => 'nullable|email',
            'vehiculo' => 'required|array',
            'vehiculo.id' => 'required|integer|min:1',
            'vehiculo.placa' => 'nullable|string',
            'vehiculo.marca' => 'nullable|string',
            'vehiculo.modelo' => 'nullable|string',
            'fecha_asignacion' => 'nullable|date',
            'fecha_estimada_entrega' => 'nullable|date',
            'almacen_destino' => 'nullable|array',
            'documentos' => 'nullable|array',
            'documentos.propuesta_vehiculos' => 'nullable|string', // base64 PDF
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pedido = Pedido::find($pedidoId);

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            // Actualizar información del envío asignado
            // Nota: Necesitamos agregar campos al modelo Pedido para almacenar esta información
            // Por ahora, actualizamos los campos existentes relacionados con transportista
            
            DB::beginTransaction();

            // Actualizar transportista solo si existe en el sistema de almacenes
            // El transportista_id de plantaCruds puede no existir aquí
            if (isset($request->transportista['id'])) {
                $transportistaId = $request->transportista['id'];
                $transportista = User::find($transportistaId);
                
                if ($transportista) {
                    $pedido->transportista_id = $transportistaId;
                    Log::info('Transportista actualizado en pedido', [
                        'pedido_id' => $pedidoId,
                        'transportista_id' => $transportistaId,
                        'transportista_nombre' => $transportista->name,
                    ]);
                } else {
                    Log::warning('Transportista no encontrado en sistema de almacenes, se guarda información en observaciones', [
                        'pedido_id' => $pedidoId,
                        'transportista_id_planta' => $transportistaId,
                        'transportista_nombre' => $request->transportista['nombre'] ?? 'N/A',
                    ]);
                    
                    // Nota: La tabla pedidos no tiene columna observaciones
                    // La información del transportista se guarda en pedido_entregas
                    Log::info('Transportista de plantaCruds no existe en sistema de almacenes', [
                        'pedido_id' => $pedidoId,
                        'transportista_id_planta' => $transportistaId,
                        'transportista_nombre' => $request->transportista['nombre'] ?? 'N/A',
                    ]);
                }
            }

            // Actualizar estado del pedido según el estado del envío
            // Si el estado es "en_proceso", significa que el transportista aceptó el envío
            if ($request->estado === 'en_proceso' || $request->estado === 'aceptado') {
                // El pedido está en proceso cuando el transportista acepta
                $pedido->estado = Pedido::EN_PROCESO;
                Log::info('Pedido marcado como en proceso (transportista aceptó)', [
                    'pedido_id' => $pedidoId,
                    'estado_anterior' => $pedido->getOriginal('estado'),
                    'estado_nuevo' => $pedido->estado,
                ]);
            } elseif ($request->estado === 'asignado') {
                // El pedido está asignado pero aún no aceptado por el transportista
                // Mantener el estado actual o actualizar si es necesario
                Log::info('Pedido asignado a transportista (pendiente de aceptación)', [
                    'pedido_id' => $pedidoId,
                    'estado' => $pedido->estado,
                ]);
            }

            // Nota: La información del envío se guarda en pedido_entregas, no en observaciones
            Log::info('Información de envío recibida', [
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'envio_codigo' => $request->envio_codigo,
                'estado' => $request->estado,
            ]);

            $pedido->save();

            // Guardar propuesta de vehículos si viene en los documentos
            $documentosGuardados = [];
            if ($request->has('documentos.propuesta_vehiculos') && !empty($request->input('documentos.propuesta_vehiculos'))) {
                try {
                    $directorio = "pedidos/{$pedido->id}/documentos-entrega";
                    if (!Storage::exists($directorio)) {
                        Storage::makeDirectory($directorio);
                    }

                    $pdfContent = base64_decode($request->input('documentos.propuesta_vehiculos'));
                    if ($pdfContent !== false) {
                        $nombreArchivo = "propuesta-vehiculos-{$request->envio_codigo}.pdf";
                        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
                        Storage::put($rutaCompleta, $pdfContent);
                        
                        $documentosGuardados['propuesta_vehiculos'] = $rutaCompleta;
                        
                        Log::info('Propuesta de vehículos guardada desde asignación', [
                            'pedido_id' => $pedidoId,
                            'envio_id' => $request->envio_id,
                            'ruta' => $rutaCompleta,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error guardando propuesta de vehículos desde asignación', [
                        'pedido_id' => $pedidoId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Guardar o actualizar registro en pedido_entregas con la propuesta de vehículos
            if (!empty($documentosGuardados)) {
                // Usar fecha de asignación como fecha_entrega por defecto (aunque aún no esté entregado)
                // Esto es necesario porque fecha_entrega es NOT NULL en la base de datos
                $fechaEntrega = $request->fecha_asignacion 
                    ? \Carbon\Carbon::parse($request->fecha_asignacion)->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s');
                
                DB::table('pedido_entregas')->updateOrInsert(
                    [
                        'pedido_id' => $pedido->id,
                        'envio_id' => $request->envio_id,
                    ],
                    [
                        'envio_codigo' => $request->envio_codigo,
                        'fecha_entrega' => $fechaEntrega, // Usar fecha de asignación como placeholder
                        'transportista_nombre' => $request->transportista['nombre'] ?? 'N/A',
                        'documentos' => json_encode($documentosGuardados),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                
                Log::info('Registro en pedido_entregas actualizado con propuesta de vehículos', [
                    'pedido_id' => $pedidoId,
                    'envio_id' => $request->envio_id,
                    'documentos' => array_keys($documentosGuardados),
                ]);
            }

            DB::commit();

            Log::info('Información de asignación de envío recibida y procesada', [
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'envio_codigo' => $request->envio_codigo,
                'transportista_id' => $request->transportista['id'] ?? null,
                'tiene_propuesta_vehiculos' => !empty($documentosGuardados),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Información de asignación recibida correctamente',
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'envio_codigo' => $request->envio_codigo,
                'documentos_guardados' => !empty($documentosGuardados) ? array_keys($documentosGuardados) : [],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al procesar asignación de envío', [
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

