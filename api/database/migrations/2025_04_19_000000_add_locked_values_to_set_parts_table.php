<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->json('locked_values')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn('locked_values');
        });
    }
};
