<?php

namespace Database\Factories;

use App\Enums\ProductRanges;
use App\Services\ProductServices;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker_purchase_price = $this->faker->randomFloat(2, 10, 1000);
        $faker_sale_price = $this->faker->randomFloat(2, $faker_purchase_price, 5000);

        return [
            'name' => $this->faker->words(4, true),
            'serial' => $this->faker->unique()->numerify('##########'),
            'purchase_price' => $faker_purchase_price,
            'sale_price' => $faker_sale_price,
            'range' => ProductServices::get_product_range($faker_purchase_price, $faker_sale_price),
            'stock' => $this->faker->numberBetween(0, 1000)
        ];
    }

    /**
     * Set the product prices to be of an specific range.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function range(ProductRanges $range)
    {
        return $this->state(function (array $attributes) use ($range) {
            $purchasePrice = $attributes['purchase_price'];
            $salePriceForHigh = $purchasePrice * 2 + 0.1;           // Sale price for a high-range product
            $salePriceForLow = $purchasePrice / 0.9 - 0.1;          // Sale price for a low-range product

            switch ($range) {
              case ProductRanges::HIGH_RANGE: $salePrice = $this->faker->randomFloat(2, $salePriceForHigh, 5000); break;
              case ProductRanges::LOW_RANGE: $salePrice = $this->faker->randomFloat(2, $purchasePrice, $salePriceForLow); break;
              default: $salePrice = $this->faker->randomFloat(2, $salePriceForLow, $salePriceForHigh);
            }

            return [
                'sale_price' => $salePrice,
                'range' => $range
            ];
        });
    }    

}
