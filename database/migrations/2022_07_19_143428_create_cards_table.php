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
        Schema::create('cards', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('code_number', 100);
            $table->string('serial_number', 100);
            $table->bigInteger('user_id')->nullable()->index('user_id');
            $table->string('type', 100)->default('A M1');
            $table->string('company', 100)->default('SMOPAYE');
            $table->double('unity')->default(0);
            $table->double('deposit')->default(0);
            $table->timestamp('starting_date')->useCurrent();
            $table->date('end_date');
            $table->string('card_state', 100)->default('activer');
            $table->bigInteger('user_created')->nullable()->index('user_created');
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
        Schema::dropIfExists('cards');
    }
};
