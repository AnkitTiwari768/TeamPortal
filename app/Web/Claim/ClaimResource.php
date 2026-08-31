<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasClaimNetAmountPayable;

class ClaimResource extends JsonResource
{
     use HasClaimNetAmountPayable;
    public function toArray($request)
    {

        // if ($this->team_registration_id === 'N/A') {
        //     dd($this);
        // }

        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id ?? null,
            'snp_name' => $this->snp_name ?? null,
            'application_number' => $this->application_number ?? null,
            'team_id' => $this->team_registration_id ? $this->team_registration_id : null,
            'udyam_no' => $this->msme_udyam_number ? $this->msme_udyam_number : $this->udyam_number_2,
            'msme_name' => $this->msme_name ? $this->msme_name : $this->msme_name_2,
            'msme_classification' => $this->msme_classification ?? null,
            'major_activity' => $this->major_activity ? ucfirst($this->major_activity) : $this->major_activity_2,
            'target_customer' => $this->target_customer ?? null,
            //'seller_provider_id' => $this->seller_provider_id ?? null,
            'bpp_id' => $this->bpp_id ?? null,
            'status' => $this->status ? ucfirst(strtolower(ClaimReviewStatus::from($this->status)->name)) : null,
            'onboarding_date' => $this->onboarding_date ?  date('d-m-Y', strtotime($this->onboarding_date)) : null,

            'is_bulk' => $this->is_bulk ?? null,
            'is_edited' => $this->is_edited ?? null,
            'is_revert_to_ondc' => $this->is_revert_to_ondc ?? null,
            'is_revert_to_snp' => $this->is_revert_to_snp ?? null,
            'is_reject_to_snp' => $this->is_reject_to_snp ?? null,
            'is_revert_to_nsic' => $this->is_revert_to_nsic ?? null,
            'is_deleted' => $this->is_deleted ?? null,
            'amount' => $this->amount - ((float)$this->gst_amount + (float)$this->cgst_amount + (float)$this->sgst_amount),
            'gst_charge' => $this->gst_charge ?? null,
            'gst_charge_amount' => $this->gst_charge_amount ?? null,
            'subdomain_names' => $this->subdomain_names ? implode(',', array_unique(explode(',', $this->subdomain_names))) : null,
            'batch_id' => $this->batch_id ?? null,

            'organisation_id_seller_np' => $this->organisation_id_seller_np ?? null,
            'organisation_id_lsp' => $this->organisation_id_lsp ?? null,
            'configuration' => $this->configuration ?? null,
            'ondc_seller_network_id' => $this->ondc_seller_network_id ?? null,
            'seller_credential_report' => $this->seller_credential_report ?? null,
            'catalogue_score_report' => $this->catalogue_score_report ?? null,
            'date_of_onboarding' => $this->date_of_onboarding ?? null,
            'date_of_sku_update' => $this->date_of_sku_update ?  date('d-m-Y', strtotime($this->date_of_sku_update)) : null,
            // 'date_of_sku_update' => $this->date_of_sku_update ?? null,

            // 'network_transaction_id' => $this->network_transaction_id ?? null,
            // 'network_transaction_date' => $this->network_transaction_date ?? null,
            // 'transaction_status' => $this->transaction_status ?? null,
            // 'order_invoice_number' => $this->order_invoice_number ?? null,
            'order_details' => $this->order_details ?? null,
            //'other_charge' => $this->other_charge??null,
            //'other_charge_amount' => $this->other_charge_amount??null

            'gst_percentage' => $this->gst_percentage ?? null,
            'gst_amount' => $this->gst_amount ?? null,
            'sgst_percentage' => $this->sgst_percentage ?? null,
            'sgst_amount' => $this->sgst_amount ?? null,
            'cgst_percentage' => $this->cgst_percentage ?? null,
            'cgst_amount' => $this->cgst_amount ?? null,
            'tds_percentage' => $this->tds_percentage ?? null,
            'tds_amount' => $this->tds_amount ?? null,

            'sgst_tds_percentage' => $this->sgst_tds_percentage ?? null,
            'sgst_tds_amount' => $this->sgst_tds_amount ?? null,
            'cgst_tds_percentage' => $this->cgst_tds_percentage ?? null,
            'cgst_tds_amount' => $this->cgst_tds_amount ?? null,

             'total_claimed_amount' => $this->calculateNetAmountPayable(
                base: (float) $this->amount, 
                gst: (float) $this->gst_amount, 
                tds: (float) $this->tds_amount, 
                sgst: (float) $this->sgst_amount, 
                cgst:(float)  $this->cgst_amount,
                tds_cgst: (float) $this->cgst_tds_amount,
                tds_sgst: (float) $this->sgst_tds_amount
            ),
        ];
    }

}
