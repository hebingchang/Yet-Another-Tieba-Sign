<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sign_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('forum_id');
            $table->dateTime('sign_time')->nullable();
            $table->integer('user_sign_rank')->nullable();
            $table->integer('cont_sign_num')->nullable();
            $table->integer('total_sign_num')->nullable();
            $table->integer('sign_bonus_point')->nullable();
            $table->string('level_name')->nullable();
            $table->integer('levelup_score')->nullable();
            $table->boolean('has_signed');
            $table->string('error_msg')->nullable();

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
        Schema::dropIfExists('sign_records');
    }
}
