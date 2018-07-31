<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitationCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitation_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('apply_user_id');
            $table->string('code');
            $table->integer('used_user_id')->nullable();
            $table->boolean('has_used')->default(false);
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
        Schema::dropIfExists('invitation_codes');
    }
}
