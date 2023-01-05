<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name')
              ->unique()
              ->nullable(false)
              ->comment('Product name.');

            $table->string('serial', 10)
              ->unique()
              ->nullable(false)
              ->comment('Product serial number.');  

            $table->decimal('purchase_price', 19, 2)
              ->nullable(false)
              ->comment('Purchase price of the product.');

            $table->decimal('sale_price', 19, 2)
              ->nullable(false)
              ->comment('Sale price of the product.');      
              
            $table->enum('range', ['HIGH-RANGE', 'MID-RANGE', 'LOW-RANGE'])
              ->nullable(false)
              ->comment('Product range defined from the relationship between its sale price and utility.'); 
            
            $table->integer('stock')
              ->default(0)
              ->nullable(true)
              ->comment('Product current stock.');
        });

        DB::statement("COMMENT ON TABLE products IS 'Products information'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
