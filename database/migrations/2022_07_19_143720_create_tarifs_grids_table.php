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
        Schema::create('tarif_grids', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->double('tranche_min');
            $table->double('tranche_max');
            $table->double('tarif_night');
            $table->string('type_tarif', 25);
            $table->double('tarif_day');
            $table->bigInteger('categorie_id')->index('fk_tarif_grid');
            $table->unsignedBigInteger('role_id')->nullable()->index('role_id');
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
        Schema::dropIfExists('tarif_grids');
    }
};
