<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagemEmMassaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imagem_em_massa', function (Blueprint $table) {
            $table->id();
            $table->string('caminho'); // Coluna para armazenar o caminho da imagem
            // Adicione outras colunas conforme necessário
            $table->timestamps(); // Adiciona colunas created_at e updated_at automaticamente
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imagem_em_massa');
    }
}
