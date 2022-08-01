<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('recap_tabs', function (Blueprint $table) {
            //$table->timestamp('date')->default('current_timestamp()');
            $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->double('compte');
            $table->double('unite');
            $table->double('depot');
            $table->double('total');
            $table->double('recharge_orange')->default(0);
            $table->double('recharge_mtn')->default(0);
            $table->double('recharge_eu')->default(0);
            $table->double('recharges_total')->default(0);
            $table->double('solde_monetbil')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recap_tabs');
    }
};
