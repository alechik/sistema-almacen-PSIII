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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('trazabilidad_tracking_id')->nullable()->after('estado')->comment('ID del pedido en Trazabilidad');
            $table->string('trazabilidad_estado')->nullable()->after('trazabilidad_tracking_id')->comment('Estado en Trazabilidad (pendiente, aprobado, rechazado)');
            $table->boolean('enviado_a_trazabilidad')->default(false)->after('trazabilidad_estado')->comment('Si ya fue enviado a Trazabilidad');
            $table->timestamp('fecha_envio_trazabilidad')->nullable()->after('enviado_a_trazabilidad')->comment('Fecha de envío a Trazabilidad');
            $table->timestamp('fecha_respuesta_trazabilidad')->nullable()->after('fecha_envio_trazabilidad')->comment('Fecha de respuesta de Trazabilidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'trazabilidad_tracking_id',
                'trazabilidad_estado',
                'enviado_a_trazabilidad',
                'fecha_envio_trazabilidad',
                'fecha_respuesta_trazabilidad'
            ]);
        });
    }
};
