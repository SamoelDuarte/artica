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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('jid', 255)->nullable();
            $table->integer('erro')->default(0);
            // Remove a declaração da chave estrangeira abaixo
            // $table->foreignId('session_id')->constrained('devices', 'id');
            $table->unsignedBigInteger('session_id')->nullable(); // Adiciona uma coluna de chave estrangeira não restrita
            $table->foreign('session_id')->references('id')->on('devices')->onDelete('set null'); // Define a ação de exclusão como definir nulo
            $table->string('service_id', 255)->nullable();
            $table->string('await_answer', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
