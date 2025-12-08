<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    // ESTADOS:ACTIVO, PENDIENTE, NO ACTIVO
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'name' => $request->name,
            'email' => $request->email,
            'company' => $request->company,
            'phone_number' => $request->phone_number,
            'estado' => 'PENDIENTE',
            'password' => Hash::make($request->password),
        ]);
        // Asignar rol propietario
        $user->assignRole('propietario');

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Tu cuenta fue creada y está en estado PENDIENTE. Un admin debe activarla.');

        // Auth::login($user);

        // return redirect(route('dashboard', absolute: false));
    }
}
