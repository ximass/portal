<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('unit_value');
        });
    }

    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn(['supplier']);
        });
    }
};