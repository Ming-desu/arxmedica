<?php
declare(strict_types=1);

namespace App\Interfaces;

interface AnalyticsRepositoryInterface {
  public function get_cost_overview(array $array): array;
}