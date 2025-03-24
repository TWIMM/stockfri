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
        Schema::create('client_debts', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('client_id'); 
            $table->unsignedBigInteger('commande_id');
            $table->decimal('amount', 10, 2); 
            //$table->string('payment_method'); 
            //$table->boolean('is_late')->default(false);
            $table->date('due_date'); 
            $table->timestamps(); 

            // Foreign key constraints
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
        });


        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('client_debt_id')->nullable(); // Add the client_debt_id column
            $table->foreign('client_debt_id')->references('id')->on('client_debts')->onDelete('cascade'); // Foreign key to client_debts table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_debts');
    }
};
