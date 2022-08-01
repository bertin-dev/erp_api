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
        Schema::create('users', function (Blueprint $table) {

            $table->bigInteger('id')->primary();
            $table->string('phone', 100);
            $table->string('address', 100)->nullable();
            $table->bigInteger('category_id')->index('users_ibfk_3');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 100)->nullable();
            $table->string('state')->default('actif');
            $table->bigInteger('parent_id')->nullable()->index('users_ibfk_5');
            $table->bigInteger('created_by')->nullable()->index('users_ibfk_4');
            $table->rememberToken();
            $table->bigInteger('compte_id')->index('users_ibfk_2');
            $table->unsignedBigInteger('role_id')->index('users_ibfk_1');
            $table->integer('bonus')->default(0);
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
        Schema::dropIfExists('users');
    }
};
