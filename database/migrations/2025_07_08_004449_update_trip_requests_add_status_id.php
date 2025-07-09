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
        Schema::table('trip_requests', function (Blueprint $table) {
            // Adicionar a coluna status_id
            $table->unsignedBigInteger('status_id')->nullable()->after('return_date');

            // Criar foreign key
            $table->foreign('status_id')->references('id')->on('trip_status');

            // Adicionar índice para performance
            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('trip_requests', function (Blueprint $table) {
        //     // Remover foreign key primeiro
        //     $table->dropForeign(['status_id']);

        //     // Remover a coluna
        //     $table->dropColumn('status_id');
        // });
    }
};
