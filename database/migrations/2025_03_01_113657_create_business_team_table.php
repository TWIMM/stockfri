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
        Schema::create('business_team', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable(); // Make sure this is unsigned
            $table->unsignedBigInteger('team_id'); // Make sure this is unsigned
    
            // Foreign key to business table (note: using 'businesses' table)
            $table->foreign('business_id')->references('id')->on('business')->onDelete('set null');
    
            // Foreign key to teams table
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
    
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_team');
    }
};
