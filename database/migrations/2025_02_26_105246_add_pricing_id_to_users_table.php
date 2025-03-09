<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_id')->nullable()->after('id'); 
            $table->foreign('pricing_id')->references('id')->on('pricings')->onDelete('set null');
        });

        Schema::table('pricings', function (Blueprint $table) {
            $table->date('subscription_end_date')->nullable()->after('periodicity');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pricing_id']);
            $table->dropColumn('pricing_id');
        });
    }
};

