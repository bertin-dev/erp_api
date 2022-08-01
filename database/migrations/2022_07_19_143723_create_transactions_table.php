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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id');
            $table->dateTime('starting_date');
            $table->dateTime('end_date');
            $table->string('state');
            $table->bigInteger('card_id_sender')->nullable()->index('transactions_ibfk_1');
            $table->bigInteger('card_id_receiver')->nullable()->index('transactions_ibfk_2');
            $table->bigInteger('account_id_sender')->nullable()->index('transactions_ibfk_4');
            $table->bigInteger('account_id_receiver')->nullable()->index('transactions_ibfk_5');
            $table->string('transaction_type');
            $table->double('amount');
            $table->double('servicecharge')->nullable();
            $table->bigInteger('tarif_grid_id')->nullable()->index('transactions_ibfk_3');
            $table->unsignedBigInteger('device_id')->nullable()->index('device_id');
            $table->string('transaction_number');
            $table->timestamps();
            $table->string('operator')->nullable();
            $table->boolean('pay')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
