<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
  public function create(User $user): User;
  public function read(array $array): array;
  public function update(User $user): User;
  public function find(int $id);
  public function delete(User $user): User;
}
