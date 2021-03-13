<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\UnitRepositoryInterface;
use App\Models\Unit;

class UnitRepository extends BaseRepository implements UnitRepositoryInterface
{
  /**
   * Creates a new unit record in the database
   * 
   * @param Unit $unit
   * @throws RecordExistsException
   * @return Unit
   */
  public function create(Unit $unit): Unit
  {
    $unit->setConnection($this->c);
    return $unit->insert();
  }

  /**
   * Read units in the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $unit = new Unit();
    $unit->setConnection($this->c);
    return $unit->read($array);
  }

  /**
   * Updates an existing unit record in the database
   * 
   * @param Unit $unit
   * @throws RecordExistsException
   * @return Unit
   */
  public function update(Unit $unit): Unit
  {
    $unit->setConnection($this->c);
    return $unit->save();
  }

  /**
   * Deletes unit from the the database
   * 
   * @param Unit $unit
   * @return Unit
   */
  public function delete(Unit $unit): Unit
  {
    $unit->setConnection($this->c);
    return $unit->delete();
  }
}