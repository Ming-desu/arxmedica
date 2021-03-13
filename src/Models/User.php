<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Base\BaseModel;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\RecordExistsException;
use App\Exceptions\UserInactiveException;
use App\Helpers\AuthHelper;

class User extends BaseModel
{
  protected $hidden = ['password', 'password_salt', 'picture_id', 'created_at', 'updated_at'];
  protected $ucWords = ['first_name', 'last_name'];
  private $table = 'users';

  /**
   * Inserts the record to the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function insert(): self
  {
    // Check if user is existing
    $this->isUserExists();

    // Hashes the password of the user
    $this->hashPassword();

    $this->setStatus();

    // Pushes record to the database
    return $this->push();
  }

  /**
   * Tries to login the user
   * 
   * @throws UserInactiveException
   * @throws InvalidCredentialsException
   * @return User
   */
  public function login(): User
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('username', $this->username);

    $user = $q->getRow();
    if ($user) {
      if (password_verify($this->password . $user['password_salt'], $user['password'])) {
        $user = new User($user);

        if ($user->status === 'inactive')
          throw new UserInactiveException();

        $user->picture = $this->getPicture(intval($user->picture_id));
        $user->permissions = $this->getPermission(intval($user->id));
        return $user;
      }
    }

    throw new InvalidCredentialsException();
  }

  /**
   * Reads user from the database
   *
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $query = trim(htmlentities(urldecode($array['q'] ?? '')));
    $limit = 30;
    $offset = isset($array['offset']) ? intval($array['offset']) * $limit : 0;

    $q = $this->c->dsql();
    $q->table($this->table)
      ->where([
        ['first_name', 'like', "%{$query}%"],
        ['last_name', 'like', "%{$query}%"],
        ['username', 'like', "%{$query}%"]
      ])
      ->where('id', '!=', AuthHelper::getCurrentUser()->id)
      ->where('id', '!=', 1)
      ->limit($limit, $offset)
      ->order('first_name');

    $data = [];

    foreach ($q->get() as $user) {
      $u = new User();
      foreach ($user as $key => $value) {
        $u->$key = in_array($key, $this->ucWords) ? ucwords($value) : $value;
      }
      $u->picture = $this->getPicture(intval($u->picture_id));
      $data[] = $u;
    }

    return $data;
  }

  /**
   * Finds a user by it's id
   * 
   * @param int $id
   * @return User|null
   */
  public function find(int $id = null)
  {
    $uid = $id ?? $this->id;
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $uid);

    $result = $q->getRow();

    if (!$result)
      return null;

    $user = new User($result);
    $user->picture = $this->getPicture(intval($user->picture_id));
    $user->permissions = $this->getPermission(intval($user->id));
    return $user;
  }

  /**
   * Saves user in the database
   * 
   * @throws RecordExistsException
   * @return self
   */
  public function save(): self
  {
    // Check if user exists
    $this->isUserExists();

    $this->hashPassword();

    return $this->update();
  }

  /**
   * Pushes record to the database
   * 
   * @return self
   */
  private function push(): self
  {
    if (isset($this->getProperties()['files']) && $this->getProperties()['files'] !== null) {
      $picture = new Picture($this->getProperties()['files'][0]);
      $picture->setConnection($this->c);
      $picture = $picture->insert();
      $this->picture_id = $picture->id;
    }

    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      if ($key !== 'files' && $key !== 'permissions') {
        $q->set($key, $value);
      }
    }

    $q->insert();

    $id = $this->c->lastInsertId();

    $this->permissions = $this->setPermission(intval($id));
    $this->id = $id;

    return $this;
  }

  /**
   * Updates record to the database
   * 
   * @return self
   */
  private function update(): self
  {
    if (isset($this->getProperties()['files']) && $this->getProperties()['files'] !== null) {
      $picture = new Picture($this->getProperties()['files'][0]);
      $picture->setConnection($this->c);

      // Delete the existing picture
      if ($this->picture_id) {
        $picture->delete(intval($this->picture_id));
      }

      $picture = $picture->insert();
      $this->picture_id = $picture->id;
    }

    $q = $this->c->dsql();
    $q->table($this->table);

    foreach ($this->getProperties() as $key => $value) {
      if ($value !== '' && $key !== 'files' && $key !== 'permissions') {
        $q->set($key, $value);
      }
    }

    $q->where('id', $this->id);
    $q->update();

    if ($this->permissions) {
      $this->updatePermission(intval($this->id));
    }

    return $this->find();
  }

  /**
   * Deletes record in the database
   * 
   * @return self
   */
  public function delete(): self
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('id', $this->id)
      ->delete();

    // Deletes the user permission
    $user_permission = new UserPermission([
      'user_id' => $this->id
    ]);
    $user_permission->setConnection($this->c);
    $user_permission->delete();

    return $this;
  }

  /**
   * Checks if the user exists
   * 
   * @throws RecordExistsException
   */
  private function isUserExists()
  {
    if (!$this->username)
      return;

    $q = $this->c->dsql();
    $q->table($this->table)
      ->where('username', $this->username)
      ->where('id', '!=', $this->id ?? 0);

    if ($q->getRow())
      throw new RecordExistsException('User already exists.');
  }

  /**
   * Hashes the password
   */
  private function hashPassword()
  {
    if ($this->password === null || $this->password === '')
      return;

    $password_salt = bin2hex(random_bytes(16));
    $this->password = password_hash($this->password . $password_salt, PASSWORD_BCRYPT);
    $this->password_salt = $password_salt;
  }

  /**
   * Sets the status of the user
   */
  private function setStatus()
  {
    $this->status = $this->hasRecord() ? 'inactive' : 'active';
  }

  /**
   * Gets the picture associated to the user
   * 
   * @param null|int $id
   * @return null|Picture
   */
  public function getPicture(int $id = null)
  {
    if ($id === null)
      return null;

    $picture = new Picture();
    $picture->setConnection($this->c);
    return $picture->find($id);
  }

  /**
   * Gets the permission of the user
   * 
   * @param int $id
   * @return string
   */
  public function getPermission(int $id = null): string
  {
    $uid = $id ?? $this->id;

    $user_permission = new UserPermission([
      'user_id' => $uid
    ]);
    $user_permission->setConnection($this->c);

    return $user_permission->getPermission();
  }

  /**
   * Sets the permission of the user whether it is the first account or not
   * 
   * @return string
   */
  private function setPermission(int $id)
  {
    $permissions = $id !== 1 ? '' : implode(',', ['stores', 'inventory', 'quotations', 'expenses', 'accounts', 'settings']);
    if ($this->permissions) {
      $permissions = $this->permissions;
    }

    $user_permission = new UserPermission([
      'user_id' => $id,
      'permission' => $permissions
    ]);
    $user_permission->setConnection($this->c);

    $user_permission->insert();

    return $permissions;
  }

  /**
   * Updates permission of an account
   * 
   * @param int $id
   * @return string
   */
  private function updatePermission(int $id)
  {
    $user_permission = new UserPermission([
      'user_id' => $id,
      'permission' => $this->permissions
    ]);
    $user_permission->setConnection($this->c);

    $user_permission->save();

    return $this->permissions;
  }

  /**
   * Checks if there is already a record inside the database
   * 
   * @return bool
   */
  private function hasRecord(): bool
  {
    $q = $this->c->dsql();
    $q->table($this->table)
      ->limit(5);

    if (count($q->get()) > 0)
      return true;
    return false;
  }
}
