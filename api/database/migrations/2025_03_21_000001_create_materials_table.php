<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("CREATE TYPE material_type AS ENUM ('sheet', 'bar', 'component')");

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['sheet', 'bar', 'component']);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('materials');
        
        DB::statement('DROP TYPE IF EXISTS material_type');
    }
};