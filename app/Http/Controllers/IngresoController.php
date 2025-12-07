<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetalleIngreso;
use App\Models\Ingreso;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TipoIngreso;
use App\Models\User;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngresoController extends Controller
{
    /**
     * ESTADO = 1 : EMITIDO
     * ESTADO = 2 : COMPLETADO
     * ESTADO = 3 : ANULADO
     */
    //CONSUMIR APIS DE PROVEEDORES EXTERNOS
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
        $pedidos = Pedido::where('estado', 2)
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
            'estado' => 1, // EMITIDO
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
        // Cargamos las relaciones necesarias
        $ingreso->load([
            'almacen:id,nombre',
            'operador:id,full_name',
            'transportista:id,full_name',
            'administrador:id,full_name',
            'vehiculo:id,placa_identificacion',
            'tipoIngreso:id,nombre',
            'pedido:id,codigo_comprobante',
            'detalles.producto:id,nombre'
        ]);

        // Cálculo del monto total del ingreso
        $monto_total = $ingreso->detalles->sum(function ($d) {
            return $d->cant_ingreso * $d->precio;
        });

        // Proveedores del sistema
        $proveedores = $this->proveedores;

        // Encontrar proveedor por id
        $proveedor_nombre = collect($proveedores)
            ->firstWhere('id', $ingreso->proveedor_id)['nombre'] ?? 'No definido';

        return view('ingresos.show', compact('ingreso', 'monto_total', 'proveedor_nombre'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingreso $ingreso)
    {
        $user = Auth::user();

        // Permisos: solo propietario o administrador
        if (! $user->hasRole(['administrador', 'propietario'])) {
            return back()->with('error', 'No tiene permisos para editar ingresos.');
        }

        // Validación adicional según rol
        if ($user->hasRole('administrador') && $ingreso->administrador_id !== $user->id) {
            return back()->with('error', 'No puede editar ingresos de otros administradores.');
        }

        if ($user->hasRole('propietario')) {
            $adminsIds = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            if (! $adminsIds->contains($ingreso->administrador_id)) {
                return back()->with('error', 'Este ingreso no pertenece a sus administradores.');
            }
        }

        // ✔ Cargar relaciones
        $ingreso->load([
            'detalles.producto',
            'almacen',
            'operador',
            'transportista',
            'vehiculo',
            'tipoIngreso',
            'pedido'
        ]);
        // dd($ingreso);

        $productos = Producto::where('proveedor_id', $ingreso->proveedor_id)->get();
        // dd($productos);
        // Datos necesarios para el formulario
        if ($user->hasRole('propietario')) {
            $propietarioId = $user->id;
        } else {
            $propietarioId = $user->user_id;
        }

        $almacenes = Almacen::where('user_id', $propietarioId)->get();
        // dd($almacenes);
        $operadores = User::role('operador')->where('user_id', $propietarioId)->get();
        $transportistas = User::role('transportista')->where('user_id', $propietarioId)->get();
        $vehiculos = Vehiculo::all();
        $tiposIngreso = TipoIngreso::all();
        $proveedores = $this->proveedores;

        return view('ingresos.edit', compact(
            'ingreso',
            'almacenes',
            'operadores',
            'transportistas',
            'vehiculos',
            'tiposIngreso',
            'proveedores',
            'productos'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingreso $ingreso)
    {
        $user = Auth::user();

        // 🔐 Permisos de rol
        if (! $user->hasRole(['administrador', 'propietario'])) {
            return back()->with('error', 'No tiene permisos para actualizar ingresos.');
        }

        // 🔐 Restricción según rol
        if ($user->hasRole('administrador') && $ingreso->administrador_id !== $user->id) {
            return back()->with('error', 'No puede modificar ingresos que no registró.');
        }

        if ($user->hasRole('propietario')) {
            $adminsIds = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            if (! $adminsIds->contains($ingreso->administrador_id)) {
                return back()->with('error', 'Este ingreso no pertenece a sus administradores.');
            }
        }

        // 🔎 Validaciones
        $validated = $request->validate([
            'codigo_comprobante' => 'required|string',
            'fecha'             => 'required|date',
            'fecha_min'         => 'required|date',
            'fecha_max'         => 'required|date|after_or_equal:fecha_min',

            'almacen_id'        => 'required|exists:almacens,id',
            'operador_id'       => 'required|exists:users,id',
            'transportista_id'  => 'required|exists:users,id',
            'proveedor_id'      => 'required',
            'tipo_ingreso_id'   => 'required|exists:tipo_ingresos,id',
            'vehiculo_id'       => 'nullable|exists:vehiculos,id',
            'pedido_id'         => 'required|exists:pedidos,id',

            'detalles'                     => 'required|array|min:1',
            'detalles.*.producto_id'       => 'required|exists:productos,id',
            'detalles.*.cant_ingreso'      => 'required|numeric|min:1',
            'detalles.*.precio'            => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
        ]);

        // ✔ Actualizar ingreso
        $ingreso->update([
            'codigo_comprobante' => $request->codigo_comprobante,
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'almacen_id' => $request->almacen_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
            'proveedor_id' => $request->proveedor_id,
            'pedido_id' => $request->pedido_id,
            'tipo_ingreso_id' => $request->tipo_ingreso_id,
            'vehiculo_id' => $request->vehiculo_id,
        ]);

        // ✔ Eliminar detalle anterior
        DetalleIngreso::where('ingreso_id', $ingreso->id)->delete();

        // ✔ Registrar nuevo detalle
        foreach ($request->detalles as $d) {
            DetalleIngreso::create([
                'ingreso_id' => $ingreso->id,
                'producto_id' => $d['producto_id'],
                'cant_ingreso' => $d['cant_ingreso'],
                'precio' => $d['precio'],
            ]);
        }

        return redirect()->route('ingresos.index')
            ->with('success', 'Ingreso actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, Ingreso $ingreso)
    {
        $user = Auth::user();

        // Solo administrador o propietario
        if (! $user->hasRole(['administrador', 'propietario'])) {
            return back()->with('error', 'No tiene permisos para cambiar el estado.');
        }

        // Restricción por rol administrador
        if ($user->hasRole('administrador') && $ingreso->administrador_id !== $user->id) {
            return back()->with('error', 'No puede modificar ingresos que no registró.');
        }

        // Restricción por rol propietario
        if ($user->hasRole('propietario')) {
            $adminsIds = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            if (! $adminsIds->contains($ingreso->administrador_id)) {
                return back()->with('error', 'Este ingreso no pertenece a sus administradores.');
            }
        }

        // Debe estar en estado emitido
        if ($ingreso->estado != 1) {
            return back()->with('error', 'Solo los ingresos EMITIDOS pueden cambiar de estado.');
        }

        // Validar acción
        $validated = $request->validate([
            'accion' => 'required|in:confirmar,anular'
        ]);

        if ($request->accion === 'confirmar') {
            $ingreso->estado = 2; // CONFIRMADO
        }

        if ($request->accion === 'anular') {
            $ingreso->estado = 0; // ANULADO
        }

        $ingreso->save();

        return back()->with('success', 'Estado del ingreso actualizado.');
    }


    public function pdf(Ingreso $ingreso)
    {
        $ingreso->load([
            'almacen:id,nombre',
            'operador:id,full_name',
            'transportista:id,full_name',
            'administrador:id,full_name',
            'vehiculo:id,placa_identificacion',
            'tipoIngreso:id,nombre',
            'pedido:id,codigo_comprobante',
            'detalles.producto:id,nombre,unidad_medida_id',
            'detalles.producto.unidadMedida:id,cod_unidad_medida'
        ]);

        $monto_total = $ingreso->detalles->sum(fn($d) => $d->cant_ingreso * $d->precio);

        $proveedores = $this->proveedores;
        $empresa = User::find(Auth::user()->user_id);
        $propietario = User::find(Auth::user()->user_id);
        $proveedor_nombre = collect($proveedores)
            ->firstWhere('id', $ingreso->proveedor_id)['nombre'] ?? 'No definido';

        $pdf = Pdf::loadView('ingresos.comprobante-pdf', [
            'ingreso'          => $ingreso,
            'monto_total'      => $monto_total,
            'proveedor_nombre' => $proveedor_nombre,
            'empresa'          => $empresa,
            'propietario'      => $propietario,
        ])->setPaper('letter', 'portrait');

        // Agregar footer con firma y numeración
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Texto izquierda (administrador + fecha)
        $canvas->page_text(25, $h - 35, "Solicitado por: " . ($ingreso->administrador->full_name ?? '-') .
            "    |    Fecha: " . now()->format('d/m/Y'), null, 9, [0, 0, 0]);

        // Numeración derecha
        $canvas->page_text($w - 120, $h - 35, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, [0, 0, 0]);

        return $pdf->stream("Ingreso-{$ingreso->codigo_comprobante}.pdf");
    }
}
