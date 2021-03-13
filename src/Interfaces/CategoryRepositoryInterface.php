<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Category;

interface CategoryRepositoryInterface
{
  public function create(Category $category): Category;
  public function read(array $array): array;
  public function update(Category $category): Category;
  public function delete(Category $category): Category;
}