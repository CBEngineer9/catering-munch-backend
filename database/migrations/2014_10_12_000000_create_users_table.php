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
        Schema::create('users', function (Blueprint $table) {
            $table->id('users_id');
            $table->string('users_email')->unique();
            $table->string('users_telepon')->unique();
            $table->string('users_nama');
            $table->string('users_alamat');
            $table->string('users_password');
            $table->string('users_desc')->nullable();
            $table->bigInteger('users_saldo')->default(0);
            $table->enum('users_role', ["admin", "provider", "customer"])->default("customer");
            $table->enum('users_status', ["banned", "aktif", "menunggu"])->default("menunggu");
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
        Schema::dropIfExists('users');
    }
};
