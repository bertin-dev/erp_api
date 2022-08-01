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
        Schema::create('user_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('firstname')->nullable();
            $table->string('gender', 100);
            $table->string('phone', 100)->unique('user_registrations_phone_unique');
            $table->string('cni', 100);
            $table->string('address', 100)->nullable();
            $table->string('category_id');
            $table->string('password', 100)->nullable();
            $table->string('state', 100)->default('inactif');
            $table->string('parent_id', 11)->nullable();
            $table->string('created_by')->default('1');
            $table->string('nom_img_recto');
            $table->string('nom_img_verso')->nullable();
            $table->rememberToken();
            $table->unsignedBigInteger('role_id')->index('user_registration_role_id_foreign');
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
        Schema::dropIfExists('user_registrations');
    }
};
