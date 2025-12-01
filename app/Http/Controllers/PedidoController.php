<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    private $proveedores = [
        ['id' => 1, 'nombre' => 'Proveedor 1'],
        ['id' => 2, 'nombre' => 'Proveedor 2'],
        ['id' => 3, 'nombre' => 'Proveedor 3'],
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuario = Auth::user();

        // Si es propietario → ve pedidos de todos sus administradores
        if ($usuario->hasRole('propietario')) {
            $pedidos = Pedido::with(['almacen', 'administrador'])
                ->whereHas('administrador', function ($q) use ($usuario) {
                    $q->where('user_id', $usuario->id);
                })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            // Si es administrador → solo ve sus pedidos
            $pedidos = Pedido::with(['almacen', 'administrador'])
                ->where('administrador_id', $usuario->id)
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'proveedores' => $this->proveedores
        ]);
    }

    public function create()
    {
        $admin = Auth::user();
        $propietarioId = $admin->user_id;
        $lastId = Pedido::max('id') ?? 0;
        $almacenes = Almacen::where('user_id', $propietarioId)->get();
        $operadores = User::role('operador')->where('user_id', $propietarioId)->get();
        $transportistas = User::role('transportista')->where('user_id', $propietarioId)->get();
        $productos = Producto::where('estado', 1)->get(); // <<< Agregado

        return view('pedidos.create', compact('almacenes', 'operadores', 'lastId', 'transportistas', 'productos') + [
            'proveedores' => $this->proveedores
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'codigo_comprobante' => 'required|string',
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date|after_or_equal:fecha_min',

            'almacen_id' => 'required|exists:almacens,id',
            'proveedor_id' => 'required',
            'operador_id' => 'required|exists:users,id',
            'transportista_id' => 'required|exists:users,id',

            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $propietarioId = $user->user_id ?? $user->id;

        $lastPedido = Pedido::orderBy('id', 'desc')->first();
        $newId = $lastPedido ? $lastPedido->id + 1 : 1;

        $codigo = ($propietarioId * 1000000) + $newId;

        $pedido = Pedido::create([
            'codigo_comprobante' => $codigo,
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'estado' => Pedido::EMITIDO,
            'almacen_id' => $request->almacen_id,
            'proveedor_id' => $request->proveedor_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
            'administrador_id' => $user->id,
        ]);

        foreach ($request->productos as $item) {
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $item['producto_id'],
                'cantidad' => $item['cantidad'],
            ]);
        }

        return redirect()->route('pedidos.create')
            ->with('success', 'Pedido creado exitosamente');
    }


    public function show(Pedido $pedido)
    {
        $pedido->load([
            'almacen',
            'operador',
            'transportista',
            'administrador',
            'detalles.producto'
        ]);
        // dd($pedido);

        return view('pedidos.show', [
            'pedido' => $pedido,
            'proveedores' => $this->proveedores
        ]);
    }

    public function edit(Pedido $pedido)
    {
        $pedido->load(['detalles.producto']);

        $admin = Auth::user();
        if ($admin->hasRole('propietario')) {
            $propietarioId = $admin->id;
        } else {
            $propietarioId = $admin->user_id;
        }

        $almacenes = Almacen::where('user_id', $propietarioId)->get();
        $operadores = User::role('operador')->where('user_id', $propietarioId)->get();
        $transportistas = User::role('transportista')->where('user_id', $propietarioId)->get();
        $productos = Producto::where('estado', 1)->get();

        return view('pedidos.edit', [
            'pedido' => $pedido,
            'almacenes' => $almacenes,
            'operadores' => $operadores,
            'transportistas' => $transportistas,
            'productos' => $productos,
            'proveedores' => $this->proveedores
        ]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date|after_or_equal:fecha_min',

            'almacen_id' => 'required|exists:almacens,id',
            'proveedor_id' => 'required',
            'operador_id' => 'required|exists:users,id',
            'transportista_id' => 'required|exists:users,id',

            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
        ]);

        // Actualización cabecera
        $pedido->update([
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'almacen_id' => $request->almacen_id,
            'proveedor_id' => $request->proveedor_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
        ]);

        // Reemplazar el detalle
        DetallePedido::where('pedido_id', $pedido->id)->delete();

        foreach ($request->productos as $item) {
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $item['producto_id'],
                'cantidad' => $item['cantidad'],
            ]);
        }

        return redirect()->route('pedidos.index', $pedido->id)
            ->with('success', 'Pedido actualizado correctamente.');
    }

    public function destroy(Pedido $pedido)
    {
        //
    }
}
