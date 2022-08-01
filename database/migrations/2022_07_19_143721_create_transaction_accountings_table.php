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
        Schema::create('transaction_accountings', function (Blueprint $table) {
            $table->integer('RMO');
            $table->integer('ROM');
            $table->integer('TCCA');
            $table->integer('TCCO');
            $table->integer('TUD');
            $table->integer('TDU');
            $table->integer('RCOMO');
            $table->integer('RCOOM');
            $table->integer('RCAMO');
            $table->integer('RCAOM');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_accountings');
    }
};
