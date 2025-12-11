<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if (Auth::user()->hasRole('admin')) {
            $users= User::propietarios()->get();
            // dd($users);
            return redirect()->intended(route('users.index', absolute: false))->with('users',$users);
        } 
        if (Auth::user()->hasRole('propietario')) {
             $users = User::where('user_id', $user->id)->get();
             $cantidad = $users->count();
            return redirect()->intended(route('dashboard', absolute: false))->with('cantidad',$cantidad);;
        }
        if (Auth::user()->hasRole('administrador')) {
            
            $users = User::where('user_id', $user->parent->id)->get();
            $cantidad = $users->count();
            // dd($cantidad);
            return redirect()->intended(route('dashboard', absolute: false))->with('cantidad',$cantidad);
        }
                    

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
