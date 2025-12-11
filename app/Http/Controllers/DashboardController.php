<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Pedido;
use App\Models\Salida;
use App\Models\User;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ADMIN → va a Users
        if ($user->hasRole('admin')) {
            $users= User::propietarios()->get();
            // dd($users);
            return redirect()->route('users.index')->with('users',$users);
        }

        // ADMINISTRADOR → dashboard con cantidad de usuarios
        if ($user->hasRole('administrador')) {
            $cantidad = User::where('user_id', $user->parent->id)->count();
            $nroSalidasEmitidas= Salida::where('estado',1)->orWhere('estado',2)->count();
            $nroSalidasConfirmadas= Salida::where('estado',2)->count();
            $nroPedidosEmitidas= Pedido::where('estado',1)->orWhere('estado',2)->count();
            $nroPedidosConfirmadas= Pedido::where('estado',2)->count();
            $nroIngresoEmitidas= Ingreso::where('estado',1)->orWhere('estado',2)->count();
            $nroIngresoConfirmadas= Ingreso::where('estado',2)->count();

            return view('dashboard', compact(
                'cantidad',
                'nroSalidasEmitidas',
                'nroSalidasConfirmadas',
                'nroPedidosEmitidas',
                'nroPedidosConfirmadas',
                'nroIngresoEmitidas',
                'nroIngresoConfirmadas'));
        }

        // PROPIETARIO → dashboard básico
        if ($user->hasRole('propietario')) {
            $cantidad = User::where('user_id', $user->id)->count();
            $nroSalidasEmitidas= Salida::where('estado',1)->orWhere('estado',2)->count();
            $nroSalidasConfirmadas= Salida::where('estado',2)->count();
            $nroPedidosEmitidas= Pedido::where('estado',1)->orWhere('estado',2)->count();
            $nroPedidosConfirmadas= Pedido::where('estado',2)->count();
            $nroIngresoEmitidas= Ingreso::where('estado',1)->orWhere('estado',2)->count();
            $nroIngresoConfirmadas= Ingreso::where('estado',2)->count();

            return view('dashboard', compact(
                'cantidad',
                'nroSalidasEmitidas',
                'nroSalidasConfirmadas',
                'nroPedidosEmitidas',
                'nroPedidosConfirmadas',
                'nroIngresoEmitidas',
                'nroIngresoConfirmadas'));
        }

        // Por defecto
        return view('dashboard');
    }
}
