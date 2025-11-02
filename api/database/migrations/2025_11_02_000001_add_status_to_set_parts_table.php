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
        Schema::table('set_parts', function (Blueprint $table) {
            $table->enum('status', ['in_production', 'finished'])
                  ->default('in_production')
                  ->after('locked_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};