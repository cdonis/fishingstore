<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $sales = Sale::with('products')->get()->toArray();

      return response()->json([
          'data' => $sales,
          'total' => count($sales)
      ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\StoreSaleRequest
     */
    public function store(StoreSaleRequest $request)
    {
        $data = $request->all();
        
        DB::beginTransaction();
        try {
          // Create the sale record
          $sale = Sale::create(['cashier' => $data['cashier']]);

          // Process each product
          foreach($data['products'] as $product) {
            $productModel = Product::find($product['id']);
            
            if ($productModel && ($productModel->stock - $product['quantity'] >= 0)) {                
              // Product exists and has enougth stock
              $newStock = $productModel->stock - $product['quantity'];

              // Attach the product to the sale
              $sale->products()->attach($product['id'], ['quantity' => $product['quantity']]);

              // Decrease product stock 
              $productModel->stock = $newStock;
              $productModel->save();

            } else {
              // Product does not exists or has no stock. Stop process and return HTTP_UNPROCESSABLE_ENTITY
              DB::rollback();
              return response([
                'message' => 'Invalid product or not enought stock',
                'errors'  => [
                  "product.{$product['id']}" => [
                    "The product.{$product['id']} does not exists or has no enought stock"
                  ]
                ]
              ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
          }

          DB::commit();
          return response()->json($sale, Response::HTTP_CREATED);

        } catch (Exception $e) {
          DB::rollback();
          return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        $data = $sale->toArray();
        $data['products'] = $sale->products;

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return response()->noContent();
    }
}
