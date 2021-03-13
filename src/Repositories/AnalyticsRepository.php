<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Base\BaseRepository;
use App\Interfaces\AnalyticsRepositoryInterface;
use App\Models\Canvas;

class AnalyticsRepository extends BaseRepository implements AnalyticsRepositoryInterface
{
  public function get_cost_overview(array $array): array
  {
    $canvas = new Canvas();
    $canvas->setConnection($this->c);
    return $canvas->read($array);
  }
}