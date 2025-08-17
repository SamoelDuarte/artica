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
        Schema::table('devices', function (Blueprint $table) {
            $table->integer('start_minutes')->nullable()->comment('Minutos do início do intervalo');
            $table->integer('start_seconds')->nullable()->comment('Segundos do início do intervalo');
            $table->integer('end_minutes')->nullable()->comment('Minutos do fim do intervalo');
            $table->integer('end_seconds')->nullable()->comment('Segundos do fim do intervalo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('start_minutes');
            $table->dropColumn('start_seconds');
            $table->dropColumn('end_minutes');
            $table->dropColumn('end_seconds');
        });
    }
};
