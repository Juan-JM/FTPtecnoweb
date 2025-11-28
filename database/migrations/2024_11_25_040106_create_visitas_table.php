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
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('pagina')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('url');
            $table->index('pagina');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};