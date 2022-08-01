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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('category_id');
            $table->integer('discount');
            $table->dateTime('starting_date');
            $table->dateTime('end_date');
            $table->timestamps();

            $table->foreign('category_id', 'fk_campaigns_constraint')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
};
