<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_topup', function (Blueprint $table) {
            $table->id('topup_id');
            $table->bigInteger('topup_nominal');
            $table->dateTime('topup_tanggal');
            $table->tinyInteger('topup_response');
            $table->unsignedBigInteger('users_id');
            $table->foreign('users_id')->references('users_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_topup');
    }
};
