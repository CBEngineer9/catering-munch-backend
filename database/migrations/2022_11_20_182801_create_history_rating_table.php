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
        Schema::create('history_rating', function (Blueprint $table) {
            $table->id('rating_id');
            $table->integer('rating_score');
            $table->unsignedBigInteger('users_provider');
            $table->foreign('users_provider')->references('users_id')->on('users');
            $table->unsignedBigInteger('users_customer');
            $table->foreign('users_customer')->references('users_id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_rating');
    }
};
