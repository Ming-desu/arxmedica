<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Store;
use App\Models\StoreProduct;

interface StoreProductRepositoryInterface
{
  public function create(StoreProduct $storeProduct): StoreProduct;
  public function read(array $array, int $id): array;
  public function update(StoreProduct $storeProduct): StoreProduct;
  public function delete(StoreProduct $storeProduct): StoreProduct;
}