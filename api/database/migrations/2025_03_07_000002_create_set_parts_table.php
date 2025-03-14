<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetPartsTable extends Migration
{
    public function up()
    {
        Schema::create('set_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('set_parts');
    }
}