<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMappingsTable extends Migration
{
    public function up()
    {
        Schema::create('mappings', function (Blueprint $table) {
            $table->id();
            $table->string('mapping_type'); // 'department','category','store', etc.
            $table->unsignedBigInteger('local_id'); // our local id (e.g. departments.id)
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('supplier_external_id')->nullable();
            $table->string('supplier_external_name')->nullable();
            $table->timestamps();

            $table->index(['mapping_type', 'supplier_id', 'supplier_external_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mappings');
    }
}
