<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidenteController extends Controller
{
    /**
     * Mostrar lista de incidentes
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener incidentes según el rol del usuario
        if ($user->hasRole('propietario')) {
            $admins = \App\Models\User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');
            
            $incidentes = Incidente::with('pedido')
                ->whereHas('pedido', function($query) use ($admins, $user) {
                    $query->whereIn('administrador_id', $admins)
                          ->orWhere('administrador_id', $user->id);
                })
                ->orderBy('fecha_reporte', 'desc')
                ->get();
        } else {
            $incidentes = Incidente::with('pedido')
                ->whereHas('pedido', function($query) use ($user) {
                    $query->where('administrador_id', $user->id);
                })
                ->orderBy('fecha_reporte', 'desc')
                ->get();
        }
        
        return view('incidentes.index', compact('incidentes'));
    }

    /**
     * Mostrar detalles de un incidente
     */
    public function show(Incidente $incidente)
    {
        $incidente->load('pedido');
        return view('incidentes.show', compact('incidente'));
    }

    /**
     * Ver foto del incidente
     */
    public function verFoto(Incidente $incidente)
    {
        if (!$incidente->foto_url || !Storage::exists($incidente->foto_url)) {
            abort(404, 'Foto no encontrada');
        }
        
        return response()->file(Storage::path($incidente->foto_url));
    }
}
