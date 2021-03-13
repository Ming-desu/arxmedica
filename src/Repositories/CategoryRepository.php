<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
  /**
   * Creates a new category record in the database
   * 
   * @param Category $category
   * @throws RecordExistsException
   * @return Category
   */
  public function create(Category $category): Category
  {
    $category->setConnection($this->c);
    return $category->insert();
  }

  /**
   * Read categories in the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $category = new Category();
    $category->setConnection($this->c);
    return $category->read($array);
  }

  /**
   * Updates an existing category record in the database
   * 
   * @param Category $category
   * @throws RecordExistsException
   * @return Category
   */
  public function update(Category $category): Category
  {
    $category->setConnection($this->c);
    return $category->save();
  }

  /**
   * Deletes category from the the database
   * 
   * @param Category $category
   * @return Category
   */
  public function delete(Category $category): Category
  {
    $category->setConnection($this->c);
    return $category->delete();
  }
}