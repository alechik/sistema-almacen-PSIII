<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // dd($user->id);
        if ($user->hasRole('admin')) {
            // dd($user);
            // $users = User::where('id', $user->id)->paginate(10);
            $users = User::propietarios()->paginate(10);
        } elseif ($user->hasRole('propietario')) {
            $users = User::where('user_id', $user->id)->paginate(10);
        } else {
            $users = User::where('id', $user->id)->paginate(10);
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Roles disponibles según quién está creando
        if ($user->hasRole('admin')) {
            $roles = ['propietario']; // Solo puede crear propietarios
        } elseif ($user->hasRole('propietario')) {
            $roles = ['administrador', 'operador', 'transportista']; // Trabajadores
        } else {
            abort(403, 'No tienes permisos para crear usuarios.');
        }
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validación de roles para creación
        if ($user->hasRole('admin')) {
            $availableRoles = ['propietario'];
        } elseif ($user->hasRole('propietario')) {
            $availableRoles = ['administrador', 'operador', 'transportista'];
        } else {
            return back()->with('error', 'No tienes permisos para esta acción');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'name' => 'required|string|max:100|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'company' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|in:' . implode(',', $availableRoles),
        ], [
            'full_name.required' => 'El nombre completo es obligatorio.',
            'name.required' => 'El nombre de usuario es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'role.required' => 'Debe seleccionar un rol.',
        ]);

        // Crear usuario
        $nuevoUsuario = User::create([
            'full_name' => $request->full_name,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'company' => $request->company,
            'phone_number' => $request->phone_number,
            'estado' => 'ACTIVO', // ESTADOS:ACTIVO, PENDIENTE, NO ACTIVO
            'user_id' => $user->id, // Propietario que lo crea
        ]);

        // Asignar Rol
        $nuevoUsuario->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $auth = Auth::user();

        // Validación de permisos
        if (
            !$auth->hasRole('admin') &&
            !$auth->hasRole('propietario') &&
            $auth->id !== $user->id
        ) {
            abort(403, 'No tienes permisos para ver este usuario.');
        }

        // Roles asignados al usuario consultado
        $roles = $user->getRoleNames();

        return view('users.show', compact('user', 'roles'));
    }

    public function edit(User $user)
    {
        $auth = Auth::user();

        // Permisos para edición
        if (
            !$auth->hasRole('admin') &&
            !$auth->hasRole('propietario') &&
            $auth->id !== $user->id // Solo puede editarse a sí mismo
        ) {
            abort(403, 'No tienes permisos para editar este usuario.');
        }

        // Roles disponibles según el editor
        if ($auth->hasRole('admin')) {
            $roles = ['propietario'];
        } elseif ($auth->hasRole('propietario')) {
            $roles = ['administrador', 'operador', 'transportista'];
        } else {
            $roles = [$user->roles->first()->name]; // No puede cambiar rol
        }

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $auth = Auth::user();

        // Roles permitidos según quien edita
        if ($auth->hasRole('admin')) {
            $availableRoles = ['propietario'];
        } elseif ($auth->hasRole('propietario')) {
            $availableRoles = ['administrador', 'operador', 'transportista'];
        } else {
            $availableRoles = [$user->roles->first()->name];
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'name' => 'required|string|max:100|unique:users,name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'estado' => 'required|in:ACTIVO,PENDIENTE,NO ACTIVO',
            'role' => 'required|in:' . implode(',', $availableRoles),
        ]);

        // Actualización base
        $user->update([
            'full_name' => $request->full_name,
            'name' => $request->name,
            'email' => $request->email,
            'company' => $request->company,
            'phone_number' => $request->phone_number,
            'estado' => $request->estado,
        ]);

        // Si cambia contraseña
        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
        }

        // Actualizar Rol
        if ($auth->hasRole('admin') || $auth->hasRole('propietario')) {
            $user->syncRoles([$request->role]);
        }

        // Redirecciones dinámicas
        if ($auth->id === $user->id && !$auth->hasAnyRole(['admin', 'propietario'])) {
            return redirect()->route('dashboard')->with('success', 'Perfil actualizado correctamente');
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $auth = Auth::user();

        // Verificar si tiene rol permitido
        if (!$auth->hasAnyRole(['propietario', 'admin'])) {
            return redirect()->route('users.index')
                ->with('error', 'No tienes permisos para cambiar el estado de usuarios.');
        }

        // Impedir que un usuario se desactive a sí mismo
        if ($auth->id === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes cambiar el estado de tu propio usuario.');
        }

        // Cambiar estado dinámico
        if ($user->estado === 'ACTIVO') {
            $user->estado = 'NO ACTIVO';
            $estadoMensaje = 'desactivado';
        } else {
            $user->estado = 'ACTIVO';
            $estadoMensaje = 'activado';
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', "Usuario {$estadoMensaje} correctamente.");
    }

    public function edittwo(User $user)
    {
        // dd($user);
        $auth = Auth::user();

        $roles=[$user->roles->first()->name];
        // dd($roles); 

        return view('users.edittwo', compact('user', 'roles'));
    }

    public function updatetwo(Request $request, User $user)
    {
        $auth = Auth::user();

        // // Roles permitidos según quien edita
        // if ($auth->hasRole('admin')) {
        //     $availableRoles = ['propietario'];
        // } elseif ($auth->hasRole('propietario')) {
        //     $availableRoles = ['administrador', 'operador', 'transportista'];
        // } else {
        //     $availableRoles = [$user->roles->first()->name];
        // }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'name' => 'required|string|max:100|unique:users,name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            // 'role' => 'required|in:' . implode(',', $availableRoles),
        ]);

        // Actualización base
        $user->update([
            'full_name' => $request->full_name,
            'name' => $request->name,
            'email' => $request->email,
            'company' => $request->company,
            'phone_number' => $request->phone_number,
            'estado' => $user->estado,
        ]);

        // Si cambia contraseña
        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
        }

        // Actualizar Rol
        // if ($auth->hasRole('admin') || $auth->hasRole('propietario')) {
        //     $user->syncRoles([$request->role]);
        // }

        // Redirecciones dinámicas
        if ($auth->hasRole(['admin', 'propietario'])) {
            return redirect()->route('dashboard')->with('success', 'Perfil actualizado correctamente');
        }
        if ($auth->hasRole('admin')) {
            dd( 'llego admin');
            return redirect()->route('dashboard')->with('success', 'Perfil actualizado correctamente');
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
    }
}
