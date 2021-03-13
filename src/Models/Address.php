<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class Address extends BaseModel
{
  protected $hidden = ['created_at', 'updated_at'];
  protected $ucWords = ['street', 'municipality', 'province'];
  private $table = 'addresses';

  /**
   * Insert record to the database
   * 
   * @return self
   */
  public function insert(): self
  {
    return $this->push();
  }

  /**
   * Finds record in the database by id
   * 
   * @param int $id
   * @return Address
   */
  public function find(int $id = null): Address
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $id ?? $this->id);

    return new Address($q->getRow());
  }

  /**
   * Saves record in the database
   * 
   * @return self
   */
  public function save(): self
  {
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
}