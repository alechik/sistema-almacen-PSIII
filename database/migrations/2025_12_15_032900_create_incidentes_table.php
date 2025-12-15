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
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->integer('envio_id')->comment('ID del envío en plantaCruds');
            $table->string('envio_codigo')->comment('Código del envío');
            $table->integer('incidente_id_planta')->comment('ID del incidente en plantaCruds');
            $table->integer('transportista_id')->nullable();
            $table->string('transportista_nombre')->nullable();
            $table->string('tipo_incidente');
            $table->text('descripcion');
            $table->string('foto_url')->nullable();
            $table->enum('accion', ['cancelar', 'continuar']);
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto'])->default('pendiente');
            $table->decimal('ubicacion_lat', 10, 8)->nullable();
            $table->decimal('ubicacion_lng', 11, 8)->nullable();
            $table->timestamp('fecha_reporte');
            $table->text('notas_resolucion')->nullable();
            $table->timestamps();
            
            $table->index(['pedido_id', 'envio_id']);
            $table->index(['estado', 'fecha_reporte']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
