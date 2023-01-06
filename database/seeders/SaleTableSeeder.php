<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all products
        $products = Product::all();

        // // Create 5 sales with its products in the pivot table (sales_products)
        Sale::factory(5)
          ->create()
          ->each(function ($sale) use ($products) { 
                $productAmount = rand(1, 5);
                for($i = 1; $i <= $productAmount; $i++) {
                    $sale->products()->attach($products->random()->id, ['quantity' => rand(1, 100)]);
                }
            });
    }
}
