<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController
{
    public function exportSelected(Request $request)
    {
        $type = $request->input('type');
        $ids = $request->ids;

        if ($type === 'logistic') {
            return Excel::download(
                new LogisticClaimExport($ids ?? []),
                'logistic_selected_rows.xlsx'
            );
        }

        if ($type === 'packaging') {
            return Excel::download(
                new PackagingClaimExport($ids ?? []),
                'packaging_selected_rows.xlsx'
            );
        }

        if (empty($ids)) {
            return response()->json(['message' => 'No records selected'], 422);
        }

        return Excel::download(
            new CatalogueClaimExport($ids),
            'selected_rows.xlsx'
        );

        return Excel::download(
            new SelectedRowsExport($ids),
            'selected_rows.xlsx'
        );
    }

    public function exportSelectedForAccounts(Request $request)
    {
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json(['message' => 'No records selected'], 422);
        }

        return Excel::download(
            new AccountClaimExport($ids),
            'selected_rows.xlsx'
        );
    }

    public function downloadAccountDummyTemplate()
    {
        return Excel::download(
            new AccountClaimDummyExport(),
            'account_management_claim_dummy_template.xlsx'
        );
    }

    public function downloadPackagingDummyTemplate()
    {
        return Excel::download(
            new PackagingClaimDummyExport(),
            'packaging_claim_dummy_template.xlsx'
        );
    }
}
