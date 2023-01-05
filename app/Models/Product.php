<?php

namespace App\Models;

use App\Enums\ProductRanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
      'purchase_price' => 'float',
      'sale_price' => 'float',
      'range' => ProductRanges::class
    ];

    protected $fillable = ['name', 'serial', 'purchase_price', 'sale_price', 'range', 'stock'];
    
    public $timestamps = false;
}
