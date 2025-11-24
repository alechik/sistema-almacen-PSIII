<?php

namespace App\Http\Controllers;

use App\Models\TipoSalida;
use Illuminate\Http\Request;

class TipoSalidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tipos = TipoSalida::orderBy('id', 'ASC')->paginate(10);

        return view('tiposalidas.index', compact('tipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tiposalidas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación con mensajes en español
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no debe exceder los 150 caracteres.',

            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ]);

        // Crear el registro
        TipoSalida::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        // Redirigir con mensaje de éxito
        return redirect()
            ->route('tiposalidas.index')
            ->with('success', 'Tipo de salida registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoSalida $tipoSalida)
    {
        return view('tiposalidas.show', compact('tipoSalida'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoSalida $tipoSalida)
    {
        return view('tiposalidas.edit', compact('tipoSalida'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoSalida $tipoSalida)
    {
        // Validación con mensajes personalizados en español
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no debe exceder los 150 caracteres.',

            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ]);

        // Actualizar registro
        $tipoSalida->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()
            ->route('tiposalidas.index')
            ->with('success', 'Tipo de salida actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoSalida $tipoSalida)
    {
        $tipoSalida->delete();

        return redirect()
            ->route('tiposalidas.index')
            ->with('success', 'Tipo de salida eliminado correctamente.');
    }
}
