<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\AlmacenProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlmacenProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Traer todos los registros de almacen_producto con sus relaciones
        $almacenProductos = AlmacenProducto::with([
            'producto.unidadMedida',
            'producto.categoria',
            'almacen'
        ])
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('almacen-productos.index', compact('almacenProductos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->hasRole('propietario')) {
            $almacenes = $user->almacenesCreados()->get();
        } else {
            $almacenes = $user->almacenes()->get();
        }
        // dd($almacenes);
        $productos = Producto::orderBy('nombre', 'ASC')->get();

        return view('almacen-productos.create', compact('almacenes', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // VALIDACIÓN -----------------------------------------
        $validated = $request->validate(
            [
                'almacen_id'   => 'required|exists:almacens,id',
                'producto_id'  => 'required|exists:productos,id',

                'stock'        => 'required|numeric|min:0',
                'stock_minimo' => 'required|numeric|min:0',

                'en_pedido'    => 'required|in:0,1',
            ],
            [
                'almacen_id.required'   => 'Debe seleccionar un almacén.',
                'almacen_id.exists'     => 'El almacén seleccionado no es válido.',

                'producto_id.required'  => 'Debe seleccionar un producto.',
                'producto_id.exists'    => 'El producto seleccionado no es válido.',

                'stock.required'        => 'El campo stock es obligatorio.',
                'stock.numeric'         => 'El stock debe ser un número válido.',
                'stock.min'             => 'El stock no puede ser negativo.',

                'stock_minimo.required' => 'Debe especificar el stock mínimo.',
                'stock_minimo.numeric'  => 'El stock mínimo debe ser un número.',
                'stock_minimo.min'      => 'El stock mínimo no puede ser negativo.',

                'en_pedido.required'    => 'Debe seleccionar si está en pedido.',
                'en_pedido.in'          => 'El valor de en pedido no es válido.',
            ]
        );

        // VALIDAR QUE NO EXISTE ESTE PRODUCTO YA ASIGNADO A ESTE ALMACÉN -----
        $existe = AlmacenProducto::where('almacen_id', $request->almacen_id)
            ->where('producto_id', $request->producto_id)
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->with('error', 'Este producto ya está asignado a este almacén.');
        }

        // GUARDAR REGISTRO ----------------------------------------------------
        AlmacenProducto::create([
            'almacen_id'   => $request->almacen_id,
            'producto_id'  => $request->producto_id,
            'stock'        => $request->stock,
            'stock_minimo' => $request->stock_minimo,
            'en_pedido'    => $request->en_pedido,
        ]);

        return redirect()
            ->route('almacen-productos.index')
            ->with('success', 'Producto asignado correctamente al almacén.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AlmacenProducto $almacenProducto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlmacenProducto $almacenProducto)
    {
        $user = Auth::user();
        // Si es propietario, solo ve sus almacenes creados
        if ($user->hasRole('propietario')) {
            $almacenes = $user->almacenesCreados()->get();
        } else {
            $almacenes = $user->almacenes()->get();
        }

        // Lista de productos
        $productos = Producto::orderBy('nombre', 'ASC')->get();

        return view('almacen-productos.edit', compact('almacenes', 'productos', 'almacenProducto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlmacenProducto $almacenProducto)
    {
        // VALIDACIÓN -----------------------------------------
        $validated = $request->validate(
            [
                'almacen_id'   => 'required|exists:almacens,id',
                'producto_id'  => 'required|exists:productos,id',

                'stock'        => 'required|numeric|min:0',
                'stock_minimo' => 'required|numeric|min:0',

                'en_pedido'    => 'required|in:0,1',
            ],
            [
                'almacen_id.required'   => 'Debe seleccionar un almacén.',
                'almacen_id.exists'     => 'El almacén seleccionado no es válido.',

                'producto_id.required'  => 'Debe seleccionar un producto.',
                'producto_id.exists'    => 'El producto seleccionado no es válido.',

                'stock.required'        => 'El campo stock es obligatorio.',
                'stock.numeric'         => 'El stock debe ser un número válido.',
                'stock.min'             => 'El stock no puede ser negativo.',

                'stock_minimo.required' => 'Debe especificar el stock mínimo.',
                'stock_minimo.numeric'  => 'El stock mínimo debe ser un número.',
                'stock_minimo.min'      => 'El stock mínimo no puede ser negativo.',

                'en_pedido.required'    => 'Debe seleccionar si está en pedido.',
                'en_pedido.in'          => 'El valor de en pedido no es válido.',
            ]
        );

        // VALIDAR QUE NO SE DUPLIQUE LA ASIGNACIÓN ---------------------------------
        $existe = AlmacenProducto::where('almacen_id', $request->almacen_id)
            ->where('producto_id', $request->producto_id)
            ->where('id', '!=', $almacenProducto->id)
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->with('error', 'Este producto ya está asignado a este almacén.');
        }

        // ACTUALIZAR ---------------------------------------------------------------
        $almacenProducto->update([
            'almacen_id'   => $request->almacen_id,
            'producto_id'  => $request->producto_id,
            'stock'        => $request->stock,
            'stock_minimo' => $request->stock_minimo,
            'en_pedido'    => $request->en_pedido,
        ]);

        return redirect()
            ->route('almacen-productos.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlmacenProducto $almacenProducto)
    {
        // Validar si este producto tiene movimientos en ingresos o salidas
        $tieneIngresos = DB::table('detalle_ingresos')
            ->where('producto_id', $almacenProducto->producto_id)
            ->exists();

        $tieneSalidas = DB::table('detalle_salidas')
            ->where('producto_id', $almacenProducto->producto_id)
            ->exists();

        // SI TIENE MOVIMIENTOS NO SE PUEDE ELIMINAR
        if ($tieneIngresos || $tieneSalidas) {
            return redirect()->back()
                ->with('error', 'No es posible eliminar esta asignación porque el producto ya tiene movimientos de ingresos o salidas registrados.');
        }

        // BORRADO LÓGICO
        // OPCIÓN 1: eliminar físicamente porque no afecta histórico (si no tiene movimientos)
        $almacenProducto->delete();

        return redirect()
            ->route('almacen-productos.index')
            ->with('success', 'Asignación eliminada correctamente.');
    }
}
