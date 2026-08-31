<?php

declare(strict_types=1);

namespace App\Web\SNP;

use App\Traits\HasResponses;
use Illuminate\Http\Request;

final class MsmeSnpMappingController
{
    use HasResponses;

    /*public function __invoke(Request $request, MsmeSnpMappingAction $action)
    {
        $data = $request->validate([
            'seller_provider_id' => 'required|string',
            'msme_id' => 'required|string|exists:team_msme_schemes,id',
        ]);

        $result = $action->execute($data);

        return response()->json([
            'status' => true,
            'message' => 'MSME SNP mapping successful',
            'url' => $result['url'] ?? null,
        ], 200);
    }*/

 public function __invoke(Request $request, MsmeSnpMappingAction $action)
    {
        $data = $request->validate([
            'bpp_id' => 'required|string',
            'msme_id' => 'required|string|exists:team_msme_schemes,id',
        ]);

        $result = $action->execute($data);

        return response()->json([
            'status' => true,
            'message' => 'MSME SNP mapping successful',
            'url' => $result['url'] ?? null,
        ], 200);
    }
}
