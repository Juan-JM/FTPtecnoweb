<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para PostgreSQL, necesitamos usar SQL directo para modificar el enum
        DB::statement("ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_tipo_movimiento_check");
        DB::statement("ALTER TABLE inventario ADD CONSTRAINT inventario_tipo_movimiento_check CHECK (tipo_movimiento IN ('compra', 'venta', 'entrada', 'salida', 'ajuste'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al enum original
        DB::statement("ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_tipo_movimiento_check");
        DB::statement("ALTER TABLE inventario ADD CONSTRAINT inventario_tipo_movimiento_check CHECK (tipo_movimiento IN ('compra', 'venta'))");
    }
};
