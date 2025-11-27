<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
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

        return view('productos.create', compact('categorias', 'proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Reglas de validación
        $rules = [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cod_producto' => 'required|string|max:100|unique:productos,cod_producto',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'proveedor_id' => 'required|integer',
            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'fech_vencimiento' => 'nullable|date',
        ];

        // Mensajes personalizados en español
        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'cod_producto.required' => 'El código del producto es obligatorio.',
            'cod_producto.unique' => 'Este código ya está registrado.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'stock.min' => 'El stock no puede ser negativo.',
            'precio.min' => 'El precio no puede ser negativo.',
        ];

        Validator::make($request->all(), $rules, $messages)->validate();

        // Guardar datos
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
        $proveedor = collect($this->proveedores)
            ->firstWhere('id', $producto->proveedor_id);

        return view('productos.show', compact('producto', 'proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $proveedores = $this->proveedores;

        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        // Reglas de validación
        $rules = [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cod_producto' => 'required|string|max:100|unique:productos,cod_producto,' . $producto->id,
            'categoria_id' => 'required|integer|exists:categorias,id',
            'proveedor_id' => 'required|integer',
            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'fech_vencimiento' => 'nullable|date',
        ];

        // Mensajes personalizados en español
        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'cod_producto.required' => 'El código del producto es obligatorio.',
            'cod_producto.unique' => 'Este código ya está registrado.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'stock.min' => 'El stock no puede ser negativo.',
            'precio.min' => 'El precio no puede ser negativo.',
        ];

        $request->validate($rules, $messages);

        // Actualizar producto
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
        // Si el producto tiene el atributo estado → lo desactiva
        if ($producto->getAttribute('estado') !== null) {

            // Si el producto está activo → desactiva
            if ($producto->estado == 1 || $producto->estado == 'activo') {
                $producto->estado = 0; // o 'inactivo'
            } else {
                $producto->estado = 1; // Permite reactivar si lo deseas
            }

            $producto->save();

            return redirect()
                ->route('productos.index')
                ->with('success', 'El estado del producto ha sido actualizado correctamente.');
        }

        // Si no tiene estado → se realiza eliminación real
        $producto->delete();

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
