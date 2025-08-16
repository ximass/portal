<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS set_unit');
            DB::statement("CREATE TYPE set_unit AS ENUM ('piece', 'kg')");
        }

        Schema::table('sets', function (Blueprint $table) {
            $table->enum('unit', ['piece', 'kg'])->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
        
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS set_unit');
        }
    }
};
