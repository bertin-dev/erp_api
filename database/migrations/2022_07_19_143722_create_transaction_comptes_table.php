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
        Schema::create('transaction_comptes', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starting_date');
            $table->dateTime('end_date');
            $table->string('state');
            $table->unsignedBigInteger('account_id_sender')->index('transaction_comptes_account_id_sender_foreign');
            $table->unsignedBigInteger('account_id_receiver')->index('transaction_comptes_account_id_receiver_foreign');
            $table->string('transaction_type');
            $table->double('amount');
            $table->double('servicecharge')->nullable();
            $table->unsignedBigInteger('tarif_grid_id')->nullable()->index('transaction_comptes_tarif_grid_id_foreign');
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
        Schema::dropIfExists('transaction_comptes');
    }
};
