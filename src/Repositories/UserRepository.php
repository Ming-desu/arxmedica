<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
  /**
   * Creates a new user record in the database
   * 
   * @param User $user
   * @throws RecordExistsException
   * @return User
   */
  public function create(User $user): User
  {
    $user->setConnection($this->c);
    return $user->insert();
  }

  /**
   * Reads user records in the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $user = new User();
    $user->setConnection($this->c);
    return $user->read($array);
  }

  /**
   * Finds a user by it's id
   * 
   * @param int $id
   * @return User|null
   */
  public function find(int $id)
  {
    $user = new User();
    $user->setConnection($this->c);
    $user = $user->find($id);

    return $user;
  }

  /**
   * Updates user record in the database
   * 
   * @param User $user
   * @throws RecordExistsException
   * @return User
   */
  public function update(User $user): User
  {
    $user->setConnection($this->c);
    return $user->save();
  }

  /**
   * Deletes user record from the database
   * 
   * @param User $user
   * @return User
   */
  public function delete(User $user): User
  {
    $user->setConnection($this->c);
    return $user->delete();
  }
}
