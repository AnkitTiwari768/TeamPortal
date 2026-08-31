<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Web\Claim\ClaimReviewStatus;
use App\Web\Claim\ClaimService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateAICataloguingClaimAction
{
    public function execute(string $userId, string $gstType, float $gstPercentage, float $cgstPercentage, float $sgstPercentage)
    {
        DB::beginTransaction();
        try {
            $claimService = app(ClaimService::class);
            $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-ai-cataloguing')->value('id');

            $temporaryClaims = DB::table('temporary_claims')
                ->where('claim_type_id', $claimTypeId)
                ->where('created_by', $userId)
                ->where('status', ClaimReviewStatus::TEMPORARY->value)
                ->get();

            if ($temporaryClaims->isEmpty()) {
                throw new \Exception('No temporary claim data found to submit.');
            }

            // Flat rate per claim is 2500, no GST calculation at import
            $inclusiveAmount = 2500.0;
            $baseAmount = $inclusiveAmount;

            $gstAmount = 0.0;
            $cgstAmount = 0.0;
            $sgstAmount = 0.0;

            foreach ($temporaryClaims as $index => $claim) {
                $appNo = $this->generateAICApplicationNumber($claim->snp_id, $index);

                DB::table('claims')->insert([
                    'id'                            => $claim->id,
                    'claim_type_id'                 => $claimTypeId,
                    'application_number'            => $appNo,
                    'snp_id'                        => $claim->snp_id,
                    'claim_month'                   => $claim->claim_month,
                    'claim_period_start_date'       => $claim->claim_period_start_date,
                    'claim_period_end_date'         => $claim->claim_period_end_date,
                    'total_uploaded_records'        => $claim->total_uploaded_records,
                    'total_valid_records'           => $claim->total_valid_records,
                    'total_invalid_records'         => $claim->total_invalid_records,
                    'total_eligible_records'        => 1,
                    'total_unique_mse_count'        => 1,
                    'bpp_id'                        => $claim->bpp_id,
                    'is_bulk'                       => $claim->is_bulk,
                    'status'                        => ClaimReviewStatus::DRAFT->value,
                    'created_by'                    => $claim->created_by,
                    'is_declaration_agreed'         => true,

                    // Amounts
                    'amount'                        => $baseAmount,
                    'gst_type'                      => $gstType,
                    'gst_percentage'                => $gstType === '1' ? $gstPercentage : 0,
                    'gst_amount'                    => $gstType === '1' ? $gstAmount : 0,
                    'cgst_percentage'               => $gstType === '2' ? $cgstPercentage : 0,
                    'cgst_amount'                   => $gstType === '2' ? $cgstAmount : 0,
                    'sgst_percentage'               => $gstType === '2' ? $sgstPercentage : 0,
                    'sgst_amount'                   => $gstType === '2' ? $sgstAmount : 0,
                    'total_claimed_amount'          => $inclusiveAmount,

                    // Specific fields
                    'msme_udyam_number'                           => $claim->msme_udyam_number,
                    'catalogue_id'                                => $claim->catalogue_id,
                    'catalogue_finalization_date'                 => $claim->catalogue_finalization_date,
                    'catalogue_completion_status'                 => $claim->catalogue_completion_status,
                    'digital_catalogue_footprint'                 => $claim->digital_catalogue_footprint,
                    'amount_claimed_with_financial_reconciliation' => $claim->amount_claimed_with_financial_reconciliation,

                    // MSME mappings
                    'team_registration_id'                        => $claim->team_registration_id,
                    'msme_name'                                   => $claim->msme_name,
                    'msme_email'                                  => $claim->msme_email,
                    'msme_mobile'                                 => $claim->msme_mobile,
                    'msme_address'                                => $claim->msme_address,
                    'msme_state'                                  => $claim->msme_state,
                    'msme_district'                               => $claim->msme_district,
                    'msme_classification'                         => $claim->msme_classification,
                    'msme_category'                               => $claim->msme_category,
                    'msme_transaction_type'                       => $claim->msme_transaction_type,
                    'team_id'                                     => $claim->team_id,

                    'created_at'                    => now(),
                    'updated_at'                    => now(),
                ]);
            }

            // Clean up temporary claims
            DB::table('temporary_claims')
                ->where('claim_type_id', $claimTypeId)
                ->where('created_by', $userId)
                ->delete();

            DB::commit();
            return [
                'status' => true,
                'message' => 'Claim submitted successfully!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function generateAICApplicationNumber(string $snpId, ?int $index = 0): string
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        if ($currentMonth >= 4) {
            $fy = $currentYear . '-' . substr((string)($currentYear + 1), -2);
        } else {
            $fy = ($currentYear - 1) . '-' . substr((string)$currentYear, -2);
        }

        $prefix = "AIC/{$fy}/{$snpId}";

        $lastNumber = DB::table('claims')
            ->where('application_number', 'like', "{$prefix}/%")
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(application_number, '/', -1) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $nextNumber = (int)$lastNumber + 1 + $index;
        return "{$prefix}/" . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
