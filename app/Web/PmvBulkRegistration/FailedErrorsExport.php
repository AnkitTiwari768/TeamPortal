<?php

namespace App\Web\PmvBulkRegistration;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\SubUserTrait; // ✅ Import Trait

class FailedErrorsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use SubUserTrait; // ✅ Use Trait

    protected $userIds;
    protected $errors;

    public function __construct($userIds = null)
    {
        // If userIds not provided, get them automatically
        $this->userIds = $userIds ?? $this->getUserIdsWithSubUsers();
        $this->errors = $this->getErrors();
    }

    public function collection()
    {
        return $this->errors;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Owner Name',
            'Store Name',
            'PMV ID',
            'Mobile',
            'Email',
            'PAN',
            'Pin Code',
            'Address',
            'Product Category',
            'Error Message',
            'Created At'
        ];
    }

    public function map($error): array
    {
        static $serial = 0;
        $serial++;
        
        return [
            $serial,
            $error->owner_name ?? '-',
            $error->store_name ?? '-',
            $error->pmv_id ?? '-',
            $error->mobile ?? '-',
            $error->email ?? '-',
            $error->pan ?? '-',
            $error->pin_code ?? '-',
            $error->address ?? '-',
            $error->product_category_id ?? '-',
            $error->error_message ?? '-',
            $error->created_at ? date('d-m-Y H:i:s', strtotime($error->created_at)) : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:L1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }

    private function getErrors()
    {
        return DB::table('pm_vishwakarma_bulk_registration_errors')
            ->whereIn('created_by', $this->userIds) // ✅ Use whereIn
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getErrorCount()
    {
        return $this->errors->count();
    }
}