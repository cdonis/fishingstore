<?php

namespace App\Repository\Eloquent;

use App\Models\Product;
use App\Repository\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Product repository
 */

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
  /**
   * Constructor
   * 
   * @param Product $model
   */
  public function __construct(Product $model)
  {
    parent::__construct($model);
  }

  /**
   * @inheritDoc
   */
  public function getList(Request $request): array
  {
    $filters = \json_decode($request->filter, true);           // Parameters for filtering
    $defaultSort = ['order' => 'ascend'];                      // Parameters for sorting
    $sorters = \json_decode($request->sort, true);

    // Parameters for filtering by "keyword".
    $keyword = $request->input('keyword');
    $keywordSearchFields = ['"id"'];                          // Models' fields to consider in "keyword" filtering

    try {
      $data = $this->model
        ->withFiltering($filters)
        ->withSorting($defaultSort, $sorters)
        ->withKeywordSearch($keyword, $keywordSearchFields);

      $items = null;
      $total = 0;

      if (!empty($request->current) && !empty($request->pageSize)) {
        $paginator = $data->paginate($request->input('pageSize'), '[*]', 'current');
        $items = $paginator->items();
        $total = $paginator->total();
      } else {
        $items = $data->get()->toArray();
        $total = count($items);
      }

      return [
        'success' => true,
        'data' => $items,
        'total' => $total,
      ];
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc 
   */
  public function updateStock(array $data): int
  {
      // Construct and use a single raw SQL to avoid using one query for each product in the list
      
      $productTable = $this->model->getModel()->getTable(); // The product table
      $cases = [];                                          // Conditions to use in the CASE clause
      $ids = [];                                            // Set of IDs for the WHERE clause

      foreach ($data['products'] as $product) {
          $id = (int) $product['id'];
          $cases[] = "WHEN {$id} then stock + {$product['quantity']}";
          $ids[] = $id;
      }

      $ids = implode(',', $ids);
      $cases = implode(' ', $cases);

      $result = 0;
      try {

        $result = DB::update("UPDATE {$productTable} SET stock = CASE id {$cases} END WHERE id in ({$ids})");
      
      } catch (\PDOException $e) {

        $error = $this->handlePDOExceptions($e);
        throw new \Exception($error['text'], $error['httpStatus']);
        
      }
      
      return $result;
  }      
}
