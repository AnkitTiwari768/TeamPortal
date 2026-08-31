<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Http\Request;

final class TierWiseMISReportController
{
    public function index()
    {
        $title = 'Tier Wise MIS Report';
        return view('mis.tier-wise-mis-report', compact('title'));
    }

    public function getTierWiseMISReport(Request $request,TierWiseMISReportAction $action)
    {
        try {           
            $tier = $request->tier;
            $data = $action->execute($tier);
           
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