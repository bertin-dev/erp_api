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
        Schema::create('categories', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name', 100);
            $table->unsignedBigInteger('role_id');
            $table->bigInteger('type_id')->nullable();
            $table->integer('bonus_point')->nullable();
            $table->integer('remise')->default(0);
            $table->timestamps();

            $table->foreign('role_id', 'FK_categorie_roles')->references('id')->on('roles');
            $table->foreign('type_id', 'FK_type')->references('id')->on('types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
