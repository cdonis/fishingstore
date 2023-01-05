<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $productID = ($this->product) ? $this->product->id : '';
        
        return [
          'name' => [
            'required', 
            'max:255', 
            Rule::unique('products')->ignore($productID)
          ],
          'serial' => [
            'required', 
            'max:10', 
            'regex:/^[0-9]{10}$/', 
            Rule::unique('products')->ignore($productID)
          ],
          'purchase_price' => ['required', 'numeric'],
          'sale_price' => ['required', 'numeric'],
          'stock' => ['required', 'numeric']
        ];
    }
}
