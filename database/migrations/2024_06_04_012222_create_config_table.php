<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->string('motoboy_fone')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('chatbot')->default(false);
            $table->timestamps();
        });

        Config::create([
            'motoboy_fone' => '5511986123660',
            'status' => true,
            'chabot' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config');
    }
}
