<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
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
        // Consulta con paginación y relaciones
        $productos = Producto::with(['categoria'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        // dd($proveedors=$this->proveedores[0]['nombre']);
        return view('productos.index', compact('productos'), ['proveedores' => $this->proveedores]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $proveedores = $this->proveedores;
        $unidades = UnidadMedida::orderBy('cod_unidad_medida')->get();

        return view('productos.create', compact('categorias', 'proveedores', 'unidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cod_producto' => 'required|string|max:100|unique:productos,cod_producto',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'proveedor_id' => 'required|integer',
            'unidad_medida_id' => 'required|integer|exists:unidad_medidas,id',

            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'fech_vencimiento' => 'nullable|date',
        ];

        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'cod_producto.required' => 'El código del producto es obligatorio.',
            'cod_producto.unique' => 'Este código ya está registrado.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'unidad_medida_id.required' => 'Debe seleccionar una unidad de medida.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no es válida.',
        ];

        $request->validate($rules, $messages);

        Producto::create($request->all());

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'unidadMedida']);

        $proveedor = collect($this->proveedores)->firstWhere('id', $producto->proveedor_id);

        return view('productos.show', compact('producto', 'proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $proveedores = $this->proveedores;
        $unidades = UnidadMedida::orderBy('cod_unidad_medida')->get();

        return view('productos.edit', compact('producto', 'categorias', 'proveedores', 'unidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $rules = [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cod_producto' => 'required|string|max:100|unique:productos,cod_producto,' . $producto->id,
            'categoria_id' => 'required|integer|exists:categorias,id',
            'proveedor_id' => 'required|integer',
            'unidad_medida_id' => 'required|integer|exists:unidad_medidas,id',

            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'fech_vencimiento' => 'nullable|date',
        ];

        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'cod_producto.required' => 'El código del producto es obligatorio.',
            'cod_producto.unique' => 'Este código ya está registrado.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'unidad_medida_id.required' => 'Debe seleccionar una unidad de medida.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no es válida.',
        ];

        $request->validate($rules, $messages);

        $producto->update($request->all());

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        // Verificar si el producto tiene el atributo estado
        if ($producto->getAttribute('estado') !== null) {

            // Si el producto está ACTIVO → se intenta desactivar
            if ($producto->estado == 1 || $producto->estado == 'activo') {

                // Verificación de stock
                if ($producto->stock > 0) {
                    return redirect()
                        ->route('productos.index')
                        ->with('error', 'No se puede desactivar el producto porque tiene stock mayor a 0.');
                }

                // Stock 0 → se puede desactivar
                $producto->estado = 0;
                $producto->save();

                return redirect()
                    ->route('productos.index')
                    ->with('success', 'Producto desactivado correctamente.');
            }

            // Si está INACTIVO → se reactiva
            $producto->estado = 1;
            $producto->save();

            return redirect()
                ->route('productos.index')
                ->with('success', 'Producto activado correctamente.');
        }
    }
    public function generarCodigo($categoria_id)
    {
        $categoria = Categoria::findOrFail($categoria_id);

        // Inicial de categoría
        $inicial = strtoupper(substr($categoria->nombre, 0, 1));

        // Cantidad de productos en esa categoría → correlativo
        $contador = Producto::where('categoria_id', $categoria_id)->count() + 1;

        // Formato 0001
        // $correlativo = str_pad($contador, 4, '0', STR_PAD_LEFT);

        // Código final
        $codigo = $inicial . (($categoria_id * 1000) + $contador);

        return response()->json(['codigo' => $codigo]);
    }
}
