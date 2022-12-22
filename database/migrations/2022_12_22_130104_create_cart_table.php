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
        Schema::create('cart', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('users_customer');
            $table->foreign('users_customer')->references('users_id')->on('users');
            $table->unsignedBigInteger('menu_id');
            $table->foreign('menu_id')->references('menu_id')->on('menu');
            $table->integer('cart_jumlah');
            $table->bigInteger('cart_total');
            $table->dateTime('cart_tanggal');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
};
