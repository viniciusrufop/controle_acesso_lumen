<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adjustment_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('data_user_id')->unsigned()->nullable(false);
            $table->foreign('data_user_id')
                    ->references('id')
                    ->on('data_users')
                    ->onDelete('cascade');
            $table->date('data')->nullable(false);
            $table->time('hora')->nullable(false);
            $table->text('justificativa')->nullable(false);
            $table->boolean('atendido')->nullable(false);
            $table->boolean('aceito')->nullable(false);
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
        Schema::dropIfExists('adjustment_requests');
    }
}
