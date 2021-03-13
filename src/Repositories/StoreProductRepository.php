<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\StoreProductRepositoryInterface;
use App\Models\StoreProduct;

class StoreProductRepository extends BaseRepository implements StoreProductRepositoryInterface
{
  /**
   * Creates store product to the database
   * 
   * @param StoreProduct $storeProduct
   * @throws RecordExistsException
   * @return StoreProduct
   */
  public function create(StoreProduct $storeProduct): StoreProduct
  {
    $storeProduct->setConnection($this->c);
    return $storeProduct->insert();
  }

  /**
   * Reads store products from the database
   * 
   * @param array $array
   * @param int $store_id
   * @return array
   */
  public function read(array $array, int $store_id): array
  {
    $storeProduct = new StoreProduct();
    $storeProduct->setConnection($this->c);
    
    return $storeProduct->read($array, [
      'store_id' => $store_id
    ]);
  }

  /**
   * Updates store product to the database
   * 
   * @param StoreProduct $storeProduct
   * @throws RecordExistsException
   * @return StoreProduct
   */
  public function update(StoreProduct $storeProduct): StoreProduct
  {
    $storeProduct->setConnection($this->c);
    return $storeProduct->save();
  }

  /**
   * Delete store product to the database
   * 
   * @param StoreProduct $storeProduct
   * @throws RecordExistsException
   * @return StoreProduct
   */
  public function delete(StoreProduct $storeProduct): StoreProduct
  {
    $storeProduct->setConnection($this->c);
    return $storeProduct->delete(); 
  }
}