<?php

namespace App\Services;

use App\Enums\ProductRanges;

class ProductServices
{
    /**
     * Get the range of a product calculated from the relationship between its sale price and its utility 
     * 
     * @param purchasePrice Purchase price of the product
     * @param salePrice     Sale price of the product
     * 
     * @return ProductRanges
     */
    static public function get_product_range(float $purchasePrice, float $salePrice): ProductRanges {
        
      $range = ProductRanges::MID_RANGE;
      
      $product_utility = $salePrice - $purchasePrice;
      
      if ($product_utility > $salePrice * 0.5) {
        $range = ProductRanges::HIGH_RANGE;
      } else if ($product_utility < $salePrice * 0.1) {
        $range = ProductRanges::LOW_RANGE;
      }

      return $range;

    }  
}