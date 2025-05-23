<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->text('content')->nullable(false)->change();
        });
    }
};
