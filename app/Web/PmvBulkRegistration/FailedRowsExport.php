<?php

namespace App\Web\PmvBulkRegistration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Traits\SubUserTrait; // ✅ Import Trait

class FailedRowsExport implements FromCollection, WithHeadings
{
    use SubUserTrait; // ✅ Use Trait

    protected $userIds;

    public function __construct($userIds = null)
    {
        // If userIds not provided, get them automatically
        $this->userIds = $userIds ?? $this->getUserIdsWithSubUsers();
    }

    public function collection()
    {
        return DB::table('pm_vishwakarma_bulk_registration_errors')
            ->select(
                'owner_name',
                'store_name',
                'pmv_id',
                'mobile',
                'email',
                'pan',
                'pin_code',
                'address',
                'product_category_id',
                'error_message'
            )
            ->whereIn('created_by', $this->userIds) // ✅ Use whereIn
            ->get();
    }

    public function headings(): array
    {
        return [
            'owner_name',
            'store_name',
            'pmv_id',
            'mobile',
            'email',
            'pan',
            'pin_code',
            'address',
            'product_category',
            'error_message'
        ];
    }
}