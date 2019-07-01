<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("first")->unsigned();
            $table->bigInteger("second")->unsigned();
            $table->integer("first_status")->unsigned()->default(1);
            $table->integer("second_status")->unsigned()->default(1);
            $table->integer("status")->unsigned()->default(1);
            $table->foreign('first')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('second')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('chat_rooms');
    }
}
