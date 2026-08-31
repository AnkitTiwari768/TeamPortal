<?php

namespace App\Web\Claim;

use App\Traits\HasCommon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClaimRequest extends FormRequest
{
    use HasCommon;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {        
        $rules = [
            'id' => 'nullable|uuid',
            'msme_id' => 'nullable|uuid',
            'claim_type_id' => 'required|string|exists:claim_types,slug',
            'snp_id' => 'nullable|string|max:100',
            'team_registration_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'msme_name' => 'nullable|string|max:255',
            'msme_udyam_number' => 'nullable|string|max:50',
            'msme_classification' => [
                'nullable',
                'in:micro,small,medium,Micro,Small,Medium',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === 'medium' || $value === 'Medium') {
                        $fail('You are not eligible to file this claim');
                    }
                }
            ],
            'msme_category' => [
                'nullable',
                'in:manufacturing,services,trading,Manufacturing,Services,Trading',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === 'trading' || $value === 'Trading') {
                        $fail('You are not eligible to file this claim');
                    }
                }
            ],
            'msme_transaction_type' => 'nullable|exists:attribute_values,id',
			'target_audience' => 'nullable|string',
            //'seller_provider_id' => 'nullable|string|max:100',
            'bpp_id' => 'sometimes|required|string|max:100',
            'catalogue_type' => [
                'nullable',
                'in:ai,manual,AI,Manual',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === 'ai' || $value === 'AI') {
                        $fail('You are not eligible to file this claim');
                    }
                }
            ],
            'minimum_order_value' => [
                'nullable',
                'numeric',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $transactionType = strtolower(trim(request('target_audience') ?? ''));
                    $numberOfOrders = (int)(request('number_of_orders') ?? 0);

                    // Skip check if no orders or no value
                    if ($numberOfOrders <= 0 || $value <= 0) {
                        return;
                    }

                    $minValue = 0;
                    if ($transactionType === 'business to consumers (b2c)') {
                        $minValue = 100;
                    } elseif ($transactionType === 'business to business (b2b)') {
                        $minValue = 2500;
                    }

                    if ($value < $minValue) {
                        $fail("Minimum order value should be at 11least Rs. {$minValue} for " . strtoupper($transactionType));
                    }
                },
            ],
            'onboarding_date' => 'sometimes|required|date',
            'number_of_orders' => 'nullable|integer',
            'number_of_skus' => 'sometimes|required|integer',
            'total_gmv' => 'nullable|numeric',
            'net_sales' => 'nullable|numeric',
            'no_of_transactions' => 'nullable|numeric',
            //'minimum_order_value' => 'nullable|numeric',
            'total_commission' => 'nullable|numeric',
            'declaration_dual_claim' => 'nullable|boolean',
            'declaration_eligibility' => 'nullable|boolean',
            'declaration_authorization' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'submitted_at' => 'nullable|date',
            'audited_by' => 'nullable|string|max:255',
            'udin_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'campaign_period' => 'nullable|string',
            'campaign_duration' => 'nullable|string',
            'msme_email' => 'nullable|string|max:100',
            'msme_mobile' => 'nullable|string|max:20',
            'msme_address' => 'nullable|string|max:200',
            'msme_state' => 'nullable|string|max:36',
            'msme_district' => 'nullable|string|max:36',
            'design_type' => 'nullable|string',
            'date_of_design_request' => 'nullable|date',
            'date_of_design_delivery' => 'nullable|date',
            'design_cost' => 'nullable|numeric',
            'single_use_plastic' => 'nullable|string',
            'description' => 'nullable|string',
            'packaging_remarks' => 'nullable|string',

            'ondc_order_id1' => 'nullable|string|max:100',
            'ondc_order_id2' => 'nullable|string|max:100',

            'ondc_invoice_number1' => 'nullable|string|max:100',
            'ondc_invoice_number2' => 'nullable|string|max:100',

            'ondc_invoice_date1' => 'nullable|date',
            'ondc_invoice_date2' => 'nullable|date',

            'date_of_sku_update' => 'sometimes|required|date',


            'organisation_id_seller_np' => 'sometimes|required|string|max:100',
            'organisation_id_lsp' => 'sometimes|required|string|max:100',
            'seller_np_configuration' => 'sometimes|required|string|max:100',
            'configuration' => 'sometimes|required|string|max:100',
            'ondc_seller_network_id' => 'sometimes|required|string|max:100',
            'seller_credential_report' => 'sometimes|required|string|max:100',
            'catalogue_score_report' => 'sometimes|required|string|max:100',
            'date_of_onboarding' => 'sometimes|required|date',

            'order_details' => 'nullable|array',
            'order_details.*.network_transaction_id' => 'sometimes|required|string',
            'order_details.*.order_invoice_number'   => 'sometimes|required|string',
            //'order_details.*.network_transaction_id'   => 'nullable|distinct',
            'order_details.*.network_transaction_date' => 'sometimes|required|string',
            'order_details.*.transaction_status'       => 'sometimes|required|string',
            //'order_details.*.order_invoice_number'     => 'nullable|distinct',
            'claim_documents' => 'nullable|array',
            'claim_documents.*' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    [$documentCategoryId, $fileUploadId] = explode('|', $value);
                    if (!$documentCategoryId || !$fileUploadId) {
                        $fail("The $attribute must be in the format 'document_category_id|file_upload_id'.");
                    }

                    if (!$this->documentCategoryIsExists($documentCategoryId)) {
                        $fail("The document category with ID $documentCategoryId does not exist.");
                    }

                    if (!$this->fileUploadIsExists($fileUploadId)) {
                        $fail("The file upload with ID $fileUploadId does not exist.");
                    }
                }
            ]
        ];

        // Add unique rule only if claim type is 'claim-for-catalogue-creation'
        if ($this->claim_type_id === 'claim-for-catalogue-creation') {
            $claimTypeId = \DB::table('claim_types')
                ->where('slug', 'claim-for-catalogue-creation')
                ->value('id');
    
            $rules['team_registration_id'][] = Rule::unique('claims', 'team_registration_id')
                ->where('claim_type_id', $claimTypeId)->ignore($this->id);
        }

        // Add unique rule only if claim type is 'claim-for-packaging'
        if ($this->claim_type_id === 'claim-for-packaging') {
            $claimTypeId = \DB::table('claim_types')
                ->where('slug', 'claim-for-packaging')
                ->value('id');
        
            $rules['team_registration_id'][] = Rule::unique('claims', 'team_registration_id')
                ->where(function ($query) use ($claimTypeId) {
                    $query->where('claim_type_id', $claimTypeId)
                        ->where('campaign_period', request('campaign_period'))
                        ->where('campaign_duration', request('campaign_duration'));
                })
                ->ignore($this->id);
        }

        //dd($rules);
        return $rules;
    }

    public function messages()
    {
        return [
            'claim_type_id.required' => 'Claim type is required.',
            'claim_type_id.uuid' => 'Claim type must be a valid UUID.',
            'snp_id.required' => 'SNP ID is required.',
            'snp_id.max' => 'SNP ID must not exceed 100 characters.',
            'msme_classification.in' => 'MSME classification must be micro, small, or medium.',
            'msme_classification.*' => 'You are not eligible to file this claim',
            'msme_category.in' => 'MSME category must be manufacturing, services, or trading.',
            'number_of_orders.integer' => 'Number of orders must be an integer.',
            'no_of_transactions.integer' => 'Number of transactions must be an integer.',
            'minimum_order_value.integer' => 'Minimun order value must be an integer.',
            'number_of_skus.integer' => 'Number of SKUs must be an integer.',
            'total_gmv.numeric' => 'Total GMV must be a number.',
            'net_sales.numeric' => 'Net sales must be a number.',
            'total_commission.numeric' => 'Total commission must be a number.',
            'declaration_dual_claim.boolean' => 'Dual claim declaration must be true or false.',
            'declaration_eligibility.boolean' => 'Eligibility declaration must be true or false.',
            'declaration_authorization.boolean' => 'Authorization declaration must be true or false.',
            'status.boolean' => 'Status must be true or false.',
            'submitted_at.date' => 'Submitted at must be a valid date.',

            'order_details.array' => 'Order details must be an array.',

            'order_details.*.network_transaction_id.distinct' =>
                'Duplicate transaction ID found.',

            'order_details.*.network_transaction_id.string' =>
                'Transaction ID must be a valid string.',

            'order_details.*.network_transaction_date.string' =>
                'Transaction date must be a valid date string.',

            'order_details.*.transaction_status.string' =>
                'Transaction status must be a valid value.',

            'order_details.*.order_invoice_number.distinct' =>
                'Duplicate invoice number found.',

            'order_details.*.network_transaction_id.required'   => 'Network Transaction ID is required.',
            'order_details.*.network_transaction_date.required' => 'Network Transaction Date is required.',
            'order_details.*.transaction_status.required'       => 'Transaction Status is required.',
            'order_details.*.order_invoice_number.required'     => 'Order Invoice Number is required.',
        ];
    }

    public function toDto(): ClaimDto
    {
        return ClaimDto::fromArray($this->validated());
    }

public function withValidator($validator)
{
    $validator->after(function ($validator) {

        $rows = collect($this->order_details ?? []);

        // Invoice number duplicates
        $invoiceDuplicates = $rows
            ->pluck('order_invoice_number')
            ->filter()
            ->duplicates();

        if ($invoiceDuplicates->isNotEmpty()) {
            foreach ($rows as $index => $row) {
                if (
                    !empty($row['order_invoice_number']) &&
                    $invoiceDuplicates->contains($row['order_invoice_number'])
                ) {
                    $validator->errors()->add(
                        "order_details.$index.order_invoice_number",
                        "Duplicate Invoice Number found."
                    );
                }
            }
        }

        // Network transaction ID duplicates
        $txnDuplicates = $rows
            ->pluck('network_transaction_id')
            ->filter()
            ->duplicates();

        if ($txnDuplicates->isNotEmpty()) {
            foreach ($rows as $index => $row) {
                if (
                    !empty($row['network_transaction_id']) &&
                    $txnDuplicates->contains($row['network_transaction_id'])
                ) {
                    $validator->errors()->add(
                        "order_details.$index.network_transaction_id",
                        "Duplicate Network Transaction ID found."
                    );
                }
            }
        }
    });
}



}
