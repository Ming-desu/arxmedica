<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;
use App\Exceptions\RecordExistsException;

class Store extends BaseModel
{
  protected $hidden = ['banner_id', 'address_id', 'personnel_id', 'created_at', 'updated_at'];
  protected $ucWords = ['name'];
  private $table = 'stores';

  /**
   * Inserts record to the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function insert(): self
  {
    // Check if store exists
    $this->isStoreExisting();

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
    $q->table($this->table)
      ->where([
        ['name', 'like', "%{$query}%"]
      ]);
    // ->limit($limit, $offset)
    // ->order('name');

    if (count($where) > 0) {
      foreach ($where as $key => $value) {
        $q->where($key, $value);
      }
    }

    $q->order('name');

    if (!(isset($array['mode']) && $array['mode'] === 'all'))
      $q->limit($limit, $offset);

    $data = [];

    foreach ($q->get() as $store) {
      $s = new Store();
      foreach ($store as $key => $value) {
        $s->$key = $value;
      }
      $s->address = $this->getAddress(intval($s->address_id))->jsonSerialize();
      $s->personnel = $this->getPersonnel(intval($s->personnel_id))->jsonSerialize();
      $data[] = $s;
    }

    return $data;
  }

  /**
   * Finds record by id from the database
   * 
   * @param int $id
   * @return Store|null
   */
  public function find(int $id = null)
  {
    $store = $this->read([], [
      'id' => $id ?? $this->id
    ])[0] ?? null;

    return $store;
  }

  /**
   * Saves record in the database
   * 
   * @return self
   */
  public function save(): self
  {
    // Check if store exists
    $this->isStoreExisting();

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

    // Delete the store products
    $q = $this->c->dsql();
    $q->table('store_products')
      ->where('store_id', $this->id)
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
   * Gets the address associated with the current store
   * 
   * @param int $id
   * @return Address
   */
  private function getAddress(int $id): Address
  {
    $address = new Address();
    $address->setConnection($this->c);
    return $address->find($id);
  }

  /**
   * Gets the personnel associated with the current store
   * 
   * @param int $id
   * @return Personnel
   */
  private function getPersonnel(int $id): Personnel
  {
    $personnel = new Personnel();
    $personnel->setConnection($this->c);
    return $personnel->find($id);
  }

  /**
   * Updates record in the database
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
   * Checks if store exists
   * 
   * @throws RecordExistsException
   */
  private function isStoreExisting()
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('name', $this->name)
      ->where('id', '!=', $this->id ?? 0);

    if ($q->getRow())
      throw new RecordExistsException('Store already exists.');
  }
}
