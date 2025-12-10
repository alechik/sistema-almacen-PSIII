<?php

namespace App\Policies;

use App\Models\EnvioPlanta;
use App\Models\User;

class EnvioPlantaPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier envío
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'propietario']);
    }

    /**
     * Determinar si el usuario puede ver un envío específico
     */
    public function view(User $user, EnvioPlanta $envioPlanta): bool
    {
        // Propietario puede ver todos los envíos de sus almacenes
        if ($user->hasRole('propietario')) {
            $almacenesIds = \App\Models\Almacen::where('user_id', $user->id)->pluck('id')->toArray();
            return in_array($envioPlanta->almacen_id, $almacenesIds);
        }

        // Administrador puede ver envíos de almacenes de su propietario
        if ($user->hasRole('administrador')) {
            $propietarioId = $user->user_id;
            $almacenesIds = \App\Models\Almacen::where('user_id', $propietarioId)->pluck('id')->toArray();
            return in_array($envioPlanta->almacen_id, $almacenesIds);
        }

        return false;
    }

    /**
     * Determinar si el usuario puede crear envíos
     */
    public function create(User $user): bool
    {
        return false; // Los envíos se crean desde Planta
    }

    /**
     * Determinar si el usuario puede actualizar envíos
     */
    public function update(User $user, EnvioPlanta $envioPlanta): bool
    {
        return false; // Los envíos se actualizan desde Planta
    }

    /**
     * Determinar si el usuario puede eliminar envíos
     */
    public function delete(User $user, EnvioPlanta $envioPlanta): bool
    {
        return false; // Los envíos no se eliminan
    }
}

