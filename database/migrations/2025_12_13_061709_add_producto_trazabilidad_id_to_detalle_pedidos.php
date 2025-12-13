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
        // Verificar si la columna ya existe antes de agregarla
        if (!Schema::hasColumn('detalle_pedidos', 'producto_trazabilidad_id')) {
            Schema::table('detalle_pedidos', function (Blueprint $table) {
                $table->unsignedBigInteger('producto_trazabilidad_id')->nullable()->after('producto_id');
            });
        }
        
        // Verificar si producto_nombre existe, si no, agregarlo
        if (!Schema::hasColumn('detalle_pedidos', 'producto_nombre')) {
            Schema::table('detalle_pedidos', function (Blueprint $table) {
                $table->string('producto_nombre')->nullable()->after('producto_trazabilidad_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_pedidos', 'producto_trazabilidad_id')) {
                $table->dropColumn('producto_trazabilidad_id');
            }
            if (Schema::hasColumn('detalle_pedidos', 'producto_nombre')) {
                $table->dropColumn('producto_nombre');
            }
        });
    }
};
