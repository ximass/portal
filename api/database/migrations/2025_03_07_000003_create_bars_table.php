<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->decimal('weight', 10, 2);
            $table->decimal('length', 10, 2);
            $table->decimal('price_kg', 10, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bars');
    }
};