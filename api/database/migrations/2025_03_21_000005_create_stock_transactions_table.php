<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->timestamp('transaction_date')->useCurrent();
            $table->char('operation', 1);
            $table->decimal('quantity', 10, 3);
            $table->text('obs')->nullable();
            $table->timestamps();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
        });

        // Adding check constraint to ensure only 'E' (entry) or 'S' (exit) are allowed
        DB::statement("ALTER TABLE stock_transactions ADD CONSTRAINT chk_operation CHECK (operation IN ('E','S'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};