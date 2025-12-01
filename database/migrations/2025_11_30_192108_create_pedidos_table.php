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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_comprobante', 100)->nullable();
            $table->date('fecha')->nullable();
            $table->date('fecha_min')->nullable();
            $table->date('fecha_max')->nullable();
            $table->integer('estado')->default(1); // EMITIDO por defecto

            $table->unsignedBigInteger('almacen_id')->nullable();
            $table->unsignedBigInteger('operador_id')->nullable();
            $table->unsignedBigInteger('transportista_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('administrador_id')->nullable();

            $table->foreign('almacen_id')->references('id')->on('almacens')->onDelete('set null');
            $table->foreign('operador_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transportista_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('administrador_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('Proveedor_Id')->references('id')->on('proveedors')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
