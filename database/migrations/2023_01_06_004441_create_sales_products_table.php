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
        Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('sale_id')
              ->nullable(false)
              ->comment('Sale ID.');
            $table->foreign('sale_id')
              ->references('id')->on('sales')
              ->onUpdate('CASCADE')
              ->onDelete('CASCADE');              
            
            $table->bigInteger('product_id')
              ->nullable(false)
              ->comment('Product ID.');
            $table->foreign('product_id')
              ->references('id')->on('products')
              ->onUpdate('CASCADE')
              ->onDelete('NO ACTION'); 

            $table->integer('quantity')
              ->nullable(false)
              ->comment('Quantity of the product included in the sale.');
        });

        DB::statement("COMMENT ON TABLE sales_products IS 'Store information about the products included on sales'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_products');
    }
};
