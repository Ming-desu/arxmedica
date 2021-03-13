<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class QuotationItem extends BaseModel
{
  protected $hidden = ['qoutation_id'];
  private $table = 'quotation_items';

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
    $q = $this->c->dsql();
    $q->table($this->table, 'qi')
      ->join('products p', 'qi.product_id')
      ->join('categories c', 'p.category_id')
      ->join('units u', 'p.unit_id')
      ->field([
        'id' => 'qi.id',
        'product_id' => 'qi.product_id',
        'brand' => 'p.brand',
        'description' => 'p.description',
        'category' => 'c.name',
        'unit' => 'u.name',
        'quantity' => 'qi.quantity',
        'unit_cost' => 'qi.unit_cost'
      ]);

    if (isset($array['orderby']))
      $q->order($array['orderby']);

    if (count($where) > 0) {
      foreach ($where as $key => $value) {
        $q->where($key, $value);
      }
    }
  
    $data = [];

    foreach ($q->get() as $row) {
      $qi = new QuotationItem($row);
      $data[] = $qi->jsonSerialize();
    }
    
    return $data;
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
    foreach ($this->items as $item) {
      $q = $this->c->dsql();
      $q->table($this->table)
        ->set('quotation_id', $this->quotation_id)
        ->set('product_id', $item['id'])
        ->set('quantity', $item['quantity'])
        ->set('unit_cost', $item['unit_cost'])
        ->insert();
    }

    return $this;
  }

  /**
   * Updates record in the database
   */
  private function update(): self
  {
    $q = $this->c->dsql();
    
    // Delete the previous record
    $q->table($this->table)
      ->where('quotation_id', $this->quotation_id)
      ->delete();

    foreach ($this->items as $item) {
      $q = $this->c->dsql();
      $q->table($this->table)
        ->set('quotation_id', $this->quotation_id)
        ->set('product_id', $item['id'])
        ->set('quantity', $item['quantity'])
        ->set('unit_cost', $item['unit_cost'])
        ->insert();
    }

    return $this;
  }
}