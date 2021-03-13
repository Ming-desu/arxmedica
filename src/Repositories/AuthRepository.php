<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UserInactiveException;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use App\Models\UserPermission;

class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
  /**
   * Login Action, tries to login the User
   * 
   * @param User $user
   * @throws UserInactiveException
   * @throws InvalidCredentialsException
   * @return User
   */
  public function doLogin(User $user): User
  {
    $user->setConnection($this->c);
    return $user->login();
  }

  /**
   * Gets the permission of the requesting User
   * 
   * @param User $user
   * @return string
   */
  public function getPermission(User $user): string
  {
    $user->setConnection($this->c);
    return $user->getPermission();
  }  
}