<?php

namespace App\Repository\Eloquent;

use App\Repository\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class BaseRepository implements EloquentRepositoryInterface
{
  /**
   * Manage Postgres PDO driver specific exceptions 
   * @param \PDOException $e Exception
   * @return array $error['text' => 'Error message', 'httpStatus' => 'Error code']   
   */
  protected function handlePDOExceptions(\PDOException $e): array
  {
    $error = array();
    $error['text'] = '';
    switch ($e->getCode()) {
      case '22P02':  // UUID with wrong format trigger this error code
        $error['text'] = "Wrong parameters format, e.g wrong UUID." . $e->errorInfo[2];
        $error['httpStatus'] = Response::HTTP_BAD_REQUEST;
        break;
      case '23503': // Foreign key violation
        $error['text'] = 'Element trying to delete is refered by other resources';
        $error['httpStatus'] = 409;
        break;
      case '7':      // Postgress not available, wrong database name, server not found, wrong credentials
        $error['text'] = 'Error interacting with database server: ' . utf8_encode($e->errorInfo[2]);
        $error['httpStatus'] = Response::HTTP_INTERNAL_SERVER_ERROR;
        break;
      default:        // Other PDO error should be reported as INTERNAL SERVER ERRORS
        $error['text'] = utf8_encode($e->errorInfo[2]);
        $error['httpStatus'] = Response::HTTP_INTERNAL_SERVER_ERROR;
    }
    return $error;
  }

  /**
   * @var Model
   */
  protected $model;

  /**
   * BaseRepository constructor.
   *
   * @param Model $model
   */
  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  /**
   * @return Collection
   */
  public function all(): Collection
  {
    try {

      return $this->model->all();

    } catch (\PDOException $e) {

      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);

    }
  }

  /**
   * @inheritDoc
   */
  public function getByFilters(array $filters): Collection
  {
    try {

      return $this->model->where($filters)->get();

    } catch (\PDOException $e) {

      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);

    }   
  }

  /**
   * @inheritDoc
   */
  public function create(array $attributes): Model
  {
    try {

      return $this->model->create($attributes);

    } catch (\PDOException $e) {

      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
      
    }
  }

  /**
   * @inheritDoc
   */
  public function insert(array $data): void
  {
    $this->model->insert($data);
  }

  /**
   * @inheritDoc
   */
  public function find($id): Model
  {
    try {
      return $this->model->findOrFail($id);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function findOrNull($id): ?Model
  {
    try {
      return $this->model->find($id);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function findBy(array $filters): ?Model
  {
    try {
      return $this->model->where($filters)->first();
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function update(array $attributes, $id): Model
  {
    try {

      $item = $this->model->findOrFail($id);
      $item->fill($attributes);
      $item->save();
      return $item;

    } catch (\PDOException $e) {

      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
      
    }
  }

  /**
   * @inheritDoc
   */
  public function updateOrCreate(array $attributes, $id): Model
  {
    try {
      return $this->model->updateOrCreate(['id' => $id], $attributes);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
    public function massUpdate(array $filters, array $updates): void
    {
        try {
            $this->model
                ->where($filters)
                ->update($updates);
        } catch (\PDOException $e) {
            $error = $this->handlePDOExceptions($e);
            throw new \Exception($error['text'], $error['httpStatus']);
        }
    }

  /**
   * @inheritDoc
   */
  public function updateOrCreateByFilters(array $attributes, array $filters): Model
  {
    try {
      return $this->model->updateOrCreate($filters, $attributes);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function delete($id): int
  {
    try {
      return $this->model->destroy($id);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function batchDeletion(array $keys): int
  {
    try {
      return $this->model->whereIn('id', $keys)->delete();
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }

  /**
   * @inheritDoc
   */
  public function upsert(array $values, $uniqueBy, $update = null)
  {
    try {
      return $this->model->upsert($values, $uniqueBy, $update);
    } catch (\PDOException $e) {
      $error = $this->handlePDOExceptions($e);
      throw new \Exception($error['text'], $error['httpStatus']);
    }
  }
}
