<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $user = Auth::user();

        // PROPIETARIO → ve los pedidos de todos sus administradores
        if ($user->hasRole('propietario')) {

            // IDs de todos sus administradores
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            $pedidos = Pedido::whereIn('administrador_id', $admins)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // ADMINISTRADOR → SOLO sus pedidos
        else if ($user->hasRole('administrador')) {

            $pedidos = Pedido::where('administrador_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // OPERADOR → pedidos donde participa
        else if ($user->hasRole('operador')) {

            $pedidos = Pedido::where('operador_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // TRANSPORTISTA → pedidos donde participa
        else if ($user->hasRole('transportista')) {

            $pedidos = Pedido::where('transportista_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // OTROS (por si existiera un rol raro)
        else {
            $pedidos = collect(); // vacío
        }

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'proveedores' => $this->proveedores
        ]);
    }

    public function create()
    {
        $admin = Auth::user();
        $propietarioId = $admin->user_id ?? $admin->id;

        $lastId = Pedido::max('id') ?? 0;

        // Almacenes asignados al administrador
        $almacenes = $admin->almacenes;

        // Proveedores
        $proveedores = $this->proveedores;

        return view('pedidos.create', compact('almacenes', 'proveedores', 'lastId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'codigo_comprobante' => 'required|string',
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

        $codigo = 'P' . ($propietarioId * 1000000 + $newId);

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
            // 🔒 Marcar producto como "en pedido"
            $pedido->almacen->productos()
                ->updateExistingPivot(
                    $item['producto_id'],
                    ['en_pedido' => 1]
                );
        }

        return redirect()->route('pedidos.index')
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

        $propietarioId = $admin->hasRole('propietario')
            ? $admin->id
            : $admin->user_id;

        // Almacenes del propietario
        $almacenes = Almacen::where('user_id', $propietarioId)->get();

        // Operadores del propietario
        $operadores = User::role('operador')
            ->where('user_id', $propietarioId)
            ->get();

        // Transportistas del propietario
        $transportistas = User::role('transportista')
            ->where('user_id', $propietarioId)
            ->get();

        // Productos filtrados por proveedor actual del pedido
        $productos = Producto::where('proveedor_id', $pedido->proveedor_id)
            ->where('estado', 1)
            ->get();

        return view('pedidos.edit', compact(
            'pedido',
            'almacenes',
            'operadores',
            'transportistas',
            'productos'
        ) + ['proveedores' => $this->proveedores]);
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

        // CABECERA
        $pedido->update([
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha_min,
            'fecha_max' => $request->fecha_max,
            'almacen_id' => $request->almacen_id,
            'proveedor_id' => $request->proveedor_id,
            'operador_id' => $request->operador_id,
            'transportista_id' => $request->transportista_id,
        ]);

        // DETALLE (Reemplazo total)
        DetallePedido::where('pedido_id', $pedido->id)->delete();

        foreach ($request->productos as $item) {
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $item['producto_id'],
                'cantidad' => $item['cantidad'],
            ]);
        }

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente.');
    }


    public function createFromStockMinimo(Almacen $almacen, $proveedorId)
    {
        // dd($almacen);
        $admin = Auth::user();

        if (!$admin->hasRole('administrador')) {
            abort(403);
        }

        // 🔹 Productos con stock mínimo del MISMO almacén y proveedor
        $productos = Producto::where('proveedor_id', $proveedorId)
            ->whereHas('almacenes', function ($q) use ($almacen) {
                $q->where('almacen_id', $almacen->id)
                    ->whereColumn('stock', '<=', 'stock_minimo')
                    ->where('en_pedido', 0);
            })
            ->with(['almacenes' => function ($q) use ($almacen) {
                $q->where('almacen_id', $almacen->id);
            }])
            ->get();

        if ($productos->isEmpty()) {
            return back()->with('error', 'No hay productos con stock mínimo para este proveedor.');
        }

        // Operadores y transportistas del almacén
        $operadores = User::role('operador')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacen->id))
            ->get();

        $transportistas = User::role('transportista')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacen->id))
            ->get();
        // dd($operadores);
        $lastId = Pedido::max('id') ?? 0;

        return view('pedidos.create-stock-minimo', [
            'almacen'        => $almacen,
            'proveedorId'    => $proveedorId,
            'productos'      => $productos,
            'operadores'     => $operadores,
            'transportistas' => $transportistas,
            'lastId'         => $lastId,
            'proveedores'    => $this->proveedores,
        ]);
    }





    public function confirmar(Pedido $pedido)
    {
        if ($pedido->estado != Pedido::EMITIDO) {
            return back()->with('error', 'Solo los pedidos EMITIDOS pueden ser confirmados.');
        }

        $pedido->update([
            'estado' => Pedido::CONFIRMADO
        ]);

        return back()->with('success', 'Pedido confirmado correctamente.');
    }

    public function anular(Pedido $pedido)
    {
        if ($pedido->estado == Pedido::TERMINADO) {
            return back()->with('error', 'No se puede anular un pedido TERMINADO.');
        }

        $pedido->update([
            'estado' => Pedido::ANULADO
        ]);

        return back()->with('success', 'Pedido anulado correctamente.');
    }

    // Obtener operadores y transportistas del almacén
    public function getUsuariosPorAlmacen($almacenId)
    {
        $operadores = User::role('operador')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacenId))
            ->select('id', 'full_name')
            ->get();

        $transportistas = User::role('transportista')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacenId))
            ->select('id', 'full_name')
            ->get();

        return response()->json([
            'operadores' => $operadores,
            'transportistas' => $transportistas
        ]);
    }

    // Obtener productos según proveedor
    public function getProductosPorProveedor($proveedorId)
    {
        $productos = Producto::where('proveedor_id', $proveedorId)
            ->where('estado', 1)
            ->select('id', 'nombre')
            ->get();

        return response()->json($productos);
    }



    public function generarPDF(Pedido $pedido)
    {
        $usuario = Auth::user();
        $empresa = $usuario->hasRole('propietario')
            ? $usuario
            : $usuario->parent;

        $pedido->load(['almacen', 'operador', 'transportista', 'administrador', 'detalles.producto']);
        $proveedores = $this->proveedores;

        $pdf = Pdf::loadView('pedidos.comprobante-pdf', compact('pedido', 'proveedores', 'empresa'))
            ->setPaper('letter', 'portrait');

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // === TEXTO IZQUIERDA DEL FOOTER (admin + fecha) ===
        $canvas->page_text(
            25,                   // X (parte izquierda)
            $h - 35,              // Y (bien abajo)
            "Solicitado por: " . ($pedido->administrador->full_name ?? '-') .
                "    |    Fecha: " . now()->format('d/m/Y'),
            null,
            9,
            [0, 0, 0]
        );

        // === NUMERACIÓN A LA DERECHA ===
        $canvas->page_text(
            $w - 120,            // X (derecha)
            $h - 35,             // misma altura exacta
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            null,
            9,
            [0, 0, 0]
        );

        return $pdf->stream("pedido-{$pedido->codigo_comprobante}.pdf");
    }
}
