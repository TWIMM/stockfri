<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ifu')->nullable();

            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address');
            $table->unsignedBigInteger('user_id');  
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fournisseurs');
    }

};
