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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('stock', 12, 2)->default(0);
            $table->date('fech_vencimiento')->nullable();
            $table->integer('estado')->default(1);
            $table->decimal('stock_minimo', 12, 2)->nullable();
            $table->string('cod_producto', 100)->unique()->nullable();
            $table->decimal('precio', 12, 2)->nullable();

            // Relaciones
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();

            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            // $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
