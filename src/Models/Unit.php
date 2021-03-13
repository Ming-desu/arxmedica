<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;
use App\Exceptions\RecordExistsException;

class Unit extends BaseModel
{
  protected $hidden = ['created_at', 'updated_at'];
  protected $ucWords = ['name'];
  private $table = 'units';

  /**
   * Inserts data to the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function insert(): self
  {
    // Check if unit exists
    $this->isUnitExists();

    return $this->push();
  }

  /**
   * Reads data from the database
   * 
   * @param array $array
   * @param array $where
   * @return array
   */
  public function read(array $array, array $where = []): array
  {
    $query = trim(htmlentities(urldecode($array['q'])));
    $limit = 30;
    $offset = isset($array['offset']) ? intval($array['offset']) * $limit : 0;

    $q = $this->c->dsql();
    $q->table($this->table)
      ->where([
        ['name', 'like', "%{$query}%"]
      ]);

    if (count($where) > 0) {
      foreach ($where as $key => $value) {
        $q->where($key, $value);
      }
    }

    if (!(isset($array['mode']) && $array['mode'] === 'all'))
      $q->limit($limit, $offset);

    $q->order('name');

    $data = [];

    foreach ($q->get() as $unit) {
      $u = new Unit();
      foreach ($unit as $key => $value) {
        $u->$key = $value;
      }
      $u->count = $q->dsql()
        ->field($q->expr('COUNT(id) as count'))
        ->table('products')
        ->where('unit_id', $u->id)
        ->getOne();
      $data[] = $u;
    }

    return $data;
  }

  /**
   * Save changes to existing record in database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function save(): self
  {
    // Check if unit exists
    $this->isUnitExists();

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
   * @return mixed
   */
  private function push()
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
   * @return self;
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
  private function isUnitExists()
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('name', $this->name)
      ->where('id', '!=', $this->id ?? 0);

    if ($q->getRow())
      throw new RecordExistsException('Unit already exists.', 400);
  }
}
