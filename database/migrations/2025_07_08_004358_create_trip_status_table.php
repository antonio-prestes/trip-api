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
        Schema::create('trip_status', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // solicitado, aprovado, cancelado
            $table->string('description'); // Descrição mais detalhada do status
            $table->string('color', 7)->default('#6B7280'); // Cor hexadecimal para UI
            $table->boolean('is_active')->default(true); // Para desativar status se necessário
            $table->integer('order')->default(0); // Ordem de exibição
            $table->timestamps();
            
            // Índices para performance
            $table->index('name');
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_status');
    }
};
