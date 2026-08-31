<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PackagingClaimExport implements WithMultipleSheets
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function sheets(): array
    {
        return [
            new PackagingSelectedRowsExport($this->ids),   // Sheet 1
            new ProtocolSheet(),                 // Sheet 2
        ];
    }
}
