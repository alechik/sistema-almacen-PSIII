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
                    
                    // Guardar información del transportista en observaciones si no existe en el sistema
                    $infoTransportista = "Transportista asignado desde plantaCruds:\n";
                    $infoTransportista .= "- ID (plantaCruds): {$transportistaId}\n";
                    $infoTransportista .= "- Nombre: " . ($request->transportista['nombre'] ?? 'N/A') . "\n";
                    $infoTransportista .= "- Email: " . ($request->transportista['email'] ?? 'N/A') . "\n";
                    $infoTransportista .= "- Vehículo: " . ($request->vehiculo['placa'] ?? 'N/A') . "\n";
                    $infoTransportista .= "- Fecha asignación: " . ($request->fecha_asignacion ?? now()->toDateString()) . "\n";
                    
                    // Agregar a observaciones existentes o crear nuevas
                    $observaciones = $pedido->observaciones ?? '';
                    if (!empty($observaciones)) {
                        $pedido->observaciones = $observaciones . "\n\n" . $infoTransportista;
                    } else {
                        $pedido->observaciones = $infoTransportista;
                    }
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

            // Guardar información adicional del envío en observaciones
            $infoEnvio = "\n--- Información de Envío ---\n";
            $infoEnvio .= "Envío ID (plantaCruds): {$request->envio_id}\n";
            $infoEnvio .= "Código Envío: {$request->envio_codigo}\n";
            $infoEnvio .= "Estado: {$request->estado}\n";
            if ($request->fecha_aceptacion) {
                $infoEnvio .= "Fecha aceptación: {$request->fecha_aceptacion}\n";
            }
            if ($request->fecha_estimada_entrega) {
                $infoEnvio .= "Fecha estimada entrega: {$request->fecha_estimada_entrega}\n";
            }
            
            $observaciones = $pedido->observaciones ?? '';
            if (!empty($observaciones) && strpos($observaciones, '--- Información de Envío ---') === false) {
                $pedido->observaciones = $observaciones . "\n" . $infoEnvio;
            } elseif (empty($observaciones)) {
                $pedido->observaciones = $infoEnvio;
            }

            $pedido->save();

            DB::commit();

            Log::info('Información de asignación de envío recibida y procesada', [
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'envio_codigo' => $request->envio_codigo,
                'transportista_id' => $request->transportista['id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Información de asignación recibida correctamente',
                'pedido_id' => $pedidoId,
                'envio_id' => $request->envio_id,
                'envio_codigo' => $request->envio_codigo,
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

