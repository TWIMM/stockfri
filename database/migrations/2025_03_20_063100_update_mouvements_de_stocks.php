<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('mouvement_de_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('magasin_id')->nullable();
     

            $table->foreign('magasin_id')->references('id')->on('magasins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
