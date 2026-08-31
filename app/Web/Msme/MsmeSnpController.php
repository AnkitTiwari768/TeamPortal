<?php

namespace App\Web\Msme;

use App\Http\Controllers\Controller;
use App\Web\Msme\MsmeSnpService;
use Maatwebsite\Excel\Facades\Excel;

class MsmeSnpController extends Controller
{
    protected MsmeSnpService $service;

    public function __construct(MsmeSnpService $service)
    {
        $this->service = $service;
    }

    // ✅ Excel Download
    public function download()
    {
        $data = $this->service->getMsmeWithSnpList();
        return Excel::download(
            new MsmeSnpExport($data),
            'msme_snp_list.xlsx'
        );
    }

    // ✅ HTML List View
    public function list()
    {
        return view('common.msme_snp_list', [
            'title' => 'MSME with SNP List'
        ]);
    }
    
    // ✅ AJAX Data for DataTable - THIS METHOD WAS MISSING
    public function getData()
    {
        try {
            $data = $this->service->getMsmeWithSnpList();
            
            $rows = [];
            $sn = 1;
            
            foreach ($data as $row) {
                if (empty($row['snps'])) {
                    $rows[] = [
                        'sn' => $sn++,
                        'msme_id' => $row['msme_id'] ?? 'N/A',
                        'mobile' => $row['mobile'] ?? 'N/A',
                        'udyam_no' => $row['udyam_no'] ?? 'N/A',
                        'enterprise_name' => $row['enterprise_name'] ?? 'N/A',
                        'snp_id' => '<span class="badge bg-secondary">No SNP Found</span>',
                        'snp_name' => '—',
                        'organization_name' => '—',
                    ];
                } else {
                    foreach ($row['snps'] as $snp) {
                        $rows[] = [
                            'sn' => $sn++,
                            'msme_id' => $row['msme_id'] ?? 'N/A',
                            'mobile' => $row['mobile'] ?? 'N/A',
                            'udyam_no' => $row['udyam_no'] ?? 'N/A',
                            'enterprise_name' => $row['enterprise_name'] ?? 'N/A',
                            'snp_id' => '<span class="badge bg-primary">' . ($snp['snp_id'] ?? 'N/A') . '</span>',
                            'snp_name' => $snp['snp_name'] ?? 'N/A',
                            'organization_name' => $snp['organization_name'] ?? 'N/A',
                        ];
                    }
                }
            }
            
            return response()->json(['data' => $rows]);
            
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }
}