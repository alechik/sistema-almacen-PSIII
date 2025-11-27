<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los roles con paginación
        $roles = ModelsRole::paginate(10);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación personalizada en español
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:roles,name'
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string' => 'El nombre del rol debe ser un texto válido.',
            'name.max' => 'El nombre del rol no debe superar los 50 caracteres.',
            'name.unique' => 'Este nombre de rol ya está registrado.',
        ]);

        // Crear el rol
        ModelsRole::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelsRole $role)
    {
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModelsRole $role)
    {
        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelsRole $role)
    {

        // Validación personalizada en español
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:roles,name,' . $role->id
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string' => 'El nombre del rol debe ser un texto válido.',
            'name.max' => 'El nombre del rol no debe superar los 50 caracteres.',
            'name.unique' => 'Este nombre de rol ya está registrado.',
        ]);

        // Actualizar el rol
        $role->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelsRole $role)
    {
        // Validación: no se puede eliminar un rol asignado a usuarios
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'No es posible eliminar este rol porque está asignado a uno o más usuarios.');
        }

        // Eliminar relación con permisos antes de borrar
        $role->syncPermissions([]);

        // Eliminación del rol
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
