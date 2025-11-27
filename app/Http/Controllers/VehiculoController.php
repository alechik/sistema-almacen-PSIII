<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehiculos = Vehiculo::orderBy('id', 'asc')->paginate(10);

        return view('vehiculos.index', compact('vehiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('vehiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'placa_identificacion' => 'required|string|max:50|unique:vehiculos,placa_identificacion',
            'marca_modelo' => 'required|string|max:150',
            'anio' => 'nullable|string|max:20',
        ], [
            'placa_identificacion.required' => 'La placa de identificación es obligatoria.',
            'placa_identificacion.max' => 'La placa no debe superar los 50 caracteres.',
            'placa_identificacion.unique' => 'Ya existe un vehículo con esa placa.',

            'marca_modelo.required' => 'El campo marca/modelo es obligatorio.',
            'marca_modelo.max' => 'La marca/modelo no debe superar los 150 caracteres.',

            'anio.max' => 'El año no debe superar los 20 caracteres.',
        ]);

        // Crear vehiculo
        Vehiculo::create([
            'placa_identificacion' => $request->placa_identificacion,
            'marca_modelo' => $request->marca_modelo,
            'anio' => $request->anio,
        ]);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        return view('vehiculos.show', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        return view('vehiculos.edit', compact('vehiculo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'placa_identificacion' => 'required|string|max:50|unique:vehiculos,placa_identificacion,' . $vehiculo->id,
            'marca_modelo' => 'required|string|max:150',
            'anio' => 'nullable|string|max:20',
        ], [
            'placa_identificacion.required' => 'La placa de identificación es obligatoria.',
            'placa_identificacion.max' => 'La placa no debe superar los 50 caracteres.',
            'placa_identificacion.unique' => 'Ya existe un vehículo con esa placa.',

            'marca_modelo.required' => 'El campo marca/modelo es obligatorio.',
            'marca_modelo.max' => 'La marca/modelo no debe superar los 150 caracteres.',

            'anio.max' => 'El año no debe superar los 20 caracteres.',
        ]);

        // Actualizar vehículo
        $vehiculo->update([
            'placa_identificacion' => $request->placa_identificacion,
            'marca_modelo' => $request->marca_modelo,
            'anio' => $request->anio,
        ]);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        try {
            $vehiculo->delete();

            return redirect()
                ->route('vehiculos.index')
                ->with('success', 'Vehículo eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('vehiculos.index')
                ->with('error', 'No se pudo eliminar el vehículo. Verifique si está relacionado con otros registros.');
        }
    }
}
