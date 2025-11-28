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
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('payment_number')->nullable()->after('estado')->comment('Número de pedido enviado a PagoFacil');
            $table->string('transaction_id')->nullable()->after('payment_number')->comment('ID de transacción de PagoFacil');
            $table->string('metodo_pago')->nullable()->after('transaction_id');
            $table->timestamp('fecha_pago')->nullable()->after('metodo_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['payment_number', 'transaction_id', 'metodo_pago', 'fecha_pago']);
        });
    }
};
