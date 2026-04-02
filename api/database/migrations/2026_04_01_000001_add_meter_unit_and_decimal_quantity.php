<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TYPE set_unit ADD VALUE IF NOT EXISTS 'meter'");
        DB::statement("ALTER TYPE set_part_unit ADD VALUE IF NOT EXISTS 'meter'");

        Schema::table('sets', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->nullable()->change();
        });

        Schema::table('set_parts', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->change();
        });

        Schema::table('set_parts', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->change();
        });

        // Note: PostgreSQL does not support removing values from ENUM types.
        // To fully revert, you would need to recreate the types without 'meter'.
    }
};
