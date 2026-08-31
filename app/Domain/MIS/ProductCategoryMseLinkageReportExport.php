<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductCategoryMseLinkageReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private ProductCategoryMseLinkageReportAction $action)
    {
    }

    public function collection()
    {
        return $this->action->exportRows();
    }

    public function headings(): array
    {
        return [
            'Product Category',
            'Onboarded MSE',
            'Linked to SNP',
            'Linked to BNP',
            'Linked to LSP',
        ];
    }

    public function map($row): array
    {
        // Maatwebsite/PhpSpreadsheet's default value binder writes integer 0
        // (and float 0.0 / false) as a blank cell instead of a numeric zero -
        // casting to string keeps genuine zero counts visible in the sheet.
        return [
            $row->product_category,
            (string) $row->onboarded_mse,
            (string) $row->linked_snp,
            (string) $row->linked_bnp,
            (string) $row->linked_lsp,
        ];
    }
}
