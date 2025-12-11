<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\AlmacenUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlmacenUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asignaciones = AlmacenUser::with(['user', 'almacen'])
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('almacen-users.index', compact('asignaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        //el propietario crea los almacenes, y entonces el almacen tiene un user_id que es del propietario
        // entonces el usuario puede ver todos los almacenes y usuarios que él ha registrado

        $usuarios = User::where('user_id', $user->id)->orderBy('name', 'ASC')->get();
        $almacenes = Almacen::where('user_id', $user->id)->orderBy('nombre', 'ASC')->get();
        // dd($usuarios);
        return view('almacen-users.create', compact('usuarios', 'almacenes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate(
            [
                'user_id' => 'required|exists:users,id',
                'almacen_id' => 'required|exists:almacens,id',
            ],
            [
                'user_id.required' => 'Debe seleccionar un usuario.',
                'user_id.exists' => 'El usuario seleccionado no es válido.',
                'almacen_id.required' => 'Debe seleccionar un almacén.',
                'almacen_id.exists' => 'El almacén seleccionado no es válido.',
            ]
        );

        // Verificar si ya existe la asignación
        $existe = AlmacenUser::where('user_id', $request->user_id)
            ->where('almacen_id', $request->almacen_id)
            ->first();

        if ($existe) {
            return back()
                ->withInput()
                ->with('error', 'Este usuario ya está asignado a este almacén.');
        }

        // Guardar asignación
        AlmacenUser::create($validated);

        return redirect()
            ->route('almacen-users.index')
            ->with('success', 'Asignación registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AlmacenUser $almacenUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlmacenUser $almacenUser)
    {
        $user = Auth::user();

        // solo usuarios y almacenes registrados por el propietario
        $usuarios = User::where('user_id', $user->id)->orderBy('name', 'ASC')->get();
        $almacenes = Almacen::where('user_id', $user->id)->orderBy('nombre', 'ASC')->get();

        return view('almacen-users.edit', compact('almacenUser', 'usuarios', 'almacenes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlmacenUser $almacenUser)
    {
        // Validación
        $validated = $request->validate(
            [
                'user_id' => 'required|exists:users,id',
                'almacen_id' => 'required|exists:almacens,id',
            ],
            [
                'user_id.required' => 'Debe seleccionar un usuario.',
                'user_id.exists' => 'El usuario seleccionado no es válido.',
                'almacen_id.required' => 'Debe seleccionar un almacén.',
                'almacen_id.exists' => 'El almacén seleccionado no es válido.',
            ]
        );

        // Evitar duplicados (excepto el propio registro)
        $existe = AlmacenUser::where('user_id', $request->user_id)
            ->where('almacen_id', $request->almacen_id)
            ->where('id', '!=', $almacenUser->id)
            ->first();

        if ($existe) {
            return back()
                ->withInput()
                ->with('error', 'Esta asignación ya existe. No puede duplicarse.');
        }

        // Actualizar
        $almacenUser->update($validated);

        return redirect()
            ->route('almacen-users.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlmacenUser $almacenUser)
    {
        // Verificar que el registro exista
        if (!$almacenUser) {
            return redirect()
                ->route('almacen-users.index')
                ->with('error', 'La asignación que intenta eliminar no existe.');
        }

        // Eliminar asignación
        $almacenUser->delete();

        return redirect()
            ->route('almacen-users.index')
            ->with('success', 'Asignación eliminada correctamente.');
    }
}
