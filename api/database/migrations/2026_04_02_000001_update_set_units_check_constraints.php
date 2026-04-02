<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE sets DROP CONSTRAINT IF EXISTS sets_unit_check');
        DB::statement("ALTER TABLE sets ADD CONSTRAINT sets_unit_check CHECK (unit IN ('piece', 'kg', 'meter'))");

        DB::statement('ALTER TABLE set_parts DROP CONSTRAINT IF EXISTS set_parts_unit_check');
        DB::statement("ALTER TABLE set_parts ADD CONSTRAINT set_parts_unit_check CHECK (unit IN ('piece', 'kg', 'meter'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE sets DROP CONSTRAINT IF EXISTS sets_unit_check');
        DB::statement("ALTER TABLE sets ADD CONSTRAINT sets_unit_check CHECK (unit IN ('piece', 'kg'))");

        DB::statement('ALTER TABLE set_parts DROP CONSTRAINT IF EXISTS set_parts_unit_check');
        DB::statement("ALTER TABLE set_parts ADD CONSTRAINT set_parts_unit_check CHECK (unit IN ('piece', 'kg'))");
    }
};
