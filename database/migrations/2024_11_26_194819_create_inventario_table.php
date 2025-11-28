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
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad_disponible')->default(0);
            $table->date('fecha_ultima_actualizacion')->default(now());
            $table->enum('tipo_movimiento', ['compra', 'venta']);
            $table->integer('cantidad_movimiento');
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
