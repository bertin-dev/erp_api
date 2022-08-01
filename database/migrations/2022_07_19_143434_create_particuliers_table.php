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
        Schema::create('particuliers', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('firstname')->nullable();
            $table->string('cni', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('fonction')->default('ezpass');
            $table->string('gender', 100);
            $table->bigInteger('user_id')->index('user_id');
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
        Schema::dropIfExists('particuliers');
    }
};
