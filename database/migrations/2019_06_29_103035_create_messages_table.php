<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender_id')->unsigned();
            $table->bigInteger('reciever_id')->unsigned();
            $table->text("message")->nullable();
            $table->integer("status")->unsigned()->default(0);
            $table->bigInteger('chatRoom_id')->unsigned();
            $table->foreign('chatRoom_id')->references('id')->on('chat_rooms')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reciever_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('messages');
    }
}
