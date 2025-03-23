<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSetPartsTable extends Migration
{
    public function up()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->constrained('materials')->onDelete('set null');
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
        });
    }

    public function down()
    {
        Schema::table('set_parts', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn([
                'material_id',
                'quantity',
                'loss',
                'unit_net_weight',
                'net_weight',
                'unit_gross_weight',
                'gross_weight',
                'unit_value',
                'final_value',
                
                // component
                'markup',

                // bar and sheet
                'width',
                'length'
            ]);
        });
    }
}