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
        
        Schema::table('commandes', function (Blueprint $table) {

            if (Schema::hasColumn('commandes', 'stock_id')) {
                $table->dropForeign(['stock_id']); // Make sure the constraint name is correct
                $table->dropColumn('stock_id');
            }
            
            if (Schema::hasColumn('commandes', 'service_id')) {
                $table->dropForeign(['service_id']); // Make sure the constraint name is correct
                $table->dropColumn('service_id');
            }
            
            if (Schema::hasColumn('commandes', 'quantity')) {
                $table->dropColumn('quantity');
            }
            
            $table->string('payment_mode')->nullable();
            $table->string('invoice_status')->nullable();
            $table->decimal('tva', 8, 2)->default(18);
            $table->string('mobile_number')->nullable();
            $table->string('mobile_reference')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_reference')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_reference')->nullable();
            $table->string('cash_reference')->nullable();
        });

        Schema::create('commande_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
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
