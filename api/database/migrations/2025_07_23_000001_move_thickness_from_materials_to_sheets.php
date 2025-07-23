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
        // Adicionar thickness na tabela sheets
        Schema::table('sheets', function (Blueprint $table) {
            $table->decimal('thickness', 10, 2)->after('material_id');
        });

        // Remover thickness da tabela materials
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('thickness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Adicionar thickness de volta na tabela materials
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('thickness', 10, 2)->after('name');
        });

        // Remover thickness da tabela sheets
        Schema::table('sheets', function (Blueprint $table) {
            $table->dropColumn('thickness');
        });
    }
};
