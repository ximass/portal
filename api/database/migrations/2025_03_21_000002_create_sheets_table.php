<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sheets', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id')->primary();
            $table->decimal('thickness', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('length', 10, 2);
            $table->decimal('specific_weight', 10, 6);
            $table->decimal('price_kg', 10, 4);
            $table->timestamps();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sheets');
    }
};