<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlmacenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // dd($user);
        // Si es propietario → ve solo los almacenes que él creó
        if ($user->hasRole('propietario')) { //Cambiar a propietario luego
            $almacenes = Almacen::where('user_id', $user->id)
                ->orderBy('id', 'asc')
                ->paginate(10);
        }
        // Si es trabajador → ve solo los almacenes asignados
        else {
            $almacenes = $user->almacenes()
                ->orderBy('almacen.id', 'DESC')
                ->paginate(10);
        }

        return view('almacenes.index', compact('almacenes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('almacenes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|max:150',
            'email'       => 'nullable|email|max:150',
            'estado' => 'required|in:ACTIVADO,DESACTIVADO,CERRADO',
            'ubicacion'   => 'nullable',
            'longitud'    => 'nullable|max:50',
            'latitud'     => 'nullable|max:50',
            'cellphone'   => 'nullable|max:50',
        ]);

        $almacen = Almacen::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'ubicacion'   => $request->ubicacion,
            'longitud'    => $request->longitud,
            'latitud'     => $request->latitud,
            'estado'      => $request->estado,
            'email'       => $request->email,
            'cellphone'   => $request->cellphone,
            'user_id'     => Auth::id(),
        ]);

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Almacen $almacen)
    {
        $almacen = Almacen::findOrFail($almacen->id);
        return view('almacenes.show', compact('almacen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Almacen $almacen)
    {
        return view('almacenes.edit', compact('almacen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Almacen $almacen)
    {
        $almacen = Almacen::findOrFail($almacen->id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:200',
            'longitud' => 'nullable|string|max:50',
            'latitud' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'cellhphone' => 'nullable|string|max:50',
        ]);

        $almacen->update($request->all());


        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Almacen $almacen)
    {
        $almacen = Almacen::findOrFail($almacen->id);
        $almacen->delete();

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén eliminado correctamente.');
    }
}
