<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MeetingsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('meetings', function (Blueprint $table) {
          $table->increments('id');
          $table->string('roomName')->default('');
          $table->string('status');
          $table->string('summary');
          $table->string('location')->default('');
          $table->text('attendees');
          $table->string('meetingId');
          $table->string('date')->default('');
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
        Schema::dropIfExists('meetings');
    }
}
