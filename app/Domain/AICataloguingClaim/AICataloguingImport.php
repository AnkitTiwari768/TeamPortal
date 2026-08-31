<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AICataloguingImport implements ToCollection, SkipsEmptyRows, WithMultipleSheets
{
    public array $validRows = [];
    public array $invalidRows = [];
    public int $totalRows = 0;
    public int $insertedCount = 0;
    public int $invalidCount = 0;

    protected array $requiredHeaders = [
        'Udyam Number',
        'Catalogue ID',
        'Catalogue Finalization Date',
        'Catalogue Completion Status',
        'Digital Catalogue Footprint',
        'Amount Claimed with Financial Reconciliation Report'
    ];

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $headerRow = $rows->first()->toArray();
        $actualHeaders = array_map(fn($val) => trim((string)$val), $headerRow);

        // Header Validation
        for ($i = 0; $i < count($this->requiredHeaders); $i++) {
            if (($actualHeaders[$i] ?? '') !== $this->requiredHeaders[$i]) {
                throw new \Exception("Excel file column " . ($i + 1) . " should be '{$this->requiredHeaders[$i]}', but found '" . ($actualHeaders[$i] ?? '') . "'. Please download and use the provided template.");
            }
        }

        $userId = auth()->id();
        $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-ai-cataloguing')->value('id');

        // Delete old temporary data for this user
        DB::table('temporary_claims')
            ->where('claim_type_id', $claimTypeId)
            ->where('created_by', $userId)
            ->delete();

        $processedUdyams = [];
        $processedCatalogIds = [];

        // Preload MSME details
        $msmes = DB::table('team_msme_schemes')->get()->keyBy('udyam_no');

        // Preload network provider details
        $claimService = app(AICataloguingClaimService::class);
        $np = $claimService->getNetworkProviderDetailsByUserID($userId);

        foreach ($rows->skip(1) as $idx => $row) {
            $rowArray = $row->toArray();
            if (empty(array_filter($rowArray, fn($v) => !is_null($v) && $v !== ''))) {
                continue;
            }

            $this->totalRows++;

            $udyam = trim((string)($rowArray[0] ?? ''));
            $catalogId = trim((string)($rowArray[1] ?? ''));
            $finDateStr = trim((string)($rowArray[2] ?? ''));
            $compStatus = trim((string)($rowArray[3] ?? ''));
            $footprint = trim((string)($rowArray[4] ?? ''));
            $recon = trim((string)($rowArray[5] ?? ''));

            $rowErrors = [];

            if (empty($udyam)) {
                $rowErrors['udyam_number'][] = 'Udyam Number is required.';
            } else {
                if (in_array($udyam, $processedUdyams)) {
                    $rowErrors['udyam_number'][] = 'Duplicate Udyam Number found in the Excel sheet.';
                } else {
                    $processedUdyams[] = $udyam;
                    if (!$msmes->has($udyam)) {
                        $rowErrors['udyam_number'][] = 'The Udyam Number does not exist in the database.';
                    } else {
                        // Check if already claimed in claims table (exclude rejected claims)
                        $alreadyClaimed = DB::table('claims')
                            ->where('claim_type_id', $claimTypeId)
                            ->where('msme_udyam_number', $udyam)
                            ->where('status', '!=', ClaimReviewStatus::REJECTED->value)
                            ->exists();
                        if ($alreadyClaimed) {
                            $rowErrors['udyam_number'][] = 'A claim has already been filed for this Udyam Number.';
                        }
                    }
                }
            }

            if (empty($catalogId)) {
                $rowErrors['catalogue_id'][] = 'Catalogue ID is required.';
            } else {
                if (in_array($catalogId, $processedCatalogIds)) {
                    $rowErrors['catalogue_id'][] = 'Duplicate Catalogue ID found in the Excel sheet.';
                } else {
                    $processedCatalogIds[] = $catalogId;
                }
            }

            $parsedDate = null;
            if (empty($finDateStr)) {
                $rowErrors['catalogue_finalization_date'][] = 'Catalogue Finalization Date is required.';
            } else {
                try {
                    $cleanedDate = str_replace('/', '-', $finDateStr);
                    $parsedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $cleanedDate);
                    if (!$parsedDate) {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    $rowErrors['catalogue_finalization_date'][] = 'Catalogue Finalization Date is invalid (expected format dd-mm-yyyy).';
                }
            }

            if ($compStatus !== '0' && $compStatus !== '1') {
                $rowErrors['catalogue_completion_status'][] = 'Catalogue Completion Status must be 1 or 0.';
            }
            if ($footprint !== '0' && $footprint !== '1') {
                $rowErrors['digital_catalogue_footprint'][] = 'Digital Catalogue Footprint must be 1 or 0.';
            }
            if ($recon !== '0' && $recon !== '1') {
                $rowErrors['amount_claimed_with_financial_reconciliation'][] = 'Amount Claimed with Financial Reconciliation Report must be 1 or 0.';
            }

            if (!empty($rowErrors)) {
                $this->invalidRows[] = [
                    'row_number' => $idx + 1,
                    'data' => [
                        'udyam_number' => $udyam,
                        'catalogue_id' => $catalogId,
                        'catalogue_finalization_date' => $finDateStr,
                        'catalogue_completion_status' => $compStatus,
                        'digital_catalogue_footprint' => $footprint,
                        'amount_claimed_with_financial_reconciliation' => $recon
                    ],
                    'errors' => $rowErrors
                ];
                $this->invalidCount++;
            } else {
                $msmeInfo = $msmes->get($udyam);
                $this->validRows[] = [
                    'id' => uuid(),
                    'claim_type_id' => $claimTypeId,
                    'snp_id' => auth()->user()->username,
                    'bpp_id' => $msmeInfo->bpp_id ?? $np->bppid_providerid ?? null,
                    'is_bulk' => true,
                    'claim_month' => $parsedDate ? $parsedDate->format('Y-m') : now()->format('Y-m'),
                    'claim_period_start_date' => $parsedDate ? $parsedDate->format('Y-m-d') : null,
                    'claim_period_end_date' => $parsedDate ? $parsedDate->format('Y-m-d') : null,
                    'total_uploaded_records' => 1,
                    'total_valid_records' => 1,
                    'total_invalid_records' => 0,
                    'status' => ClaimReviewStatus::TEMPORARY->value,
                    'created_by' => $userId,
                    
                    // AI Cataloguing values
                    'msme_udyam_number' => $udyam,
                    'catalogue_id' => $catalogId,
                    'catalogue_finalization_date' => $parsedDate ? $parsedDate->format('Y-m-d') : null,
                    'catalogue_completion_status' => (int)$compStatus,
                    'digital_catalogue_footprint' => (int)$footprint,
                    'amount_claimed_with_financial_reconciliation' => (int)$recon,

                    // MSME lookups
                    'team_registration_id' => $msmeInfo->team_id ?? null,
                    'msme_name' => $msmeInfo->enterprise_name ?? $msmeInfo->entrepreneur_name ?? null,
                    'msme_email' => $msmeInfo->email ?? null,
                    'msme_mobile' => $msmeInfo->mobile ?? null,
                    'msme_address' => $msmeInfo->address ?? null,
                    'msme_state' => DB::table('states')->where('id', $msmeInfo->state_id)->value('name'),
                    'msme_district' => DB::table('locations')->where('id', $msmeInfo->district_id)->value('name'),
                    'msme_classification' => $msmeInfo->msme_classification ?? null,
                    'msme_category' => $msmeInfo->major_activity ?? null,
                    'msme_transaction_type' => DB::table('attribute_values')->where('id', $msmeInfo->ondc_transaction_type_id)->value('attribute_value'),
                    'team_id' => $msmeInfo->team_id ?? null,
                    
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $this->insertedCount++;
            }
        }

        if (!empty($this->validRows)) {
            DB::table('temporary_claims')->insert($this->validRows);
        }
    }
}
