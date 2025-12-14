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
        Schema::create('pedido_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->integer('envio_id')->comment('ID del envío en plantaCruds');
            $table->string('envio_codigo')->comment('Código del envío');
            $table->dateTime('fecha_entrega');
            $table->string('transportista_nombre')->nullable();
            $table->json('documentos')->nullable()->comment('Rutas de los documentos guardados');
            $table->timestamps();
            
            $table->index(['pedido_id', 'envio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_entregas');
    }
};
