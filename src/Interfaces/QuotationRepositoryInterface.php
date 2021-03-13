<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Quotation;

interface QuotationRepositoryInterface
{
  public function create(array $array): Quotation;
  public function read(array $array): array;
  public function find(int $id);
  public function update(array $array): Quotation;
  public function delete(array $array): Quotation;

  public function print(int $id, array $array);
}