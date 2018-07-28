<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_forums', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bduss_id');
            $table->integer('forum_id');
            $table->string('forum_name');
            $table->integer('level_id');
            $table->string('level_name');
            $table->integer('cur_score');
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
        Schema::dropIfExists('user_forums');
    }
}
