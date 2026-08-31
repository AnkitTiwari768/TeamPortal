<?php

declare(strict_types=1);

namespace App\Web\Timeline;

use App\Http\Controllers\ClientController;
use Illuminate\View\View;
use Illuminate\Http\Request;


class TimelineController extends ClientController
{
  public function __construct(
    private TimelineService $service,
  ) {}

  public function index() {}

  public function getTimelineHistory($entityId)
  {
    $response = $this->service->getTimelineHistory(entityId: $entityId, entityType: request()->query('entity'));

    return $this->success($response);
  }

  public function getTimelineHistoryDetails($batchTimelineId)
  {
    $response = $this->service->getBatchTimelineHistoryDetails($batchTimelineId);
    return $this->success($response);
  }
}
