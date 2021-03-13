<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class Canvas extends BaseModel
{
  protected $currency = ['unit_cost'];
  private $table = 'store_products';

  /**
   * Reads record from the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $query = trim(htmlentities(urldecode($array['q'] ?? '')));
    $limit = 30;
    $offset = isset($array['offset']) ? intval($array['offset']) * $limit : 0;

    $q = $this->c->dsql()
      ->table($this->table, 'sp')
      ->join('stores s', 'sp.store_id')
      ->join('products p', 'sp.product_id')
      ->join('categories c', $this->c->expr('c.id = p.category_id'))
      ->join('units u', $this->c->expr('u.id = p.unit_id'))
      ->group('p.id, sp.product_id')
      ->where([
        ['p.brand', 'like', "%{$query}%"],
        ['p.description', 'like', "%{$query}%"],
        ['s.name', 'like', "%{$query}%"],
      ]);

    if (isset($array['qid'])) {
      $products = $this->c->dsql()
        ->table('quotation_items')
        ->where('quotation_id', $array['qid'])
        ->field('product_id');

      $q->where('p.id', $products);
    }

    $q->field(
      [
        'product_id' => 'p.id',
        'brand' => 'p.brand',
        'description' => 'p.description',
        'category' => 'c.name',
        'unit' => 'u.name',
      ]
    )
      ->order('p.brand, p.description');

    if (!(isset($array['mode']) && $array['mode'] === 'all'))
      $q->limit($limit, $offset);

    $data = [];

    foreach ($q->get() as $row) {
      $c = new Canvas();
      foreach ($row as $key => $value) {
        $c->$key = $value;
      }
      $c->canvas = $this->getCanvas(intval($c->product_id));
      $data[] = $c->jsonSerialize();
    }

    return $data;
  }

  /**
   * Gets the TOP stores where lowest cost
   * 
   * @param int $product_id
   * @return array
   */
  private function getCanvas(int $product_id, int $top = 3)
  {
    $q = $this->c->dsql();
    $q->table($this->table, 'sp')
      ->join('stores s', 'sp.store_id')
      ->where('sp.product_id', $product_id)
      ->field(
        [
          'id' => 'sp.id',
          'name' => 's.name',
          'unit_cost' => 'sp.unit_cost'
        ]
      )
      ->order('sp.unit_cost')
      ->limit($top);

    $data = [];

    foreach ($q->get() as $row) {
      $c = [];
      foreach ($row as $key => $value) {
        $c[$key] = in_array($key, $this->currency) ? number_format(floatval($value), 2) : $value;
      }
      $data[] = $c;
    }

    return $data;
  }
}
