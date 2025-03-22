<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bars', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id')->primary();
            $table->decimal('diameter', 5, 2);
            $table->decimal('length', 5, 2);
            $table->decimal('specific_weight', 10, 4);
            $table->decimal('price_per_gram', 10, 4);
            $table->timestamps();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bars');
    }
};