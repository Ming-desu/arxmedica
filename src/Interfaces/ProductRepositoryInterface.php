<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Product;

interface ProductRepositoryInterface
{
  public function create(Product $product): Product;
  public function read(array $array): array;
  public function update(Product $product): Product;
  public function delete(Product $product): Product;
  public function find(int $id);
}