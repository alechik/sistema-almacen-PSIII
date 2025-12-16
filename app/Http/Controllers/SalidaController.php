<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetalleSalida;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalidaController extends Controller
{
    /**
     * const CANCELADO = 0; //el punto de venta puede cancelar salida
     * const EMITIDO = 1; //administrador de almacen
     * const CONFIRMADO = 2; //el propietario y administrador pueden confirmar la salida
     * const TERMINADO = 3; //el punto de venta lo marca como terminado
     * const ANULADO = 4; //el propietario y administrador puede anular la salida
     */
    //CONSUMIR APIS DE PUNTOS DE VENTAS EXTERNOS
    private $puntos_ventas = [
        ['id' => 1, 'nombre' => 'Punto de Venta 1'],
        ['id' => 2, 'nombre' => 'Punto de Venta 2'],
        ['id' => 3, 'nombre' => 'Punto de Venta 3'],
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('propietario')) {

            // Ver todas las salidas generadas por los administradores del propietario
            $adminsIds = User::where('user_id', $user->id)
                ->role('administrador')
                ->pluck('id');

            $salidas = Salida::whereIn('administrador_id', $adminsIds)
                ->with(['almacen', 'operador', 'transportista', 'tipoSalida'])
                ->orderBy('id', 'desc')
                ->paginate(15);
        } elseif ($user->hasRole('administrador')) {

            // Solo sus propias salidas
            $salidas = Salida::where('administrador_id', $user->id)
                ->with(['almacen', 'operador', 'transportista', 'tipoSalida'])
                ->orderBy('id', 'desc')
                ->paginate(15);
        } elseif ($user->hasRole('operador')) {

            // Salidas donde él está como operador
            $salidas = Salida::where('operador_id', $user->id)
                ->with(['almacen', 'operador', 'transportista', 'tipoSalida'])
                ->orderBy('id', 'desc')
                ->paginate(15);
        } elseif ($user->hasRole('transportista')) {

            // Salidas donde él está como transportista
            $salidas = Salida::where('transportista_id', $user->id)
                ->with(['almacen', 'operador', 'transportista', 'tipoSalida'])
                ->orderBy('id', 'desc')
                ->paginate(15);
        } else {
            // Rol desconocido: no ve nada
            $salidas = collect();
        }

        return view('salidas.index', compact('salidas'));
    }

    public function create()
    {
        $propietario = Auth::user()->parent;

        $almacenes = Almacen::where('user_id', $propietario->id)->get();

        $operadores = User::where('user_id', $propietario->id)
            ->role('operador')->get();

        $transportistas = User::where('user_id', $propietario->id)
            ->role('transportista')->get();

        $vehiculos = Vehiculo::all();
        $tiposSalida = TipoSalida::all();

        // ✅ Productos que tengan stock en al menos un almacén
        $productos = Producto::whereHas('almacenes', function ($q) {
            $q->where('stock', '>', 0);
        })->get();

        $puntosVenta = $this->puntos_ventas;

        $lastId = (Salida::max('id') ?? 0) + 1;
        $lastNota = Salida::max('nota_venta_id') ?? 0;

        return view('salidas.create', compact(
            'almacenes',
            'operadores',
            'transportistas',
            'vehiculos',
            'tiposSalida',
            'productos',
            'puntosVenta',
            'lastId',
            'lastNota'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date',
            'almacen_id' => 'required|integer',
            'operador_id' => 'required|integer',
            'transportista_id' => 'required|integer',
            'vehiculo_id' => 'required|integer',
            'tipo_salida_id' => 'required|integer',
            'punto_venta_id' => 'required|integer',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|integer',
            'detalles.*.cant_salida' => 'required|numeric|min:0.01',
            'detalles.*.precio' => 'required|numeric|min:0.0001'
        ]);

        $lastId = Salida::max('id') ?? 0;
        $lastNota = Salida::max('nota_venta_id') ?? 0;

        $adminId = auth()->user()->user_id ?? auth()->user()->id;

        $codigo_comprobante = "S" . ($adminId * 1000000 + ($lastId + 1));

        $salida = Salida::create([
            'codigo_comprobante' => $codigo_comprobante,
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'estado' => Salida::EMITIDO,
            'almacen_id' => $request->almacen_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
            'punto_venta_id' => $request->punto_venta_id,
            'nota_venta_id' => $lastNota + 1,
            'tipo_salida_id' => $request->tipo_salida_id,
            'vehiculo_id' => $request->vehiculo_id,
            'administrador_id' => Auth::user()->id,

        ]);

        foreach ($request->detalles as $d) {
            DetalleSalida::create([
                'salida_id' => $salida->id,
                'producto_id' => $d['producto_id'],
                'cant_salida' => $d['cant_salida'],
                'precio' => $d['precio']
            ]);
        }

        return redirect()->route('salidas.index')
            ->with('success', 'Salida registrada correctamente.');
    }

    public function show(Salida $salida)
    {
        // Cargar relaciones
        $salida->load([
            'almacen',
            'operador',
            'transportista',
            'tipoSalida',
            'vehiculo',
            'detalles.producto'
        ]);

        // Calcular total
        $monto_total = $salida->detalles->sum(function ($d) {
            return $d->cant_salida * $d->precio;
        });

        // Nombre del punto de venta (provisional)
        $puntoVentaNombre = collect($this->puntos_ventas)
            ->firstWhere('id', $salida->punto_venta_id)['nombre'] ?? 'No asignado';

        return view('salidas.show', [
            'salida' => $salida,
            'monto_total' => $monto_total,
            'puntoVentaNombre' => $puntoVentaNombre
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Salida $salida)
    {
        $user = Auth::user();

        // Solo propietario o administrador pueden editar
        if (!$user->hasRole('propietario|administrador')) {
            return redirect()->route('salidas.index')
                ->with('error', 'No tiene permiso para editar salidas.');
        }

        // Cargar relaciones
        $salida->load(['detalles.producto']);

        // Para obtener almacenes/usuarios/vehículos según propietario
        $propietario = $user->parent ?? $user;

        $almacenes = Almacen::where('user_id', $propietario->id)->get();

        $operadores = User::where('user_id', $propietario->id)
            ->role('operador')->get();

        $transportistas = User::where('user_id', $propietario->id)
            ->role('transportista')->get();

        $vehiculos = Vehiculo::all();
        $tiposSalida = TipoSalida::all();
        $productos = Producto::all();

        $puntosVenta = $this->puntos_ventas;

        return view('salidas.edit', compact(
            'salida',
            'almacenes',
            'operadores',
            'transportistas',
            'vehiculos',
            'tiposSalida',
            'productos',
            'puntosVenta'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Salida $salida)
    {
        $user = Auth::user();

        if (!$user->hasRole('propietario|administrador')) {
            return redirect()->route('salidas.index')
                ->with('error', 'No autorizado para actualizar salidas.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date',
            'almacen_id' => 'required|integer',
            'operador_id' => 'required|integer',
            'transportista_id' => 'required|integer',
            'vehiculo_id' => 'required|integer',
            'tipo_salida_id' => 'required|integer',
            'punto_venta_id' => 'required|integer',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|integer',
            'detalles.*.cant_salida' => 'required|numeric|min:0.01',
            'detalles.*.precio' => 'required|numeric|min:0.0001'
        ]);

        $salida->update([
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'almacen_id' => $request->almacen_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
            'punto_venta_id' => $request->punto_venta_id,
            'tipo_salida_id' => $request->tipo_salida_id,
            'vehiculo_id' => $request->vehiculo_id,
        ]);

        // Borrar detalles previos
        DetalleSalida::where('salida_id', $salida->id)->delete();

        // Registrar nuevos detalles
        foreach ($request->detalles as $d) {
            DetalleSalida::create([
                'salida_id' => $salida->id,
                'producto_id' => $d['producto_id'],
                'cant_salida' => $d['cant_salida'],
                'precio' => $d['precio'],
            ]);
        }

        return redirect()->route('salidas.show', $salida->id)
            ->with('success', 'Salida actualizada correctamente.');
    }

    public function cambiarEstado(Request $request, Salida $salida)
    {
        // dd($salida);
        $accion = $request->accion;

        if (!Auth::user()->hasRole('administrador|propietario')) {
            return back()->with('error', 'No autorizado.');
        }

        if ($accion == 'confirmar' && $salida->estado == 1) {
            $salida->estado = 2;
        } elseif ($accion == 'anular' && $salida->estado == 1) {
            $salida->estado = 4;
        }

        $salida->save();

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function pdf(Salida $salida)
    {
        $salida->load([
            'almacen:id,nombre',
            'operador:id,full_name',
            'transportista:id,full_name',
            'administrador:id,full_name',
            'vehiculo:id,placa_identificacion',
            'tipoSalida:id,nombre',
            'detalles.producto:id,nombre,unidad_medida_id',
            'detalles.producto.unidadMedida:id,cod_unidad_medida'
        ]);

        $monto_total = $salida->detalles->sum(fn($d) => $d->cant_salida * $d->precio);

        $puntoVentaNombre = collect($this->puntos_ventas)
            ->firstWhere('id', $salida->punto_venta_id)['nombre'] ?? 'No definido';

        $empresa = User::find(Auth::user()->user_id);
        $propietario = $empresa;

        $pdf = Pdf::loadView('salidas.comprobante-pdf', [
            'salida' => $salida,
            'monto_total' => $monto_total,
            'puntoVentaNombre' => $puntoVentaNombre,
            'empresa' => $empresa,
            'propietario' => $propietario
        ])->setPaper('a4');

        // ================= PIE DE PÁGINA ==================
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Texto izquierda
        $canvas->page_text(25, $h - 35, "Solicitado por: " . ($salida->administrador->full_name ?? '-') .
            "    |    Fecha: " . now()->format('d/m/Y'), null, 9, [0, 0, 0]);

        // Numeración derecha
        $canvas->page_text($w - 120, $h - 35, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, [0, 0, 0]);

        return $pdf->stream("Salida_{$salida->codigo_comprobante}.pdf");
    }
}
