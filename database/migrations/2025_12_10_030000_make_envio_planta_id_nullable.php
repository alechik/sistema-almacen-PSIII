<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hacer envio_planta_id nullable para pedidos creados localmente
     * que aún no han sido sincronizados con Planta
     */
    public function up(): void
    {
        Schema::table('envios_planta', function (Blueprint $table) {
            // Eliminar la restricción unique si existe
            $table->dropUnique(['envio_planta_id']);
        });

        Schema::table('envios_planta', function (Blueprint $table) {
            // Cambiar a nullable
            $table->unsignedBigInteger('envio_planta_id')->nullable()->change();
            
            // Agregar unique index que permite nulls
            $table->unique('envio_planta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios_planta', function (Blueprint $table) {
            $table->dropUnique(['envio_planta_id']);
            $table->unsignedBigInteger('envio_planta_id')->unique()->change();
        });
    }
};

