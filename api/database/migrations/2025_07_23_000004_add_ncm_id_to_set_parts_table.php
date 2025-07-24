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
            $table->foreignId('ncm_id')->nullable()->constrained('mercosur_common_nomenclatures')->onDelete('set null')->after('component_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropForeign(['ncm_id']);
            $table->dropColumn('ncm_id');
        });
    }
};
