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
        // Create custom enum type for PostgreSQL
        DB::statement('DROP TYPE IF EXISTS set_part_type');
        DB::statement("CREATE TYPE set_part_type AS ENUM ('material', 'sheet', 'bar', 'component', 'process')");

        Schema::create('set_parts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('secondary_content')->nullable();
            $table->string('obs')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('set_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('material_id')->nullable();
            $table->unsignedBigInteger('sheet_id')->nullable();
            $table->unsignedBigInteger('bar_id')->nullable();
            $table->unsignedBigInteger('component_id')->nullable();
            $table->foreignId('ncm_id')->nullable()->constrained('mercosur_common_nomenclatures')->onDelete('set null');
            $table->enum('type', ['material', 'sheet', 'bar', 'component', 'process'])->nullable();
            $table->decimal('thickness', 10, 2)->nullable();
            $table->json('locked_values')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('loss', 5, 2)->nullable(); // percentage loss
            $table->decimal('unit_net_weight', 10, 2)->nullable();
            $table->decimal('net_weight', 10, 2)->nullable();
            $table->decimal('unit_gross_weight', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->decimal('final_value', 10, 2)->nullable();
            $table->decimal('unit_ipi_value', 15, 2)->nullable();
            $table->decimal('total_ipi_value', 15, 2)->nullable();
            $table->decimal('unit_icms_value', 10, 2)->nullable();
            $table->decimal('total_icms_value', 10, 2)->nullable();

            // component
            $table->decimal('markup', 10, 3)->nullable();

            // bar and sheet
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('sheet_id')->references('id')->on('sheets')->onDelete('set null');
            $table->foreign('bar_id')->references('id')->on('bars')->onDelete('set null');
            $table->foreign('component_id')->references('id')->on('components')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_parts');
        DB::statement('DROP TYPE IF EXISTS set_part_type');
    }
};
