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
        Schema::create('detail_pemesanan', function (Blueprint $table) {
            $table->id('detail_id');
            $table->unsignedBigInteger('pemesanan_id');
            $table->foreign('pemesanan_id')->references('pemesanan_id')->on('history_pemesanan');
            $table->unsignedBigInteger('menu_id');
            $table->foreign('menu_id')->references('menu_id')->on('menu');
            $table->integer('detail_jumlah');
            $table->bigInteger('detail_total');
            $table->dateTime('detail_tanggal');
            $table->enum('detail_status', ["belum dikirim", "terkirim", "diterima"])->default("belum dikirim");
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
        Schema::dropIfExists('detail_pemesanan');
    }
};
