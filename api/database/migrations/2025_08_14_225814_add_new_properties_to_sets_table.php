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
        Schema::table('sets', function (Blueprint $table) {
            $table->integer('quantity')->nullable();
            $table->unsignedBigInteger('ncm_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('obs')->nullable();

            $table->foreign('ncm_id')->references('id')->on('mercosur_common_nomenclatures')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            $table->dropForeign(['ncm_id']);
            $table->dropColumn(['quantity', 'ncm_id', 'reference', 'obs']);
        });
    }
};
