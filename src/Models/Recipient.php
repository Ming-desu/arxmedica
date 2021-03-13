<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class Recipient extends BaseModel
{
  protected $hidden = ['address_id'];
  protected $ucWords = ['first_name', 'last_name', 'position'];
  private $table = 'recipients';

  /**
   * Inserts record to the database
   * 
   * @return self
   */
  public function insert(): self
  {
    return $this->push();
  }

  /**
   * Finds record based on its id
   * 
   * @param int $id
   * @return Recipient
   */
  public function find(int $id = null): Recipient
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $id ?? $this->id);

    // Create the recipient object
    $recipient = new Recipient($q->getRow());

    // Adds the fields associated with the recipient object
    $recipient->address = $this->getAddress(intval($recipient->address_id))->jsonSerialize();

    return $recipient;
  }

  /**
   * Saves changes to existing record in database
   * 
   * @return self
   */
  public function save(): self
  {
    return $this->update();
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
   * Gets the address associated with this recipient
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
}