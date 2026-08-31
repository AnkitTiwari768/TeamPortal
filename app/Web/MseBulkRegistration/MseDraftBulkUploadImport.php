<?php

namespace App\Web\MseBulkRegistration;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MseDraftBulkUploadImport implements ToCollection
{
    protected $service;
    protected array $summary = [];

    public function __construct(MseDraftBulkUploadService $service)
    {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        $this->summary = $this->service->collection($rows);
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}