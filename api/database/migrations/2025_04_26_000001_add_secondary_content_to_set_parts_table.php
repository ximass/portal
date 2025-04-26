<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondaryContentToSetPartsTable extends Migration
{
    public function up()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->text('secondary_content')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropColumn('secondary_content');
        });
    }
}
