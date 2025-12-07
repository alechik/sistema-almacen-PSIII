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
        Schema::create('detalle_salidas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('salida_id');
            $table->unsignedBigInteger('producto_id');

            $table->decimal('cant_salida', 18, 2);
            $table->decimal('precio', 18, 2);

            // Foreign Keys
            $table->foreign('salida_id')->references('id')->on('salidas')
                ->onDelete('cascade'); // Se borran detalles si se borra la salida

            $table->foreign('producto_id')->references('id')->on('productos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_salidas');
    }
};
