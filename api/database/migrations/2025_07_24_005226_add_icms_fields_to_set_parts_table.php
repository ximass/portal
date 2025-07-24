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
            $table->decimal('unit_icms_value', 10, 2)->nullable()->after('total_ipi_value');
            $table->decimal('total_icms_value', 10, 2)->nullable()->after('unit_icms_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn(['unit_icms_value', 'total_icms_value']);
        });
    }
};
