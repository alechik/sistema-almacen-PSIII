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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_comprobante')->nullable();
            $table->date('fecha')->nullable();
            $table->date('fecha_min', 18, 2)->nullable();
            $table->date('fecha_max')->nullable();
            $table->integer('estado')->default(1);

            // FKs
            $table->unsignedBigInteger('almacen_id');
            $table->unsignedBigInteger('operador_id');
            $table->unsignedBigInteger('transportista_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->unsignedBigInteger('tipo_ingreso_id');
            $table->unsignedBigInteger('vehiculo_id')->nullable();
            $table->unsignedBigInteger('administrador_id');

            // Relaciones
            $table->foreign('almacen_id')->references('id')->on('almacens');
            $table->foreign('operador_id')->references('id')->on('users');
            $table->foreign('transportista_id')->references('id')->on('users');
            // $table->foreign('proveedor_id')->references('id')->on('proveedors');
            $table->foreign('pedido_id')->references('id')->on('pedidos');
            $table->foreign('tipo_ingreso_id')->references('id')->on('tipo_ingresos');
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos');
            $table->foreign('administrador_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
