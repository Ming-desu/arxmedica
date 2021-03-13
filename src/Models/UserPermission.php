<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;

class UserPermission extends BaseModel
{
  protected $hidden = ['user_id'];

  public function insert()
  {
    $q = $this->c->dsql();
    $q->table('user_permissions');

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->insert();
  }

  public function save()
  {
    $q = $this->c->dsql();
    $q->table('user_permissions');

    foreach ($this->getProperties() as $key => $value) {
      $q->set($key, $value);
    }

    $q->where('user_id', $this->user_id);
    $q->update();
  }

  public function delete()
  {
    $q = $this->c->dsql();
    $q->table('user_permissions')
      ->where('user_id', $this->user_id)
      ->delete();
  }

  /**
   * Gets the permission of a user_id
   * 
   * @return string
   */
  public function getPermission(): string
  {
    $q = $this->c->dsql();
    $q->table('user_permissions')
      ->where('user_id', $this->user_id);

    $permission = $q->getRow();

    if ($permission)
      return $permission['permission'];
    return '';
  }
}
