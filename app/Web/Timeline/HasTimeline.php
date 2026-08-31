<?php

declare(strict_types=1);

namespace App\Web\Timeline;

trait HasTimeline
{
    public function addTimeline(array $timelineData): void
    {
        app(TimelineService::class)->createTimeline($timelineData);
    }
}
