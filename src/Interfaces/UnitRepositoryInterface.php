<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Unit;

interface UnitRepositoryInterface
{
  public function create(Unit $unit): Unit;
  public function read(array $array): array;
  public function update(Unit $unit): Unit;
  public function delete(Unit $unit): Unit;
}