<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['cashier'];

    /**
     * Products included in the sale
     */
    public function products()
    {
      return $this->belongsToMany(Product::class, 'sales_products')
        ->as('sold')                
        ->withPivot('quantity');
    }
}
