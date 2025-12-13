<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TrazabilidadIntegrationService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('TRAZABILIDAD_API_URL', 'http://localhost:8000/api');
    }

    /**
     * Envía un pedido completo a Trazabilidad
     * 
     * @param Pedido $pedido
     * @return array
     * @throws \Exception
     */
    public function sendPedidoToTrazabilidad(Pedido $pedido): array
    {
        // Cargar todas las relaciones necesarias
        $pedido->load([
            'almacen',
            'administrador',
            'operador',
            'transportista',
            'detalles.producto'
        ]);

        // Construir payload completo
        $payload = $this->buildPedidoPayload($pedido);

        Log::info('Enviando pedido a Trazabilidad', [
            'pedido_id' => $pedido->id,
            'codigo_comprobante' => $pedido->codigo_comprobante,
            'url' => "{$this->apiUrl}/pedidos-almacen"
        ]);

        try {
            $response = Http::timeout(30)
                ->post("{$this->apiUrl}/pedidos-almacen", $payload);

            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('Error al enviar pedido a Trazabilidad', [
                    'pedido_id' => $pedido->id,
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                throw new \Exception("Error al enviar pedido a Trazabilidad (HTTP {$response->status()}): {$errorBody}");
            }

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                throw new \Exception($result['message'] ?? 'Error desconocido al enviar pedido');
            }

            // Actualizar pedido con tracking_id
            $pedido->update([
                'trazabilidad_tracking_id' => $result['tracking_id'] ?? null,
                'trazabilidad_estado' => Pedido::TRAZABILIDAD_PENDIENTE,
                'enviado_a_trazabilidad' => true,
                'fecha_envio_trazabilidad' => now(),
                'estado' => Pedido::ENVIADO_TRAZABILIDAD
            ]);

            Log::info('Pedido enviado exitosamente a Trazabilidad', [
                'pedido_id' => $pedido->id,
                'tracking_id' => $result['tracking_id'] ?? null
            ]);

            return [
                'success' => true,
                'tracking_id' => $result['tracking_id'] ?? null,
                'message' => 'Pedido enviado exitosamente a Trazabilidad'
            ];

        } catch (\Exception $e) {
            Log::error('Excepción al enviar pedido a Trazabilidad', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Construye el payload completo del pedido para enviar a Trazabilidad
     * 
     * @param Pedido $pedido
     * @return array
     */
    private function buildPedidoPayload(Pedido $pedido): array
    {
        $almacen = $pedido->almacen;
        $administrador = $pedido->administrador;
        $operador = $pedido->operador;
        $transportista = $pedido->transportista;

        // Construir array de productos
        // Nota: Los productos vienen desde Trazabilidad, así que usamos el ID y nombre guardados
        $productos = [];
        foreach ($pedido->detalles as $detalle) {
            $producto = $detalle->producto;
            // Priorizar producto_trazabilidad_id, sino producto_id local
            $productoId = $detalle->producto_trazabilidad_id ?? ($producto ? $producto->id : null);
            // Priorizar producto_nombre guardado (desde Trazabilidad), sino usar nombre del producto local
            $productoNombre = $detalle->producto_nombre ?? ($producto ? $producto->nombre : 'Producto sin nombre');
            
            $productos[] = [
                'producto_id' => $productoId, // ID de Trazabilidad o ID local
                'producto_nombre' => $productoNombre,
                'cantidad' => (float) $detalle->cantidad,
                'precio' => 0.00, // No hay precio en el pedido de almacén
            ];
        }

        // Construir observaciones con toda la información relevante
        $observaciones = "Pedido desde Sistema Almacén\n";
        $observaciones .= "Código: {$pedido->codigo_comprobante}\n";
        $observaciones .= "Fecha: {$pedido->fecha}\n";
        $observaciones .= "Fecha mínima: {$pedido->fecha_min}\n";
        $observaciones .= "Fecha máxima: {$pedido->fecha_max}\n";
        
        if ($administrador) {
            $observaciones .= "Solicitante: {$administrador->full_name}";
            if ($administrador->email) {
                $observaciones .= " ({$administrador->email})";
            }
            $observaciones .= "\n";
        }
        
        if ($operador) {
            $observaciones .= "Operador: {$operador->full_name}\n";
        }
        
        if ($transportista) {
            $observaciones .= "Transportista: {$transportista->full_name}\n";
        }
        
        if ($pedido->proveedor_id) {
            $observaciones .= "Proveedor ID: {$pedido->proveedor_id}\n";
        }

        return [
            'pedido_id' => $pedido->id,
            'codigo_comprobante' => $pedido->codigo_comprobante,
            'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : null,
            'fecha_min' => $pedido->fecha_min ? $pedido->fecha_min->format('Y-m-d') : null,
            'fecha_max' => $pedido->fecha_max ? $pedido->fecha_max->format('Y-m-d') : null,
            'almacen' => [
                'id' => $almacen->id ?? null,
                'nombre' => $almacen->nombre ?? 'Almacén no especificado',
                'latitud' => $almacen->latitud ?? null,
                'longitud' => $almacen->longitud ?? null,
                'direccion' => $almacen->ubicacion ?? $almacen->nombre ?? null,
            ],
            'administrador' => [
                'id' => $administrador->id ?? null,
                'full_name' => $administrador->full_name ?? 'N/A',
                'email' => $administrador->email ?? null,
                'phone_number' => $administrador->phone_number ?? null,
            ],
            'operador' => $operador ? [
                'id' => $operador->id,
                'full_name' => $operador->full_name,
            ] : null,
            'transportista' => $transportista ? [
                'id' => $transportista->id,
                'full_name' => $transportista->full_name,
            ] : null,
            'proveedor_id' => $pedido->proveedor_id,
            'productos' => $productos,
            'observaciones' => $observaciones,
        ];
    }

    /**
     * Actualiza el estado del pedido desde webhook de Trazabilidad
     * 
     * @param int $pedidoId
     * @param string $estado
     * @param array $data
     * @return bool
     */
    public function updatePedidoStatus(int $pedidoId, string $estado, array $data = []): bool
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($pedidoId);

            // Mapear estado de Trazabilidad a estado local
            $estadoLocal = $this->mapTrazabilidadEstado($estado);

            $updateData = [
                'trazabilidad_estado' => $estado,
                'fecha_respuesta_trazabilidad' => now(),
            ];

            if ($estadoLocal !== null) {
                $updateData['estado'] = $estadoLocal;
            }

            $pedido->update($updateData);

            DB::commit();

            Log::info('Estado de pedido actualizado desde Trazabilidad', [
                'pedido_id' => $pedidoId,
                'estado_trazabilidad' => $estado,
                'estado_local' => $estadoLocal
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar estado de pedido desde Trazabilidad', [
                'pedido_id' => $pedidoId,
                'estado' => $estado,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mapea el estado de Trazabilidad al estado local
     * 
     * @param string $estadoTrazabilidad
     * @return int|null
     */
    private function mapTrazabilidadEstado(string $estadoTrazabilidad): ?int
    {
        return match($estadoTrazabilidad) {
            Pedido::TRAZABILIDAD_APROBADO => Pedido::APROBADO_TRAZABILIDAD,
            Pedido::TRAZABILIDAD_RECHAZADO => Pedido::RECHAZADO_TRAZABILIDAD,
            Pedido::TRAZABILIDAD_PENDIENTE => Pedido::ENVIADO_TRAZABILIDAD,
            default => null
        };
    }
}

