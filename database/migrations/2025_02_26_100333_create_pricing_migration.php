<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->json('offers'); // Store multiple offers as JSON
            $table->decimal('price', 10, 2);
            $table->enum('periodicity', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricings');
    }
};

