<?php

namespace App\Models;

use App\Enums\ProductRanges;
use App\Traits\Filtering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, Filtering;

    protected $casts = [
      'purchase_price' => 'float',
      'sale_price' => 'float',
      'range' => ProductRanges::class
    ];

    protected $fillable = ['name', 'serial', 'purchase_price', 'sale_price', 'range', 'stock'];
    
    public $timestamps = false;

    /**
     * Sales of the product
     */
    public function sales()
    {
      return $this->belongsToMany(Sale::class, 'sales_products')
        ->as('sold')                
        ->withPivot('quantity');
    }
}
