<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DemandGenerationTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CsvTemplateExport(),   // Sheet 1
            new ProtocolSheet(),                 // Sheet 2
        ];
    }
}
