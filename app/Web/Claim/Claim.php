<?php

namespace App\Web\Claim;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Claim extends Model
{
    protected $table = 'claims';

    protected $fillable = [
        'id',
        'application_number',
        'claim_type_id',
        'snp_id',
        'team_registration_id',
        'msme_name',
        'msme_udyam_number',
        'msme_classification',
        'msme_category',
        'msme_transaction_type',
        //'seller_provider_id',
        'bpp_id',
        'catalogue_type',
        'onboarding_date',
        'number_of_orders',
        'no_of_transactions',
        'minimum_order_value',
        'number_of_skus',
        'total_gmv',
        'net_sales',
        'total_commission',
        'declaration_dual_claim',
        'declaration_eligibility',
        'declaration_authorization',
        'status',
        'is_sent_ondc',
        'ondc_review_status',
        'submitted_at',
        'audited_by',
        'udin_number',
		'amount',
		'gst_charge',
		'gst_charge_amount',
		'other_charge',
		'other_charge_amount',
        'remarks',
        'campaign_period',
        'campaign_duration',
        'design_type',
        'date_of_design_request',
        'date_of_design_delivery',
        'design_cost',
        'single_use_plastic',
        'description',
        'packaging_remarks',
        'msme_email',
        'msme_mobile',
        'msme_address',
        'msme_state',
        'msme_district',

        'organisation_id_seller_np',
        'organisation_id_lsp',
        'seller_np_configuration',
        'configuration',
        'ondc_seller_network_id',
        'seller_credential_report',
        'catalogue_score_report',
        'date_of_onboarding',

        'date_of_sku_update',

        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function msmeCategory(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst($value),
        );
    }

    public function msmeClassification(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst($value),
        );
    }
}
