<?php

namespace App\Http\Controllers\View;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    public function compose($view)
    {
        $user = Auth::user();

        // 📌 CAMPANA 1: Solo para ADMIN
        // Solo para administrador
        if ($user && $user->hasRole('admin')) {

            $pendientes = User::pendientes()
                ->latest()
                ->take(10)
                ->get();

            $cantidadPendientes = User::pendientes()->count();

            $view->with([
                'pendientesUsuarios' => $pendientes,
                'cantidadPendientes' => $cantidadPendientes,
            ]);
        }
        // 📌 CAMPANA 2: Solo para PROPIETARIO (notificaciones clásicas)
        // 📌 CAMPANA PARA PROPIETARIO BASADA EN "pedidos"
        if ($user && $user->hasRole('propietario')) {

            $cantidadPedidosPendientes = Pedido::where('estado', 1)
                ->count();

            $pedidosPendientes = Pedido::where('estado', 1)
                ->latest()
                ->take(10)
                ->get();

            $view->with([
                'cantidadPedidosPendientes' => $cantidadPedidosPendientes,
                'pedidosPendientes' => $pedidosPendientes,
            ]);
        }
    }
}
