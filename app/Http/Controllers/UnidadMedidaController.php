<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unidadMedidas = UnidadMedida::orderBy('id', 'ASC')->paginate(10);
        return view('unidad-medidas.index', compact('unidadMedidas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unidad-medidas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Mensajes personalizados en español
        $messages = [
            'cod_unidad_medida.required' => 'El campo código es obligatorio.',
            'cod_unidad_medida.max' => 'El código no debe exceder los 10 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 255 caracteres.',
        ];

        // Validaciones
        $validated = $request->validate([
            'cod_unidad_medida' => 'required|max:10',
            'descripcion' => 'required|max:255',
        ], $messages);

        // Guardar en DB
        UnidadMedida::create($validated);

        return redirect()
            ->route('unidad-medidas.index')
            ->with('success', 'Unidad de Medida registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnidadMedida $unidadMedida)
    {
        return view('unidad-medidas.show', compact('unidadMedida'));
    }

    public function edit(UnidadMedida $unidadMedida)
    {
        return view('unidad-medidas.edit', compact('unidadMedida'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnidadMedida $unidadMedida)
    {
        // Mensajes personalizados en español
        $messages = [
            'cod_unidad_medida.required' => 'El campo código es obligatorio.',
            'cod_unidad_medida.max' => 'El código no debe exceder los 10 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 255 caracteres.',
        ];

        // Validaciones
        $validated = $request->validate([
            'cod_unidad_medida' => 'required|max:10',
            'descripcion' => 'required|max:255',
        ], $messages);

        // Actualizar registro
        $unidadMedida->update($validated);

        return redirect()
            ->route('unidad-medidas.index')
            ->with('success', 'Unidad de Medida actualizada correctamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnidadMedida $unidadMedida)
    {
        try {
            // Intentar eliminar la unidad de medida
            $unidadMedida->delete();

            return redirect()
                ->route('unidad-medidas.index')
                ->with('success', 'Unidad de Medida eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {

            // Error de restricción de clave foránea (23000)
            if ($e->getCode() == "23000") {
                return redirect()
                    ->route('unidad-medidas.index')
                    ->with('error', 'No se puede eliminar la Unidad de Medida porque está siendo utilizada en otros registros.');
            }

            // Otros errores no previstos
            return redirect()
                ->route('unidad-medidas.index')
                ->with('error', 'Ocurrió un error al intentar eliminar la Unidad de Medida.');
        }
    }
}
