<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('team_member_team', function (Blueprint $table) {
            $table->id();
            $table->boolean('mode_admin');

            $table->unsignedBigInteger('team_member_id'); 
            $table->unsignedBigInteger('team_id')->nullable();
            $table->json("permissions")->nullable();
            //$table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');

    
            $table->foreign('team_member_id')->references('id')->on('team_members')->onDelete('cascade');
    
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_member_team');
    }
};
