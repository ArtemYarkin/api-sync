<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
                Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->dateTime('last_change_date')->nullable(); 
            $table->string('supplier_article')->nullable(); 
            $table->string('tech_size')->nullable(); 
            $table->bigInteger('barcode'); 
            $table->integer('quantity')->default(0);
            $table->boolean('is_supply')->nullable(); 
            $table->boolean('is_realization')->nullable(); 
            $table->integer('quantity_full')->nullable(); 
            $table->string('warehouse_name');
            $table->integer('in_way_to_client')->nullable();
            $table->integer('in_way_from_client')->nullable();
            $table->integer('nm_id');
            $table->string('subject')->nullable(); 
            $table->string('category')->nullable(); 
            $table->string('brand')->nullable();
            $table->bigInteger('sc_code')->nullable(); 
            $table->decimal('price', 15, 2)->nullable(); 
            $table->decimal('discount', 5, 2)->nullable(); 
            $table->date('sync_date')->nullable();
            $table->json('data')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
