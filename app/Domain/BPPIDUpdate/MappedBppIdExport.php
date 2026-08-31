<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MappedBppIdExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $rows)
    {
    }

    public function collection()
    {
        return collect($this->rows)->map(fn ($row) => [
            'udyam_no' => $row['udyam_no'] ?? '',
            'bpp_id'   => $row['bpp_id'] ?? '',
        ]);
    }

    public function headings(): array
    {
        return ['Udyam No', 'BPP ID'];
    }
}
