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
        Schema::create('menu', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('menu_nama');
            $table->string('menu_foto')->default("default.png");
            $table->bigInteger('menu_harga');
            $table->enum('menu_status', ["tersedia", "tidak tersedia"])->default("tersedia");
            $table->unsignedBigInteger('users_id');
            $table->foreign('users_id')->references('users_id')->on('users');
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
        Schema::dropIfExists('menu');
    }
};
