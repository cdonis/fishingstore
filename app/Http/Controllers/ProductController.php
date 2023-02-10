<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Product;
use App\Services\ProductServices;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * @var productService
     */
    protected ProductServices $productServices;

    /**
     * Constructor
     * 
     * @param ProductServices $productServices
     */
    public function __construct(ProductServices $productServices)
    {
      $this->productServices = $productServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(
          $this->productServices->getExistingProducts(),
          Response::HTTP_OK
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productServices->create($request->validated());
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
        $product = $this->productServices->update($request->validated());
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
        return $this->productServices->updateStock($request->validated()) ; 
    }
}