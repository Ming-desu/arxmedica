<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Exceptions\RecordExistsException;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Address;
use App\Models\Personnel;
use App\Models\Store;

class StoreRepository extends BaseRepository implements StoreRepositoryInterface
{
  /**
   * Create store in the database
   * 
   * @param Store $store
   * @throws RecordExistsException
   * @return Store
   */
  public function create(array $array): Store
  {
    // Create the personnel
    $personnel = new Personnel([
      'first_name' => $array['first_name'],
      'last_name' => $array['last_name'],
      'contact_number' => $array['contact_number']
    ]);
    $personnel->setConnection($this->c);
    $personnel = $personnel->insert();
    
    // Create the address
    $address = new Address([
      'street' => $array['street'],
      'municipality' => $array['municipality'],
      'province' => $array['province']
    ]);
    $address->setConnection($this->c);
    $address = $address->insert();

    $store = new Store([
      'address_id' => $address->id,
      'personnel_id' => $personnel->id,
      'name' => $array['name']
    ]);
    $store->setConnection($this->c);
    return $store->insert();
  }

  /**
   * Reads store records from the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $store = new Store();
    $store->setConnection($this->c);
    return $store->read($array);
  }

  /**
   * Finds a store record by its id
   * 
   * @param int $id
   * @return Store|null
   */
  public function find(int $id)
  {
    $store = new Store();
    $store->setConnection($this->c);
    return $store->find($id);
  }

  /**
   * Update store in the database
   * 
   * @param array $array
   * @throws RecordExistsException
   * @return Store $store
   */
  public function update(array $array): Store
  {
    // Create the personnel
    $personnel = new Personnel([
      'id' => $array['personnel_id'],
      'first_name' => $array['first_name'],
      'last_name' => $array['last_name'],
      'contact_number' => $array['contact_number']
    ]);
    $personnel->setConnection($this->c);
    $personnel = $personnel->save();
    
    // Create the address
    $address = new Address([
      'id' => $array['address_id'],
      'street' => $array['street'],
      'municipality' => $array['municipality'],
      'province' => $array['province']
    ]);
    $address->setConnection($this->c);
    $address = $address->save();

    $store = new Store([
      'id' => $array['id'],
      'address_id' => $array['address_id'],
      'personnel_id' => $array['personnel_id'],
      'name' => $array['name']
    ]);
    $store->setConnection($this->c);
    return $store->save();
  }

  /**
   * Delete store in the database
   * 
   * @param array $array
   * @return Store
   */
  public function delete(array $array): Store
  {
    // Create personnel to delete
    $personnel = new Personnel(['id' => $array['personnel_id']]);
    $personnel->setConnection($this->c);
    $personnel->delete();

    // Create address to delete
    $address = new Address(['id' => $array['address_id']]);
    $address->setConnection($this->c);
    $address->delete();
    
    // Create store to delete
    $store = new Store(['id' => $array['id']]);
    $store->setConnection($this->c);
    return $store->delete();
  }
}