<?php

declare(strict_types=1);

namespace App\Domain\MIS;

final class SnpWiseMISReportController
{

    public function index()
    {
        $title = 'SNP Wise MIS Report';
        return view('mis.snp-wise-mis-report', compact('title'));
    }

     public function getSnpWiseMISReport(SnpWiseMISReportAction $action)
    {
        try {
            $data = $action->execute();
            return response()->json([
                'data' => $data
            ]);
          
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
