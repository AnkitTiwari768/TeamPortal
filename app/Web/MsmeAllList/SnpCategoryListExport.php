<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SnpCategoryListExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private SnpCategoryListService $service)
    {
    }

    public function collection()
    {
        return $this->service->getExportRows();
    }

    public function headings(): array
    {
        return [
            'NP Team ID',
            'Organization Name',
            'Role',
            'Category',
            'Open MSME Count',
            'Transaction Type',
            'ONDC Domain Mapping',
            'Serviceability',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row['np_team_id'],
            $row['organization_name'],
            $row['role_name'],
            $row['category'],
            $row['open_msme_count'] ?? 0,
            $row['transaction_type'],
            $row['ondc_domain_mapping'],
            $row['serviceability'],
            $row['status_name'],
        ];
    }
}
