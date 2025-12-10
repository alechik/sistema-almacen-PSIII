<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campo solicitante_id para vincular envíos con el usuario que los solicitó
     */
    public function up(): void
    {
        Schema::table('envios_planta', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitante_id')->nullable()->after('almacen_id')
                ->comment('Usuario que solicitó el pedido a planta');
            $table->foreign('solicitante_id')->references('id')->on('users')->onDelete('set null');
            
            // Agregar índice para búsquedas por solicitante
            $table->index('solicitante_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios_planta', function (Blueprint $table) {
            $table->dropForeign(['solicitante_id']);
            $table->dropIndex(['solicitante_id']);
            $table->dropColumn('solicitante_id');
        });
    }
};

