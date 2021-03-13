<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
  public function doLogin(User $user): User;
  public function getPermission(User $user): string;
}