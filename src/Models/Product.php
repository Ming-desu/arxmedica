<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;
use App\Exceptions\RecordExistsException;

class Product extends BaseModel
{
  protected $hidden = ['created_at', 'updated_at'];
  protected $ucWords = ['brand', 'description'];
  private $table = 'products';

  /**
   * Inserts record to the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function insert(): self
  {
    // Check if product exists
    $this->isProductExists();

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
    $q->table($this->table, 'p')
      ->join('categories c', 'p.category_id')
      ->join('units u', 'p.unit_id')
      ->field([
        'id' => 'p.id',
        'brand',
        'description',
        'category' => 'c.name',
        'unit' => 'u.name',
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

    if (!(isset($array['mode']) && $array['mode'] === 'all'))
      $q->limit($limit, $offset);

    $q->order('brand, description');

    $data = [];

    foreach ($q->get() as $product) {
      $p = new Product();
      foreach ($product as $key => $value) {
        $p->$key = $value;
      }
      $data[] = $p;
    }

    return $data;
  }

  /**
   * Finds record in the database by id
   * 
   * @param int $id
   * @return Product|null
   */
  public function find(int $id = null)
  {
    $product = $this->read([], [
      'p.id' => $id ?? $this->id
    ])[0] ?? null;

    return $product;
  }

  /**
   * Save changes to existing record in database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function save(): self
  {
    // Check if product exists
    $this->isProductExists();

    return $this->update();
  }

  /**
   * Deletes record in the database
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
   * @return self
   */
  private function push(): self
  {
    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->insert();

    $this->id = $this->c->lastInsertId();

    return $this;
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
  private function isProductExists()
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('brand', $this->brand)
      ->where('description', $this->description)
      ->where('category_id', $this->category_id ?? 0)
      ->where('unit_id', $this->unit_id ?? 0)
      ->where('id', '!=', $this->id ?? 0);

    if ($q->getRow())
      throw new RecordExistsException('Product already exists.');
  }
}
