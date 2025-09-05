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
            $table->unsignedBigInteger('local_id'); // troca pontos local id (e.g. categories.id) 1
            $table->foreignId('supplier_id')->constrained('suppliers'); // id categoria da magalu 3
            $table->string('supplier_external_id')->nullable(); // id do fornecedor externo 1
            $table->string('supplier_external_name')->nullable(); // magalu
            $table->timestamps();

            $table->index(['mapping_type', 'supplier_id', 'supplier_external_id']); // facilitates the search
        });
    }

    public function down()
    {
        Schema::dropIfExists('mappings');
    }
}
