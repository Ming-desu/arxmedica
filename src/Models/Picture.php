<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class Picture extends BaseModel
{
  private $table = 'pictures';

  public function insert()
  {
    return $this->push();
  }

  /**
   * Finds a picture by its id
   * 
   * @param int $id
   * @return null|Picture
   */
  public function find(int $id = null)
  {
    $pid = $id ?? $this->id;
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $pid);

    $result = $q->getRow();
    return $result ? new Picture($result) : null;
  }

  public function delete(int $id = null): Picture
  {
    // Delete the current uploaded image
    $picture = $this->find($id);

    if ($picture) {
      $directory = __DIR__ . '/../../public/uploads/' . $picture->file_name;
      if (file_exists($directory)) {
        unlink($directory);
      }
    }

    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $id ?? $this->id)
      ->delete();

    return $picture;
  }

  private function push()
  {
    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->insert();

    $this->id = $this->c->lastInsertId();

    return $this;
  }
}
