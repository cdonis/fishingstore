<?php

namespace App\Services;

use App\Enums\ProductRanges;
use App\Repository\ProductRepositoryInterface;
use Illuminate\Support\Facades\Validator;

class ProductServices
{
    /**
     * @var productRepository
     */
    protected $productRepository;

    /**
     * Constructor
     * 
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
      $this->productRepository = $productRepository;
    }

    /**
     * Get the range of a product calculated from the relationship between its sale price and its utility 
     * 
     * @param purchasePrice Purchase price of the product
     * @param salePrice     Sale price of the product
     * 
     * @return ProductRanges
     */
    private function get_product_range(float $purchasePrice, float $salePrice): ProductRanges {
        
      $range = ProductRanges::MID_RANGE;
      
      $product_utility = $salePrice - $purchasePrice;
      
      if ($product_utility > $salePrice * 0.5) {
        $range = ProductRanges::HIGH_RANGE;
      } else if ($product_utility < $salePrice * 0.1) {
        $range = ProductRanges::LOW_RANGE;
      }

      return $range;

    }  

    /**
     * Return a list of products with stock
     */
    public function getExistingProducts(): array
    {
      $products = $this->productRepository->getByFilters([['stock', '>', 0]]);
      return [
        'data' => $products,
        'total' => count($products)
      ];
    }

    /**
     * Creates a new product in database
     */
    public function create($data) 
    {
      $data['range'] = $this->get_product_range($data['purchase_price'], $data['sale_price']);
      
      return $this->productRepository->create($data);
    }

    /**
     * Update a product in database
     */
    public function update($data) 
    {
      $data['range'] = $this->get_product_range($data['purchase_price'], $data['sale_price']);
      
      return $this->productRepository->update($data);
    }

    /**
     * Massive stock update
     * 
     * @param array $data 
     * @return int Number of records updated
     */
    public function updateStock(array $data): int 
    {
        return $this->productRepository->updateStock($data);
    }    
}