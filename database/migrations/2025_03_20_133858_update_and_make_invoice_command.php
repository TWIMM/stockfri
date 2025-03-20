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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->string('invoice_link');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('already_paid', 10, 2)->nullable()->default(0.00);
            $table->decimal('rest_to_pay', 10, 2)->nullable()->default(0.00);
            $table->string('status')->default('generated'); // generated, sent, paid, voided
            $table->timestamps();
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->decimal('already_paid', 10, 2)->nullable()->default(0.00);
            $table->decimal('rest_to_pay', 10, 2)->nullable()->default(0.00);

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
