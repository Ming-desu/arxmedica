<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class Quotation extends BaseModel
{
  protected $hidden = ['representative_id', 'created_by'];
  protected $ucWords = ['project_title'];
  private $table = 'quotations';

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
        ['pr_no', 'like', "%{$query}%"],
        ['project_title', 'like', "%{$query}%"]
      ]);

    if (count($where) > 0) {
      foreach ($where as $key => $value) {
        $q->where($key, $value);
      }
    }

    $q->order('created_at DESC, updated_at DESC');

    if (!(isset($array['mode']) && $array['mode'] === 'all'))
      $q->limit($limit, $offset);

    $data = [];

    foreach ($q->get() as $row) {
      $quotation = new Quotation($row);
      $quotation->representative = $this->getRepresentative(intval($quotation->representative_id))->jsonSerialize();
      $quotation->recipients = $this->getRecipients(explode(',', $quotation->recipients));
      $quotation->items = $this->getItems(intval($quotation->id), $array);
      $quotation->date_issued = date("M d, Y", strtotime($quotation->date_issued));
      $quotation->created_at = date("M d, Y h:i:s A", strtotime($quotation->created_at));
      $quotation->updated_at = $quotation->updated_at === null ? null : date("M d, Y h:i:s A", strtotime($quotation->updated_at));
      $data[] = $quotation;
    }

    return $data;
  }

  /**
   * Finds record by its id
   * 
   * @param int $id
   * @return Quotation|null
   */
  public function find(int $id = null)
  {
    $quotation = $this->read([], [
      'id' => $id ?? $this->id
    ])[0] ?? null;

    if ($quotation)
      $quotation->date_issued = date('Y-m-d', strtotime($quotation->date_issued));

    return $quotation;
  }

  /**
   * Save changes to existing record in database
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
   * @return Quotation
   */
  public function delete(): Quotation
  {
    $quotation = $this->find();

    // Deletes the quotation itself
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $quotation->id)
      ->delete();

    // Deletes the quotation items
    $q = $this->c->dsql();
    $q->table('quotation_items')
      ->where('quotation_id', $quotation->id)
      ->delete();

    // Deletes the personnel
    $q = $this->c->dsql();
    $q->table('personnels')
      ->where('id', $quotation->representative['id'])
      ->delete();
    
    // Deletes the address
    $q = $this->c->dsql();
    $q->table('addresses')
      ->where('id', $quotation->recipients[0]['address']['id'])
      ->delete();

    // Deletes the recipients
    foreach ($quotation->recipients as $row) {
      $q = $this->c->dsql();
      $q->table('recipients')
        ->where('id', $row['id'])
        ->delete();
    }

    return $quotation;
  }

  /**
   * Gets record in print mode
   * 
   * @param array $array
   * @return Quotation|null
   */
  public function print(array $array)
  {
    $quotation = $this->read($array, [
      'id' => $id ?? $this->id
    ])[0] ?? null;

    if ($quotation)
      $quotation->date_issued = date('Y-m-d', strtotime($quotation->date_issued));

    return $quotation;
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
   * Gets the representative associated with this quotation
   * 
   * @param int $id
   * @return Personnel 
   */
  private function getRepresentative(int $id): Personnel
  {
    $personnel = new Personnel();
    $personnel->setConnection($this->c);
    return $personnel->find($id);
  }

  /**
   * Gets the recipients associated with this quotation
   * 
   * @param array $ids
   * @return Recipient
   */
  private function getRecipients(array $ids): array
  {
    $data = [];

    foreach ($ids as $id) {
      $recipient = new Recipient();
      $recipient->setConnection($this->c);
      $data[] = $recipient->find(intval($id))->jsonSerialize();
    }

    return $data;
  }

  /**
   * Gets the items associated with this quotation
   * 
   * @param int $id
   * @param array $array
   * @return array
   */
  private function getItems(int $id, array $array = []): array
  {
    $quotation_item = new QuotationItem();
    $quotation_item->setConnection($this->c);
    return $quotation_item->read($array, [
      'qi.quotation_id' => $id
    ]);
  }
}