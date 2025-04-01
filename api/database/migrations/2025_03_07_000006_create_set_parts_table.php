<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetPartsTable extends Migration
{
    public function up()
    {
        DB::statement('DROP TYPE IF EXISTS set_part_type');
        DB::statement("CREATE TYPE set_part_type AS ENUM ('material', 'sheet', 'bar', 'component')");

        Schema::create('set_parts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('set_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('material_id')->nullable();
            $table->unsignedBigInteger('sheet_id')->nullable();
            $table->unsignedBigInteger('bar_id')->nullable();
            $table->unsignedBigInteger('component_id')->nullable();
            $table->enum('type', ['material', 'sheet', 'bar', 'component'])->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('loss', 5, 2)->nullable(); // percentage loss
            $table->decimal('unit_net_weight', 10, 2)->nullable();
            $table->decimal('net_weight', 10, 2)->nullable();
            $table->decimal('unit_gross_weight', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->decimal('final_value', 10, 2)->nullable();

            // component
            $table->decimal('markup', 10, 3)->nullable();

            // bar and sheet
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('sheet_id')->references('id')->on('sheets')->onDelete('set null');
            $table->foreign('bar_id')->references('id')->on('bars')->onDelete('set null');
            $table->foreign('component_id')->references('id')->on('components')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('set_parts');

        DB::statement('DROP TYPE IF EXISTS set_part_type');
    }
}