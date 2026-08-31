<?php

declare(strict_types=1);

namespace App\Web\MisReport;

use App\Domain\Claim\ClaimStatus;
use App\Domain\Batch\BatchStatus;
use App\Web\MisReport\FinalStatus;

use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id ?? null,
            'snp_name' => $this->snp_name ?? null,
            'application_number' => $this->application_number ?? null,
            'team_id' => $this->team_registration_id ?? null,
            'udyam_no' => $this->msme_udyam_number ?? null,
            'msme_name' => $this->msme_name ?? null,
            'msme_classification' => $this->msme_classification ?? null,
            'major_activity' => $this->major_activity ?? null,
            'target_customer' => $this->target_customer ?? null,
            //'seller_provider_id' => $this->seller_provider_id ?? null,
            'bpp_id' => $this->bpp_id ?? null,
            'status' => $this->claim_status,
            // 'claim_status' => $this->claim_status ? ucfirst(strtolower(ClaimReviewStatus::from($this->status)->name)) : null,

            'claim_status' => FinalStatus::labelFromStatus($this->claim_status),
             
            'onboarding_date' => $this->onboarding_date ?  date('d-m-Y', strtotime($this->onboarding_date)) : null,
            
            'is_bulk' => $this->is_bulk ?? null,
            'is_edited' => $this->is_edited ?? null,
            'is_revert_to_ondc' => $this->is_revert_to_ondc ?? null,
            'is_revert_to_snp' => $this->is_revert_to_snp ?? null,
            'is_reject_to_snp' => $this->is_reject_to_snp ?? null,
            'is_revert_to_nsic' => $this->is_revert_to_nsic ?? null,
            'is_deleted' => $this->is_deleted ?? null,
            'amount' => $this->amount ?? null,
            // 'approved_amount' => $this->amount ?? null,
            'approved_amount' => in_array(FinalStatus::labelFromStatus($this->claim_status),['Approved', 'Payment Completed']) ? $this->amount : null,
            'gst_charge' => $this->gst_charge ?? null,
            'gst_charge_amount' => $this->gst_charge_amount ?? null,
            'subdomain_names' => $this->subdomain_names ?? null,
            'batch_id' => $this->batch_id ?? null,
            
            'organisation_id_seller_np' => $this->organisation_id_seller_np ?? null,
            'organisation_id_lsp' => $this->organisation_id_lsp ?? null,
            'configuration' => $this->configuration ?? null,
            'ondc_seller_network_id' => $this->ondc_seller_network_id ?? null,
            'seller_credential_report' => $this->seller_credential_report ?? null,
            'catalogue_score_report' => $this->catalogue_score_report ?? null,
            'date_of_onboarding' => $this->date_of_onboarding ?? null,
            'date_of_sku_update' => $this->date_of_sku_update ?  date('d-m-Y', strtotime($this->date_of_sku_update)) : null,
            'submitted_at' => $this->submitted_at ?  date('d-m-Y', strtotime($this->submitted_at)) : null,
            // 'date_of_sku_update' => $this->date_of_sku_update ?? null,

            // 'network_transaction_id' => $this->network_transaction_id ?? null,
            // 'network_transaction_date' => $this->network_transaction_date ?? null,
            // 'transaction_status' => $this->transaction_status ?? null,
            // 'order_invoice_number' => $this->order_invoice_number ?? null,
            'order_details' => $this->order_details ?? null,
            //'other_charge' => $this->other_charge??null,
            //'other_charge_amount' => $this->other_charge_amount??null

        ];
    }
}
