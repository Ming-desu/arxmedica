<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
  /**
   * Creates a new product record in the database
   * 
   * @param Product $product
   * @throws RecordExistsException
   * @return Product
   */
  public function create(Product $product): Product
  {
    $product->setConnection($this->c);
    return $product->insert();
  }

  /**
   * Reads products in the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $product = new Product();
    $product->setConnection($this->c);
    return $product->read($array);
  }

  /**
   * Updates product in the database
   * 
   * @param Product $product
   * @throws RecordExistsException
   * @return Product
   */
  public function update(Product $product): Product
  {
    $product->setConnection($this->c);
    return $product->save();
  }

  /**
   * Deletes category from the the database
   * 
   * @param Product $product
   * @return Product
   */
  public function delete(Product $product): Product
  {
    $product->setConnection($this->c);
    return $product->delete();
  }

  /**
   * Finds record based on its id
   * 
   * @param int $id
   * @return Product|null
   */
  public function find(int $id)
  {
    $product = new Product(['id' => $id]);
    $product->setConnection($this->c);
    return $product->find();
  }
}