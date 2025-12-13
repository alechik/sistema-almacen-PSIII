<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['producto_id']);
        });
        
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            // Hacer producto_id nullable para permitir productos de Trazabilidad
            $table->unsignedBigInteger('producto_id')->nullable()->change();
            
            // Agregar campo para ID del producto en Trazabilidad
            $table->unsignedBigInteger('producto_trazabilidad_id')->nullable()->after('producto_id');
            
            // Agregar campo para nombre del producto (desde Trazabilidad)
            $table->string('producto_nombre')->nullable()->after('producto_trazabilidad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropColumn(['producto_nombre', 'producto_trazabilidad_id']);
            // Nota: No revertimos producto_id a NOT NULL para evitar problemas con datos existentes
        });
    }
};
