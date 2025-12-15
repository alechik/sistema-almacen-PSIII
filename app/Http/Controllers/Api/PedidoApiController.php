<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoApiController extends Controller
{
    public function actualizarEstado(Request $request)
    {
        $request->validate([
            'codigo_comprobante' => 'required|string|exists:pedidos,codigo_comprobante',
            'estado' => 'required|integer|in:0,1,2,3,4,5,6' // según tus constantes de Pedido
        ]);

        $pedido = Pedido::where('codigo_comprobante', $request->codigo_comprobante)->first();

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        // Opcional: restricciones de transición de estado
        // por ejemplo: no permitir cambiar de COMPLETADO a EMITIDO
        if ($pedido->estado == Pedido::COMPLETADO) {
            return response()->json(['error' => 'No se puede cambiar un pedido COMPLETADO'], 400);
        }

        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json([
            'success' => true,
            'codigo_comprobante' => $pedido->codigo_comprobante,
            'nuevo_estado' => $pedido->estado
        ]);
    }
}

