<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Productos incluidos en cada envío de planta
     */
    public function up(): void
    {
        Schema::create('envio_planta_productos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('envio_planta_id');
            $table->foreign('envio_planta_id')->references('id')->on('envios_planta')->onDelete('cascade');
            
            $table->string('producto_nombre');
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(0);
            $table->decimal('peso_unitario', 12, 3)->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('total_peso', 12, 3)->default(0);
            $table->decimal('total_precio', 12, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_planta_productos');
    }
};

