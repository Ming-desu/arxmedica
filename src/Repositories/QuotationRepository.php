<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Interfaces\QuotationRepositoryInterface;
use App\Models\Address;
use App\Models\Personnel;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Recipient;

class QuotationRepository extends BaseRepository implements QuotationRepositoryInterface
{
  /**
   * Create quotation record in the database
   * 
   * @param array
   * @return Quotation
   */
  public function create(array $array): Quotation
  {
    // Creates the address for the recipient
    $address = new Address([
      'street' => $array['recipient_address_details'],
      'municipality' => $array['recipient_municipality'],
      'province' => $array['recipient_province']
    ]);
    $address->setConnection($this->c);
    $address = $address->insert();

    // Creates the representative for the quotation
    $representative = new Personnel([
      'first_name' => $array['representative_first_name'],
      'last_name' => $array['representative_last_name'],
      'contact_number' => $array['representative_contact_number']
    ]);
    $representative->setConnection($this->c);
    $representative = $representative->insert();

    // Creates the recipients for the quotation
    $recipient_main = new Recipient([
      'address_id' => $address->id,
      'first_name' => $array['main_recipient_first_name'],
      'last_name' => $array['main_recipient_last_name'],
      'position' => $array['main_recipient_position']
    ]);
    $recipient_main->setConnection($this->c);
    $recipient_main = $recipient_main->insert();

    $recipients = $recipient_main->id;

    if (
      !empty($array['secondary_recipient_first_name']) && 
      !empty($array['secondary_recipient_last_name']) &&
      !empty($array['secondary_recipient_position']))
    {
      $recipient_secondary = new Recipient([
        'address_id' => $address->id,
        'first_name' => $array['secondary_recipient_first_name'],
        'last_name' => $array['secondary_recipient_last_name'],
        'position' => $array['secondary_recipient_position']
      ]);
      $recipient_secondary->setConnection($this->c);
      $recipient_secondary = $recipient_secondary->insert();

      $recipients .= ',' . $recipient_secondary->id;
    }

    // Gets the payload in the token
    $payload = json_decode(base64_decode(explode('.', $_COOKIE['token'])[1]));

    // Creates the quotation itself
    $quotation = new Quotation([
      'representative_id' => $representative->id,
      'created_by' => $payload->sub->user->id,
      'pr_no' => $array['pr_no'],
      'date_issued' => date('Y-m-d', strtotime($array['date_issued'])),
      'project_title' => $array['project_title'],
      'project_description' => $array['project_description'],
      'recipients' => $recipients
    ]);
    $quotation->setConnection($this->c);
    $quotation = $quotation->insert();

    $quotation_items = new QuotationItem([
      'quotation_id' => $quotation->id,
      'items' => $array['quotation_items']
    ]);
    $quotation_items->setConnection($this->c);
    $quotation_items = $quotation_items->insert();

    $quotation->items = $quotation_items;

    return $quotation;
  }

  /**
   * Reads quotation records from the database
   * 
   * @param array $array
   * @return array
   */
  public function read(array $array): array
  {
    $quotation = new Quotation();
    $quotation->setConnection($this->c);
    return $quotation->read($array);
  }

  /**
   * Finds quotation based on its id
   * 
   * @param int $id
   * @return Quotation|null
   */
  public function find(int $id)
  {
    $quotation = new Quotation(['id' => $id ]);
    $quotation->setConnection($this->c);
    return $quotation->find();
  }

  /**
   * Updates an existing quotation in the database
   */
  public function update(array $array): Quotation
  {
    // Creates the address for the recipient
    $address = new Address([
      'id' => $array['address_id'],
      'street' => $array['recipient_address_details'],
      'municipality' => $array['recipient_municipality'],
      'province' => $array['recipient_province']
    ]);
    $address->setConnection($this->c);
    $address = $address->save();

    // Creates the representative for the quotation
    $representative = new Personnel([
      'id' => $array['representative_id'],
      'first_name' => $array['representative_first_name'],
      'last_name' => $array['representative_last_name'],
      'contact_number' => $array['representative_contact_number']
    ]);
    $representative->setConnection($this->c);
    $representative = $representative->save();

    // Creates the recipients for the quotation
    $recipient_main = new Recipient([
      'id' => $array['main_recipient_id'],
      'first_name' => $array['main_recipient_first_name'],
      'last_name' => $array['main_recipient_last_name'],
      'position' => $array['main_recipient_position']
    ]);
    $recipient_main->setConnection($this->c);
    $recipient_main = $recipient_main->save();

    $recipients = $recipient_main->id;

    if (
      !empty($array['secondary_recipient_first_name']) && 
      !empty($array['secondary_recipient_last_name']) &&
      !empty($array['secondary_recipient_position']))
    {
      $recipient_secondary = new Recipient([
        'address_id' => $address->id,
        'first_name' => $array['secondary_recipient_first_name'],
        'last_name' => $array['secondary_recipient_last_name'],
        'position' => $array['secondary_recipient_position']
      ]);
      $recipient_secondary->setConnection($this->c);

      if (!empty($array['secondary_recipient_id'])) {
        $recipient_secondary->id = $array['secondary_recipient_id'];
        $recipient_secondary = $recipient_secondary->save();
      }
      else {
        $recipient_secondary = $recipient_secondary->insert();
      }

      $recipients .= ',' . $recipient_secondary->id;
    }

    // Creates the quotation itself
    $quotation = new Quotation([
      'id' => $array['id'],
      'pr_no' => $array['pr_no'],
      'date_issued' => date('Y-m-d', strtotime($array['date_issued'])),
      'project_title' => $array['project_title'],
      'project_description' => $array['project_description'],
      'recipients' => $recipients
    ]);
    $quotation->setConnection($this->c);
    $quotation = $quotation->save();

    $quotation_items = new QuotationItem([
      'quotation_id' => $quotation->id,
      'items' => $array['quotation_items']
    ]);
    $quotation_items->setConnection($this->c);
    $quotation_items = $quotation_items->save();

    $quotation->items = $quotation_items;

    return $quotation;
  }

  /**
   * Deletes a quotation record from the database
   */
  public function delete(array $array): Quotation
  {
    $quotation = new Quotation($array);
    $quotation->setConnection($this->c);
    return $quotation->delete();
  }

  /**
   * Gets the quotation in print mode
   * 
   * @param int $id
   * @param array $array
   * @return Quotation|null
   */
  public function print(int $id, array $array)
  {
    $quotation = new Quotation(['id' => $id ]);
    $quotation->setConnection($this->c);
    return $quotation->print($array);
  }
}