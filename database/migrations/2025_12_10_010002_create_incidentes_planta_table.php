<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Incidentes reportados en envíos de planta
     */
    public function up(): void
    {
        Schema::create('incidentes_planta', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('incidente_planta_id')->unique()->comment('ID del incidente en PlantaCruds');
            
            $table->unsignedBigInteger('envio_planta_id');
            $table->foreign('envio_planta_id')->references('id')->on('envios_planta')->onDelete('cascade');
            
            $table->string('tipo_incidente')->comment('producto_danado, faltante, demora, accidente, otro');
            $table->text('descripcion')->nullable();
            $table->string('foto_url')->nullable();
            
            $table->string('estado')->default('pendiente')->comment('pendiente, en_proceso, resuelto');
            
            $table->timestamp('fecha_reporte')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('notas_resolucion')->nullable();
            
            $table->boolean('visto')->default(false);
            
            $table->timestamps();
            
            $table->index('estado');
            $table->index('envio_planta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes_planta');
    }
};

