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
            $table->decimal('unit_ipi_value', 15, 2)->default(0)->after('unit_value');
            $table->decimal('total_ipi_value', 15, 2)->default(0)->after('final_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn(['unit_ipi_value', 'total_ipi_value']);
        });
    }
};
