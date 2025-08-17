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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome do cliente
            $table->string('logradouro'); // Endereço
            $table->string('numero'); // Número da residência
            $table->string('complemento')->nullable(); // Complemento (opcional)
            $table->string('bairro'); // Bairro
            $table->string('cidade'); // Cidade
            $table->string('cep', 9); // CEP (formato 00000-000)
            $table->string('telefone')->nullable(); // Campo para telefone
            $table->string('link')->nullable(); // Campo para link, opcional
            $table->integer('quantidade_numeros')->default(1); // Campo para número da sorte

            // Campos adicionados conforme sua lista
            $table->string('referencia')->nullable(); // Ponto de referência
            $table->text('observacao')->nullable(); // Observações adicionais
            $table->date('data_nascimento')->nullable(); // Data de nascimento

            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
