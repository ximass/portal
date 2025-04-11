<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartProcessTable extends Migration
{
    public function up()
    {
        Schema::create('part_process', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_part_id')->constrained('set_parts')->onDelete('cascade');
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->integer('time');
            $table->decimal('final_value', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_process');
    }
}