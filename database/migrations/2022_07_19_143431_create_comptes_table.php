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
        Schema::create('comptes', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('account_number');
            $table->string('company', 100)->default('SMOPAYE');
            $table->string('account_state', 100)->default('activer');
            $table->double('amount', 8, 2)->default(0.00);
            $table->unsignedBigInteger('principal_account_id')->nullable();
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
        Schema::dropIfExists('comptes');
    }
};
