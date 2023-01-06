<?php

namespace App\Http\Controllers;

use App\Enums\ProductRanges;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PDF;
use stdClass;

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
        $data = $request->validated();
        
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

          $responseData = $sale->toArray();
          $responseData['products'] = $sale->products;

          return response()->json($responseData, Response::HTTP_CREATED);

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
     * Update the sale resource and its products (in pivot table).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(StoreSaleRequest $request, Sale $sale)
    {
        $data = $request->validated();
        
        DB::beginTransaction();
        try {
          // Update sales table
          $sale->update(['cashier' => $data['cashier']]);
  
          // Restore stock in products to be removed from the sale
          $sale->products()
               ->wherePivotNotIn('product_id', array_column($data['products'], 'id'))
               ->each(function ($product) {
                    $product->stock += $product->sold->quantity;
                    $product->save();
                });

          // Update product stock for modified and new products
          $updatedProducts = array();
          foreach($data['products'] as $product) {
            $productModel = Product::find($product['id']);
            // Validate product exists
            if ($productModel) {  
              // Set new sale-product info
              $updatedProducts[$product['id']] = ['quantity' => $product['quantity']];
              
              // Update product stock according to differences in the quantities 
              $saleProduct = $productModel->sales()->find($sale->id);
              $previousQty = ($saleProduct) ? $saleProduct->sold->quantity : 0;
              $productModel->stock += ($previousQty - $product['quantity']);
              $productModel->save();
            } else {
              // Product does not exists. Stop process and return HTTP_UNPROCESSABLE_ENTITY
              DB::rollback();
              return response([
                'message' => 'Invalid product ID',
                'errors'  => [
                  "product.{$product['id']}" => [
                    "The product.{$product['id']} does not exists."
                  ]
                ]
              ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
          }

          // Update pivot table (sales_products) with the new set of products. 
          $sale->products()->sync($updatedProducts);  // <-- Expected estructure [<product_id> => ['quantity' => <qty>], ...]

          DB::commit();

          $responseData = $sale->toArray();
          $responseData['products'] = $sale->products;

          return response()->json($responseData, Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
          DB::rollback();
          return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function salesReport()
    {
      // Initialize object containing report data
      $reportData = new stdClass();
      $reportData->lowrange_total = 0;
      $reportData->lowrange_profit = 0;
      $reportData->midrange_total = 0;
      $reportData->midrange_profit = 0;
      $reportData->highrange_total = 0;
      $reportData->highrange_profit = 0;

      Sale::all()->each(function ($sale) use ($reportData) { 
        foreach($sale->products as $product) {
          $totalSold = $product->sale_price * $product->sold->quantity;
          $totalProfit = ($product->sale_price - $product->purchase_price) * $product->sold->quantity;

          switch ($product->range) {
            case ProductRanges::LOW_RANGE : {
              $reportData->lowrange_total += $totalSold;
              $reportData->lowrange_profit += $totalProfit;
              break;
            }
            case ProductRanges::MID_RANGE : {
              $reportData->midrange_total += $totalSold;
              $reportData->midrange_profit += $totalProfit;
              break;
            }
            case ProductRanges::HIGH_RANGE : {
              $reportData->highrange_total += $totalSold;
              $reportData->highrange_profit += $totalProfit;
              break;
            }
          }
        }
      });

      $reportData->total = $reportData->lowrange_total + $reportData->midrange_total + $reportData->highrange_total;
      $reportData->profit = $reportData->lowrange_profit + $reportData->midrange_profit + $reportData->highrange_profit;
      
      // Format numbers with two decimals, '.' as decimal separator and ',' as thousand separetor
      foreach(get_object_vars($reportData) as $propertie => $value) 
        $reportData->$propertie = number_format($value, 2, '.', ',');

      // Create the pdf
      $pdf = PDF::loadView('pdf', compact('reportData'))->setPaper('a4', 'landscape');

      return $pdf->download('sales_report.pdf');
    }
}
