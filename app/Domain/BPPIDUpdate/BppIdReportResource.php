<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Illuminate\Http\Resources\Json\JsonResource;

class BppIdReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'updated_count'   => $this['updated_count'] ?? 0,
            'mapped_count'    => $this['mapped_count'] ?? 0,
            'unmapped_count'  => $this['unmapped_count'] ?? 0,
            'report_key'      => $this['report_key'] ?? null,
            'url'             => $this['url'] ?? null,
            'summary'         => $this['summary'] ?? null,
            'rows'            => $this['rows'] ?? [],
        ];
    }
}
