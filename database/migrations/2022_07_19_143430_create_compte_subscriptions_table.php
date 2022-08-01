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
        Schema::create('compte_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->double('subscriptionCharge', 8, 2);
            $table->bigInteger('subscription_id')->index('subscription_id');
            $table->string('subscription_type', 100);
            $table->bigInteger('compte_id')->index('compte_id');
            $table->string('starting_date');
            $table->string('end_date');
            $table->string('validate')->default('active');
            $table->double('transaction_number');
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
        Schema::dropIfExists('compte_subscriptions');
    }
};
