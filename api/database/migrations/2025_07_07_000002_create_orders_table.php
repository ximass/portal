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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('final_value', 10, 2)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('type', ['pre_order', 'order'])->default('pre_order');
            $table->enum('delivery_type', ['CIF', 'FOB'])->nullable();
            $table->decimal('markup', 10, 3)->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('estimated_delivery_date', 250)->nullable();
            $table->text('payment_obs')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
