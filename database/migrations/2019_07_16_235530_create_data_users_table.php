<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('nome',255)->nullable(false);
            $table->string('sobrenome',255)->nullable(true);
            $table->string('telefone',20)->nullable(true);
            $table->integer('cep')->nullable(true);
            $table->string('logradouro',255)->nullable(true);
            $table->string('bairro',255)->nullable(true);
            $table->string('complemento',255)->nullable(true);
            $table->string('cidade',255)->nullable(true);
            $table->string('estado',255)->nullable(true);
            $table->integer('login')->unique()->nullable(false);
            $table->integer('senha')->nullable(false);
            $table->boolean('ativo')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_users');
    }
}
