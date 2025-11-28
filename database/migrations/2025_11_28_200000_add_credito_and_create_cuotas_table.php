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
        // Agregar campos para manejo de crédito a ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('tipo_pago', ['contado', 'credito'])->default('contado')->after('estado');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'completado'])->default('pendiente')->after('tipo_pago');
        });

        // Crear tabla de cuotas
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->integer('numero_cuota'); // 1 o 2
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['pendiente', 'pagado', 'expirado'])->default('pendiente');
            $table->string('payment_number')->nullable()->comment('Número de pago para esta cuota en PagoFacil');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            
            $table->index(['venta_id', 'numero_cuota']);
            $table->index('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
        
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['tipo_pago', 'estado_pago']);
        });
    }
};
