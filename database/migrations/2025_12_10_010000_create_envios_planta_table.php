<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para rastrear envíos provenientes de PlantaCruds
     */
    public function up(): void
    {
        Schema::create('envios_planta', function (Blueprint $table) {
            $table->id();
            
            // Datos del envío de Planta
            $table->unsignedBigInteger('envio_planta_id')->unique()->comment('ID del envío en PlantaCruds');
            $table->string('codigo')->unique()->comment('Código único del envío');
            
            // Relación con almacén local
            $table->unsignedBigInteger('almacen_id')->nullable();
            $table->foreign('almacen_id')->references('id')->on('almacens')->onDelete('set null');
            
            // Estado del envío
            $table->string('estado')->default('pendiente')
                ->comment('pendiente, asignado, aceptado, en_transito, entregado, cancelado, rechazado');
            
            // Fechas importantes
            $table->date('fecha_creacion')->nullable();
            $table->date('fecha_estimada_entrega')->nullable();
            $table->time('hora_estimada')->nullable();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_inicio_transito')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            
            // Datos del transportista y vehículo
            $table->string('transportista_nombre')->nullable();
            $table->string('transportista_telefono')->nullable();
            $table->string('vehiculo_placa')->nullable();
            $table->string('vehiculo_descripcion')->nullable();
            
            // Ubicación actual (para tracking en tiempo real)
            $table->decimal('ubicacion_lat', 10, 7)->nullable();
            $table->decimal('ubicacion_lng', 10, 7)->nullable();
            $table->timestamp('ubicacion_actualizada_at')->nullable();
            
            // Coordenadas de origen y destino
            $table->decimal('origen_lat', 10, 7)->nullable();
            $table->decimal('origen_lng', 10, 7)->nullable();
            $table->string('origen_direccion')->nullable();
            $table->decimal('destino_lat', 10, 7)->nullable();
            $table->decimal('destino_lng', 10, 7)->nullable();
            $table->string('destino_direccion')->nullable();
            
            // Totales
            $table->integer('total_cantidad')->default(0);
            $table->decimal('total_peso', 12, 3)->default(0);
            $table->decimal('total_precio', 12, 2)->default(0);
            
            // Observaciones y notas
            $table->text('observaciones')->nullable();
            $table->text('firma_transportista')->nullable();
            
            // Para rechazo/cancelación
            $table->timestamp('fecha_rechazo')->nullable();
            $table->text('motivo_rechazo')->nullable();
            
            // Control
            $table->boolean('visto')->default(false)->comment('Si el almacén ya vio este envío');
            $table->timestamp('sincronizado_at')->nullable()->comment('Última sincronización con Planta');
            
            $table->timestamps();
            
            // Índices
            $table->index('estado');
            $table->index('almacen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios_planta');
    }
};

