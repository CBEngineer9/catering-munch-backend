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
        Schema::create('history_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->enum('log_level', ["debug", "info", "notice", "warning", "error", "critical", "alert", "emergency"])->default('warning');
            $table->string('log_title');
            $table->string('log_desc');
            $table->unsignedBigInteger('users_id');
            $table->timestamp('log_timestamp')->useCurrent()->useCurrentOnUpdate();
            
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
        Schema::dropIfExists('history_log');
    }
};
