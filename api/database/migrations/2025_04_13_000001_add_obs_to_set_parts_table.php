<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObsToSetPartsTable extends Migration
{
    public function up()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->string('obs')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn('obs');
        });
    }
}
