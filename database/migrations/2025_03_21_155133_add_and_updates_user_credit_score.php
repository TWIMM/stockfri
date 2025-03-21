<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 1. Migration pour mettre à jour la table Clients
return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('credit_score')->default(50); 
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('current_debt', 10, 2)->default(0);
            $table->timestamp('last_score_update')->nullable();
        });


        Schema::create('credit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->integer('score');
            $table->float('punctuality_score');
            $table->float('repayment_score');
            $table->float('history_score');
            $table->float('transaction_score');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasTable('paiements')) {
            Schema::create('paiements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
                $table->decimal('amount', 10, 2);
                $table->string('payment_method');
                $table->boolean('is_late')->default(false);
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['credit_score', 'credit_limit', 'current_debt', 'last_score_update']);
        });

        Schema::dropIfExists('paiements');
        Schema::dropIfExists('credit_history');

    }
};
