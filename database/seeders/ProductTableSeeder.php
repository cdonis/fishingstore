<?php

namespace Database\Seeders;

use App\Enums\ProductRanges;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::factory(5)->range(ProductRanges::HIGH_RANGE)->create();
        Product::factory(5)->range(ProductRanges::MID_RANGE)->create();
        Product::factory(5)->range(ProductRanges::LOW_RANGE)->create();
    }
}
