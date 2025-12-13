<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\TrazabilidadIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PedidoStatusController extends Controller
{
    protected TrazabilidadIntegrationService $integrationService;

    public function __construct(TrazabilidadIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Recibe webhook de Trazabilidad para actualizar estado del pedido
     * 
     * @param Request $request
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $pedidoId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:pendiente,aprobado,rechazado',
            'tracking_id' => 'nullable|string',
            'message' => 'nullable|string',
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

            $estado = $request->input('estado');
            $data = [
                'tracking_id' => $request->input('tracking_id'),
                'message' => $request->input('message'),
            ];

            $success = $this->integrationService->updatePedidoStatus($pedidoId, $estado, $data);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'pedido_id' => $pedidoId,
                    'estado' => $estado
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar estado'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error en webhook de actualización de estado', [
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

