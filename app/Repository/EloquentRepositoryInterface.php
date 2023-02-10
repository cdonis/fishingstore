<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface EloquentRepositoryInterface
 * @package App\Repository
 */
interface EloquentRepositoryInterface
{
  /**
   * Return a list of objects that satisfy conditions expressed in $filters
   * 
   * @param array $filters Array of tuples in the form [key, operator, value]. Operator '=' can be optional 
   * @return Collection
   */
  public function getByFilters(array $filters): Collection;

  /**
   * @param array $attributes
   * @return Model
   */
  public function create(array $attributes): Model;

  /**
   * @param $id
   * @return Model
   */
  public function find($id): Model;

  /**
   * @param $id
   * 
   * @return Model|null
   */
  public function findOrNull($id): ?Model;

  /**
   * Return a list of all elements
   * @return Collection
   */
  public function all(): Collection;

  /**
   * Return an element that satisfy $filters criteria or NULL if not found.
   * @param array $filters: Filters array in the form [key, operator, value]. Operator '=' can be optional
   * @return Model
   */
  public function findBy(array $filters): ?Model;

  /**
   * @param array $attributes
   * @param $id
   * @return Model
   */
  public function update(array $attributes, $id): Model;

  /**
   * @param mixed $id
   * @return int
   */
  public function delete($id): int;

  /**
   * Update or create new element. Expected: model primary key is named "id"
   * @param array $attributes Attributes values
   * @param any $id Element ID
   * @return Model 
   */
  public function updateOrCreate(array $attributes, $id): Model;


  /**
   * Mass updates specified by $updates on records meeting $filters critera
   * 
   * @param array $filters  Criteria used by "where" clause of Query Builder 
   * @param array $updates  Associative array with column-value pais
   * @return void
   */
  public function massUpdate(array $filters, array $updates): void;

  /**
   * Update or create new element. Recibe un conjunto de filtros utilizados para determinar el elemento a crear o actualizar
   * @author Carlos Alberto Donis Diaz <cdonisdiaz@gmail.com>
   * @param array $attributes: Values for attributes
   * @param array $filters: Array of pairs key, value with selection criteria
   * @return Model 
   */
  public function updateOrCreateByFilters(array $attributes, array $filters): Model;

  /**
   * Batch deletion
   * 
   * @param array $key  Array of key of elements to remove. Expected: model primary key is named "id" 
   * @return int
   */
  public function batchDeletion(array $keys): int;

  /**
   * Insert one or several records
   * 
   * @param array $data  Associative array with pairs (attributes, values) to be inserted. 
   *                     Permits a matrix with a set ob objects 
   * @return void
   */
  public function insert(array $data): void;

  /**
   * Insert new records or update existing ones.
   *
   * @param  array  $values
   * @param  array|string  $uniqueBy
   * @param  array|null  $update
   * @return int
   */
  public function upsert(array $values, $uniqueBy, $update = null);
}
