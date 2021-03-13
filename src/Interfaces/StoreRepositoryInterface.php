<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Store;

interface StoreRepositoryInterface
{
  public function create(array $array): Store;
  public function read(array $array): array;
  public function update(array $array): Store;
  public function delete(array $array): Store;
  public function find(int $id);
}