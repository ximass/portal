<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_type', ['CIF', 'FOB'])->nullable();
            $table->decimal('markup', 10, 3)->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->text('payment_obs')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'markup', 'delivery_date', 'payment_obs']);
        });
    }
};