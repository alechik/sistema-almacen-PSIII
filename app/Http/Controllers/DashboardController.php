<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Pedido;
use App\Models\Salida;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ADMIN → va a Users
        if ($user->hasRole('admin')) {
            $users = User::propietarios()->get();
            // dd($users);
            return redirect()->route('users.index')->with('users', $users);
        }

        // ADMINISTRADOR → dashboard con cantidad de usuarios
        if ($user->hasRole('administrador')) {
            $cantidad = User::where('user_id', $user->parent->id)->count();
            $nroSalidasEmitidas = Salida::where('estado', 1)->orWhere('estado', 2)->count();
            $nroSalidasConfirmadas = Salida::where('estado', 2)->count();
            $nroPedidosEmitidas = Pedido::where('estado', 1)->orWhere('estado', 2)->count();
            $nroPedidosConfirmadas = Pedido::where('estado', 2)->count();
            $nroIngresoEmitidas = Ingreso::where('estado', 1)->orWhere('estado', 2)->count();
            $nroIngresoConfirmadas = Ingreso::where('estado', 2)->count();
        }

        // PROPIETARIO → dashboard básico
        if ($user->hasRole('propietario')) {
            $cantidad = User::where('user_id', $user->id)->count();
            $nroSalidasEmitidas = Salida::where('estado', 1)->orWhere('estado', 2)->count();
            $nroSalidasConfirmadas = Salida::where('estado', 2)->count();
            $nroPedidosEmitidas = Pedido::where('estado', 1)->orWhere('estado', 2)->count();
            $nroPedidosConfirmadas = Pedido::where('estado', 2)->count();
            $nroIngresoEmitidas = Ingreso::where('estado', 1)->orWhere('estado', 2)->count();
            $nroIngresoConfirmadas = Ingreso::where('estado', 2)->count();
        }
        $topProductos = DB::table('detalle_ingresos as di')
            ->join('productos as p', 'p.id', '=', 'di.producto_id')
            ->select(
                'p.nombre',
                DB::raw('SUM(di.cant_ingreso) as total')
            )
            ->groupBy('p.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $pedidosPorMes = DB::table('pedidos')
            ->select(
                DB::raw("TO_CHAR(date_trunc('month', fecha_max), 'YYYY-MM') as mes"),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_max', '>=', Carbon::now()->subMonths(5))
            ->groupBy(DB::raw("date_trunc('month', fecha_max)"))
            ->orderBy('mes')
            ->get();

        $pedidosPorDia = DB::table('pedidos')
            ->select(
                DB::raw("TO_CHAR(fecha_max, 'YYYY-MM-DD') as dia"),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_max', '>=', Carbon::now()->subDays(7))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();


        return view('dashboard', compact(
            'cantidad',
            'nroSalidasEmitidas',
            'nroSalidasConfirmadas',
            'nroPedidosEmitidas',
            'nroPedidosConfirmadas',
            'nroIngresoEmitidas',
            'nroIngresoConfirmadas',
            'topProductos',
            'pedidosPorMes',
            'pedidosPorDia'
        ));
    }
}
