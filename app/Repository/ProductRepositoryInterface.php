<?php

namespace App\Repository;

use Illuminate\Http\Request;

/**
 * Interface ProductRepositoryInterface
 */
interface ProductRepositoryInterface extends EloquentRepositoryInterface
{
  /**
   * Return a list of products according to filters, pagination and sorting parameters specified on the request
   * @param Request $request
   * @return array ['success'=> boolean, 'data'=> array, 'total'=> int]
   */
  public function getList(Request $request): array;

  /**
   * Massive increments of product stock by constructing and use a single raw SQL to avoid using 
   * one query for each product in the list
   * 
   * @param array $data
   * @return int
   */
  public function updateStock(array $data): int;
}
