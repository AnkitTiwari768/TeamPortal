<?php

declare(strict_types=1);

namespace App\Web\CustomExport;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CatalogueExportService implements FromCollection, WithHeadings
{
    protected array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.enterprise_name',
                'ms.msme_classification',
                'ms.created_at',
                's.name as state_name'
            )

            ->join('team_snpmsme_mapping as tsm','tsm.msme_id','=','ms.id')
            ->join('team_snp_scheme as tss','tsm.snp_id','=','tss.id')
            ->leftJoin('states as s','s.id','=','ms.state_id');

        // SNP user filtering same as list
        if (hasRole('snp')) {

            $query->where(function ($q) {

                $q->where(
                    'tss.user_id',
                    (string) authId()
                );

                if (auth()->user()->parent_user_id) {

                    $q->orWhere(
                        'tss.user_id',
                        (string) auth()->user()->parent_user_id
                    );
                }
            });
        }

        // same filters as list page
        $query->where('tsm.status',1);
        $query->whereNotNull('ms.major_activity');
        $query->where(
            'ms.major_activity',
            '!=',
            'Trading'
        );

        $query->whereNull(
            'ms.is_catalogue_claim_generated'
        );

        // selected checkbox ids
        $query->whereIn(
            'ms.id',
            $this->ids
        );
        $query->orderBy(
            'ms.updated_at',
            'desc'
        );
        $data = $query->get();
        return $data->map(function($item){
            return [
                'Team Id' => $item->team_id ?? '',
                'Udyam' => $item->udyam_no ?? '',
                'Mobile' => $item->mobile ?? '',
                'Email' => $item->email ?? '',
                'State' => $item->state_name ?? '',
                'Enterprise Name' => $item->enterprise_name ?? '',
                'Enterprise Type' => $item->msme_classification ?? '',
                'Date of Registration' => $item->created_at ?? ''
            ];

        });

    }

    public function headings(): array
    {
        return [

            'Team Id',
            'Udyam',
            'Mobile',
            'Email',
            'State',
            'Enterprise Name',
            'Enterprise Type',
            'Date of Registration'

        ];
    }
}