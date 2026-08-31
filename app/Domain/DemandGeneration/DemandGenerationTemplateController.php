<?php

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Facades\Excel;

class DemandGenerationTemplateController
{
    public function downloadCsvTemplate()
    {
        $fileName = 'demand_generation_template.xlsx';
        return Excel::download(new DemandGenerationTemplateExport(), $fileName);
    }
}
