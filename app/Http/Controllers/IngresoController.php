<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetalleIngreso;
use App\Models\Ingreso;
use App\Models\Pedido;
use App\Models\TipoIngreso;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngresoController extends Controller
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
        $user = Auth::user();

        if ($user->hasRole('administrador')) {

            $ingresos = Ingreso::where('administrador_id', $user->id)
                ->with([
                    'almacen:id,nombre',
                    'administrador:id,full_name',
                    'detalles' => function ($q) {
                        $q->select('ingreso_id', 'cant_ingreso', 'precio');
                    }
                ])
                ->orderBy('id', 'ASC')
                ->paginate(10);  // <-- PAGINACIÓN
        } elseif ($user->hasRole('propietario')) {

            $adminsIds = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            $ingresos = Ingreso::whereIn('administrador_id', $adminsIds)
                ->with([
                    'almacen:id,nombre',
                    'administrador:id,full_name',
                    'detalles' => function ($q) {
                        $q->select('ingreso_id', 'cant_ingreso', 'precio');
                    }
                ])
                ->orderBy('id', 'DESC')
                ->paginate(10);  // <-- PAGINACIÓN
        } else {
            return back()->with('error', 'No tiene permisos para ver los ingresos.');
        }

        // Agregamos monto_total SIN romper el objeto paginator
        $ingresos->getCollection()->transform(function ($ing) {
            $ing->monto_total = $ing->detalles->sum(
                fn($d) =>
                $d->cant_ingreso * $d->precio
            );
            return $ing;
        });

        $proveedores = $this->proveedores;

        return view('ingresos.index', compact('ingresos') + ['proveedores' => $proveedores]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $admin = Auth::user();

        // Solo administradores pueden registrar ingresos
        if (!$admin->hasRole('administrador')) {
            return back()->with('error', 'Solo un usuario con rol ADMINISTRADOR puede registrar ingresos.');
        }

        // Obtener pedidos confirmados (estado = 3)
        $pedidos = Pedido::where('estado', 3)
            ->whereDoesntHave('ingreso')   // el pedido NO debe tener ingreso
            ->with(['detalles.producto', 'almacen'])
            ->get();

        $propietarioId = $admin->user_id ?? $admin->id;

        $almacenes = Almacen::where('user_id', $propietarioId)->get();
        $operadores = User::role('operador')->where('user_id', $propietarioId)->get();
        // dd($operadores);
        $transportistas = User::role('transportista')->where('user_id', $propietarioId)->get();
        $vehiculos = Vehiculo::all();
        $tiposIngreso = TipoIngreso::all();
        $proveedores = $this->proveedores;

        $lastId = Ingreso::max('id') ?? 0;

        return view('ingresos.create', compact(
            'pedidos',
            'almacenes',
            'operadores',
            'transportistas',
            'proveedores',
            'vehiculos',
            'tiposIngreso',
            'lastId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_comprobante' => 'required|string',
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date|after_or_equal:fecha_min',

            'almacen_id' => 'required|exists:almacens,id',
            'operador_id' => 'required|exists:users,id',
            'transportista_id' => 'required|exists:users,id',
            'proveedor_id' => 'required',
            'tipo_ingreso_id' => 'required|exists:tipo_ingresos,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'pedido_id' => 'required|exists:pedidos,id',

            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cant_ingreso' => 'required|numeric|min:1',
            'detalles.*.precio' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
        ]);

        $admin = Auth::user();

        $ingreso = Ingreso::create([
            'codigo_comprobante' => $request->codigo_comprobante,
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'estado' => 1, // PENDIENTE
            'almacen_id' => $request->almacen_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
            'proveedor_id' => $request->proveedor_id,
            'pedido_id' => $request->pedido_id,
            'tipo_ingreso_id' => $request->tipo_ingreso_id,
            'vehiculo_id' => $request->vehiculo_id,
            'administrador_id' => $admin->id
        ]);

        foreach ($request->detalles as $item) {
            DetalleIngreso::create([
                'ingreso_id' => $ingreso->id,
                'producto_id' => $item['producto_id'],
                'cant_ingreso' => $item['cant_ingreso'],
                'precio' => $item['precio'],
            ]);
        }

        return redirect()->route('ingresos.index')
            ->with('success', 'Ingreso registrado correctamente.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Ingreso $ingreso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingreso $ingreso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingreso $ingreso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingreso $ingreso)
    {
        //
    }
}
