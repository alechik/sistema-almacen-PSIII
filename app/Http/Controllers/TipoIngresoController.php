<?php

namespace App\Http\Controllers;

use App\Models\TipoIngreso;
use Illuminate\Http\Request;

class TipoIngresoController extends Controller
{
    public function index()
    {
        $tipos = TipoIngreso::orderBy('id', 'ASC')->paginate(10);

        return view('tipoingresos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipoingresos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no debe superar los 150 caracteres.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.'
        ]);

        TipoIngreso::create($validated);

        return redirect()->route('tipoingresos.index')
            ->with('success', 'El tipo de ingreso fue registrado correctamente.');
    }

    public function show(TipoIngreso $tipoIngreso)
    {
        return view('tipoingresos.show', compact('tipoIngreso'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoIngreso $tipoIngreso)
    {
        return view('tipoingresos.edit', compact('tipoIngreso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoIngreso $tipoIngreso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no debe superar los 150 caracteres.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.'
        ]);

        $tipoIngreso->update($validated);

        return redirect()->route('tipoingresos.index')
            ->with('success', 'El tipo de ingreso fue actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoIngreso $tipoIngreso)
    {

        $tipoIngreso->delete();

        return redirect()
            ->route('tipoingresos.index')
            ->with('success', 'El tipo de ingreso fue eliminado correctamente.');
    }
}
