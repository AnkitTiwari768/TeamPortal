<?php

namespace App\Web\MseBulkRegistration;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    ShouldAutoSize
};

class MseFailedExportAll implements FromCollection, WithHeadings, ShouldAutoSize
{
    // public function collection()
    // {
    //     return DB::table('team_msme_scheme_temps')
    //         ->select(
    //             'mobile',
    //             'udyam_no',
    //             'pan_no',
    //             'gstin_no',
    //             'product_category_id',
    //             'current_state_business_id',
    //             'ondc_transaction_type_id',
    //             'attending_ondc_awareness_workshop',
    //             'turnover',
    //             'error_message'
    //         )
    //         ->get();
    // }
    public function collection()
    {
        $query = DB::table('team_msme_scheme_temps')
            ->select(               
                'udyam_no',
                 'mobile',
                 'current_state_business_id',
                'attending_ondc_awareness_workshop',
                 'turnover',
                'pan_no',
                'gstin_no',
                'product_category_id',              
                'ondc_transaction_type_id',        
                'error_message'
            );

        if (hasRole('snp') || hasRole('ia-registration')) {
            $query->where('created_by', AuthId());
        }

        return $query->get();
    }
    public function headings(): array
    {
        return [
             'Udyam No',
            'Mobile',
            'Current State Business',
            'ONDC Transaction Type',
            'Turnover',
            'PAN No',
            'GSTIN No',
            'Product Category',      
            'Workshop',        
            'Error Message'
        ];
    }
}