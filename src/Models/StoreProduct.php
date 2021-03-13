<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;
use App\Exceptions\RecordExistsException;

class StoreProduct extends BaseModel
{
  protected $hidden = [];
  protected $ucWords = [];
  protected $currency = ['unit_cost'];
  private $table = 'store_products';

  /**
   * Inserts record to the database
   * 
   * @throws RecordExistsException
   * @return StoreProduct
   */
  public function insert(): StoreProduct
  {
    // Check if store product exists
    $this->isStoreProductExists();

    return $this->push();
  }

  /**
   * Reads record from the database
   * 
   * @param array $array
   * @param array $where
   * @return array
   */
  public function read(array $array, array $where = []): array
  {
    $query = trim(htmlentities(urldecode($array['q'] ?? '')));
    $limit = 30;
    $offset = isset($array['offset']) ? intval($array['offset']) * $limit : 0;

    $q = $this->c->dsql();
    $q->table($this->table, 'sp')
      ->join('products p', 'sp.product_id')
      ->join('units u', 'p.unit_id')
      ->join('categories c', 'p.category_id')
      ->field([
        'id' => 'sp.id',
        'product_id' => 'p.id',
        'brand',
        'description',
        'category' => 'c.name',
        'unit' => 'u.name',
        'unit_cost' => 'sp.unit_cost',
        'created_at' => 'p.created_at',
        'updated_at' => 'p.updated_at'
      ])
      ->where([
        ['brand', 'like', "%{$query}%"],
        ['description', 'like', "%{$query}%"]
      ]);

    if (count($where) > 0) {
      foreach ($where as $key => $value) {
        $q->where($key, $value);
      }
    }

    $q->order('description, brand');

    if (!(isset($array['mode']) && $array['mode'] !== 'all'))
      $q->limit($limit, $offset);

    $data = [];

    foreach ($q->get() as $store_product) {
      $sp = new StoreProduct();
      foreach ($store_product as $key => $value) {
        $sp->$key = $value;
      }
      $data[] = $sp;
    }

    return $data;
  }

  /**
   * Find record by its id
   * 
   * @return mixed
   */
  public function find($id = null)
  {
    $data = $this->read([], [
      'sp.id' => $id ?? $this->id
    ])[0] ?? null;

    return $data;
  }

  /**
   * Saves record to the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function save(): self
  {
    // Check if store product exists
    $this->isStoreProductExists();

    return $this->update();
  }

  /**
   * Deletes record from the database
   * 
   * @return self
   */
  public function delete(): self
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $this->id)
      ->delete();

    return $this;
  }

  /**
   * Pushes record to the database
   * 
   * @return StoreProduct
   */
  private function push(): StoreProduct
  {
    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->insert();

    $this->id = $this->c->lastInsertId();

    return $this->find();
  }

  /**
   * Updates record to the database
   * 
   * @return self
   */
  private function update(): self
  {
    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->where('id', $this->id);
    $q->update();

    return $this;
  }

  /**
   * Checks if the record is exists
   * 
   * @throws RecordExistsException
   */
  private function isStoreProductExists()
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('store_id', $this->store_id)
      ->where('product_id', $this->product_id)
      ->where('id', '!=', $this->id ?? 0);

    if ($q->getRow())
      throw new RecordExistsException('Store product already exists.');
  }

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    array_walk($this->properties, function (&$value, $key) {
      $value = in_array($key, $this->currency) ? number_format(floatval($value), 2) : $value;
    });

    return parent::jsonSerialize();
  }
}
