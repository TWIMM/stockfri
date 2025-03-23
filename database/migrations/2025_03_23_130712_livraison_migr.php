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
        Schema::dropIfExists('livraisons');
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->date('delivery_date')->nullable();
            $table->enum('delivery_status', ['none','pending', 'in_progress', 'delivered', 'cancelled'])->default('none');
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('team_members')->nullOnDelete();
            $table->string('received_by')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
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
