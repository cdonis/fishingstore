<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Product;
use App\Services\ProductServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get()->toArray();

        return response()->json([
            'data' => $products,
            'total' => count($products)
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['range'] = ProductServices::get_product_range($data['purchase_price'], $data['sale_price']);
        
        $product = Product::create($data);

        return response()->json($product, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json($product, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\StoreProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['range'] = ProductServices::get_product_range($data['purchase_price'], $data['sale_price']);
        
        $product->update($data);

        return response()->json($product, Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }

    /**
     * Massive stock update
     * 
     * @param Illuminate\Http\UpdateStockRequest $request
     * @return \Illuminate\Http\Response
     */
    public function updateStock(UpdateStockRequest $request) 
    {
        // Construct and use a single raw SQL to avoid using one query for each product in the list
        
        $productTable = Product::getModel()->getTable();      // The product table
        $cases = [];                                          // Conditions to use in the CASE clause
        $ids = [];                                            // Set of IDs for the WHERE clause

        $data = $request->validated();
        foreach ($data['products'] as $product) {
            $id = (int) $product['id'];
            $cases[] = "WHEN {$id} then stock + {$product['quantity']}";
            $ids[] = $id;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);

        return DB::update("UPDATE {$productTable} SET stock = CASE id {$cases} END WHERE id in ({$ids})");
    }
}
