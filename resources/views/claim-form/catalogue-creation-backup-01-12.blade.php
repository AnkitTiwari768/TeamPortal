@extends('components.admin.content-layout')
@section('action-header')
<style>
	input[readonly],
textarea[readonly],
select[readonly] {
    background-color: #e9ecef !important; /* light grey (Bootstrap disabled color) */
    cursor: not-allowed;
    pointer-events: none; /* stops focus / caret */
    opacity: 1; /* keep text readable */
}
/* Default disabled state styling */
button[disabled], .disabled {
    background-color: #ccc;   /* Light grey background */
    color: #666;              /* Light grey text */
    cursor: not-allowed;      /* Change cursor to indicate it's not clickable */
    opacity: 0.6;             /* Slightly transparent */
}

/* Optional: Hover state for disabled button */
button[disabled]:hover, .disabled:hover {
    background-color: #ccc;   /* Keep it the same or darker for hover */
}

</style>
    <div class="btn-group drop-btn">

        @include('components.admin.buttons.back-button')

    </div>
@endsection
@section('card-content')
    <div class="card-body pt-1">

        <div class="card1 mb-4">
             <!-- <div class="card-header d-flex justify-content-between">
                <h5 class="m-0 box-heading heading-1">
                    @if (!empty($row['id']))
                        Update {{$title}}
                    @else
                        Add {{$title}}
                    @endif

                </h5>
            </div> -->
		
            <div class="card1 card-body">
                <form id="formId">
					<input type="hidden" class="form-control" id="claim_type_id" name="claim_type_id" value="{{$claimSlug}}">
					@if(isset($claimId))
					<input type="hidden" class="form-control" id="id" name="id" value="{{$claimId}}">
					@endif
					<div class="row g-3">
						@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
							<div class="form-group col-md-4">
								<label class="form-label required" for="snp_id">SNP ID</label>
								<input type="text" class="form-control" id="snp_id" name="snp_id" placeholder="Enter SNP ID" readonly required
								 @if(isset($claimId) && !empty($claimDetails['snp_id'])) value="{{$claimDetails['snp_id']}}" @else value="{{auth()->user()->username}}@endif">
								<span class="text-danger form-error" id="snp_id_error"></span>
							</div>
						@endif
						<div class="form-group col-md-4">
							<label class="form-label required" for="team_registration_id">MSME TEAM Registration ID</label>
							<input type="text" class="form-control" id="team_registration_id" name="team_registration_id" placeholder="Enter TEAM Registration ID" readonly required
							 @if(isset($claimId) && !empty($claimDetails['team_registration_id'])) value="{{$claimDetails['team_registration_id']}}" @else value="{{$msmeDetails->team_id}} @endif" >
							<span class="text-danger form-error" id="team_registration_id_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_udyam_number">MSME Udyam Number</label>
							<input type="text" class="form-control" id="msme_udyam_number" name="msme_udyam_number" placeholder="Enter MSME Udyam Number" readonly  required
							@if(isset($claimId) && !empty($claimDetails['msme_udyam_number'])) value="{{$claimDetails['msme_udyam_number']}}" @else  value="{{$msmeDetails->udyam_no}} @endif">
							<span class="text-danger form-error" id="msme_udyam_number_error"></span>
						</div>
						@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_name">MSME Name</label>
							<input type="text" class="form-control" id="msme_name" name="msme_name" placeholder="Enter MSME Name" readonly required
							@if(isset($claimId) && !empty($claimDetails['msme_name'])) value="{{$claimDetails['msme_name']}}" @else value="{{$msmeDetails->entrepreneur_name}} @endif">
							<span class="text-danger form-error" id="msme_name_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_classification">MSME Classification</label>
							<input type="text" class="form-control" id="msme_classification" name="msme_classification" placeholder="Enter MSME Classification" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_classification'])) value="{{$claimDetails['msme_classification']}}" @else value="{{$msmeDetails->msme_classification}} @endif">
							<span class="text-danger form-error" id="msme_classification_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_category">MSME Category</label>
							<input type="text" class="form-control" id="msme_category" name="msme_category" placeholder="Enter MSME Category" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_category'])) value="{{$claimDetails['msme_category']}}" @else value="{{$msmeDetails->major_activity}} @endif">
							<span class="text-danger form-error" id="msme_category_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_category">Catalogue Type</label>
							<input type="text" class="form-control" placeholder="By SNP" readonly>
							<input type="hidden" class="form-control" value="Manual" id="catalogue_type" name="catalogue_type">
							<span class="text-danger form-error" id="catalogue_type_error"></span>
						</div>
						
						<?php /* <div class="form-group col-md-4">
							<label class="form-label required" for="catalogue_type">Catalogue Type</label>
							<select class="form-select" id="catalogue_type" name="catalogue_type" required>
								<option value="">Select</option>
								<option value="AI" @if(isset($claimId)){{ $claimDetails['catalogue_type'] == 'AI' ? 'selected' : '' }}@endif >AI</option>
								<option value="Manual" @if(isset($claimId)){{ $claimDetails['catalogue_type'] == 'Manual' ? 'selected' : '' }}@endif>Manual</option>
							</select>
							<span class="text-danger form-error" id="catalogue_type_error"></span>
						</div> */?>

						<div class="form-group col-md-4">
							<label class="form-label required" for="onboarding_date">Date of onboarding of MSME on ONDC</label>
							<input type="text" class="form-control" id="onboarding_date" name="onboarding_date" required max="{{ date('Y-m-d') }}"
							@if(isset($claimId) && !empty($claimDetails['onboarding_date'])) value="{{$claimDetails['onboarding_date']}}" @endif>
							<span class="text-danger form-error" id="onboarding_date_error"></span>
						</div>
						@endif
						<div class="form-group col-md-4">
							<label class="form-label required" for="seller_provider_id">MSME Seller Provider ID</label>
							<input type="text" class="form-control" id="seller_provider_id" name="seller_provider_id" placeholder="Enter Seller Provider ID" readonly required
							 @if(isset($claimId) && !empty($claimDetails['seller_provider_id'])) value="{{$claimDetails['seller_provider_id']}}" @else value="{{$msmeDetails->seller_provider_id}} @endif">
							<span class="text-danger form-error" id="seller_provider_id_error"></span>
						</div>
						@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
						<div class="form-group col-md-4">
							<label class="form-label required" for="number_of_skus">Number of SKU</label>
							<a class="tooltip-ins"  href="#" data-toggle="tooltip" title="Number of SKUs Catalogued for the MSME. (The incentive will be rewarded for upto 50 SKUs.)"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							<input type="text" maxlength="6"  class="form-control integer" id="number_of_skus" name="number_of_skus" placeholder="Enter number of SKUs" required
							@if(isset($claimId) && !empty($claimDetails['number_of_skus'])) value="{{$claimDetails['number_of_skus']}}" @endif>
							<span class="text-danger form-error" id="number_of_skus_error"></span>
							
							<p style="display:none" id="totalAmountDiv">Claimed Amount <b><span id="totalAmount"></span></b> (including GST <b>{{$tax['gst_charge']}}%</b>)
							</p>
						</div>
						
						@endif

						<div class="form-group col-md-4">
							<label class="form-label required" for="target_audience">MSME @if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation') Target Audience @else Transacation Type @endif</label>
							
							<input type="text" class="form-control" id="target_audience" name="target_audience" placeholder="Enter target audience" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_transaction_type_name'])) value="{{$claimDetails['msme_transaction_type_name']}}" @else value="{{$msmeDetails->transaction_type}} @endif">
				
							<input type="hidden" class="form-control" id="msme_transaction_type" name="msme_transaction_type" 
							 @if(isset($claimId) && !empty($claimDetails['msme_transaction_type'])) value="{{$claimDetails['msme_transaction_type']}}" @else value="{{$msmeDetails->ondc_transaction_type_id}} @endif">

							<span class="text-danger form-error" id="target_audience_error"></span>
						</div>
						
						@if(isset($claimSlug) && ($claimSlug == 'claim-for-logistics-and-transportation' || $claimSlug == 'claim-for-accounts-management'))
						<input type="hidden" @if(isset($claimId) && !empty($claimDetails['snp_id'])) value="{{$claimDetails['snp_id']}}" @else value="{{auth()->user()->username}}@endif" name="snp_id">
						<div class="form-group col-md-4">
							<label class="form-label required" for="number_of_orders">Number of Orders</label>
							<input type="text" class="form-control integer" id="number_of_orders" name="number_of_orders" placeholder="Enter Number Of Orders" maxlength="6" required
							@if(isset($claimId) && !empty($claimDetails['number_of_orders'])) value="{{$claimDetails['number_of_orders']}}" @endif>
							<span class="text-danger form-error" id="number_of_orders_error"></span>
						</div>
						<div class="form-group col-md-4">
							<label class="form-label required" for="total_gmv">Total Gross Merchandise Value</label>
							<input type="text" class="form-control numeric2decimal" id="total_gmv" name="total_gmv" placeholder="Enter Gross Merchandise Value" required
							@if(isset($claimId) && !empty($claimDetails['total_gmv'])) value="{{$claimDetails['total_gmv']}}" @endif>
							<span class="text-danger form-error" id="total_gmv_error"></span>
						</div>
						<div class="form-group col-md-4">
							<label class="form-label required" for="net_sales">Net Sales for MSME on ONDC</label>
							<input type="text" class="form-control numeric2decimal" id="net_sales" name="net_sales" placeholder="Enter Net Sales for MSME on ONDC" required
							@if(isset($claimId) && !empty($claimDetails['net_sales'])) value="{{$claimDetails['net_sales']}}" @endif>
							<span class="text-danger form-error" id="net_sales_error"></span>
							<p style="display:none" id="netSalestotalAmountDiv">Claimed Amount <b><span id="netSalesTotalAmount"></span></b> (including GST <b>{{$tax['gst_charge']}}%</b>)
							</p>
						</div>
						<div class="form-group col-md-4">
							<label class="form-label required" for="no_of_transactions">No. of Transactions</label>
							<input type="text" id="no_of_transactions" class="form-control numeric2decimal" placeholder="Enter No. of Transactions" name="no_of_transactions" required 
							@if(isset($claimId) && !empty($claimDetails['no_of_transactions'])) value="{{$claimDetails['no_of_transactions']}}" @endif>
						</div>
						<div class="form-group col-md-4">
							<label class="form-label required" for="minimum_order_value">Minimun Order Value</label>
							<input type="text" id="minimum_order_value" class="form-control numeric2decimal" placeholder="Enter Total Order Value" name="minimum_order_value" required
							@if(isset($claimId) && !empty($claimDetails['minimum_order_value'])) value="{{$claimDetails['minimum_order_value']}}" @endif>
							<p style="display:none" id="minimumOrderValueDiv"><b><span id="minimumOrderValueSpan"></span></b></p>
						</div>
						<div class="form-group col-md-4">
							<label class="form-label required" for="total_commission">Total Commission Charged</label>
							<input type="text" class="form-control numeric2decimal" id="total_commission" name="total_commission" placeholder="Enter Total Commission Charged" required
							@if(isset($claimId) && !empty($claimDetails['total_commission'])) value="{{$claimDetails['total_commission']}}" @endif>
							<span class="text-danger form-error" id="total_commission_error"></span>
						</div>
						@endif
						<div class="form-group col-md-4">
							<label class="form-label required" for="ondc_order_id1">@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation') ONDC @else Network @endif Order ID 1</label>
							@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
							<a class="tooltip-ins"  href="#" data-toggle="tooltip" title="ONDC network Order IDs for the first two successful orders"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							@endif
							<input type="text" class="form-control txtnumerichypenSlashUnederscoreComma" id="ondc_order_id1" name="ondc_order_id1" placeholder="Enter ONDC Order ID 1" maxlength="40" required
							@if(isset($claimId) && !empty($claimOrders[0]->ondc_order_id)) value="{{$claimOrders[0]->ondc_order_id}}" @endif>
							<span class="text-danger form-error" id="ondc_order_id1_error"></span>
						</div>
						
						<div class="form-group col-md-4">
							<label class="form-label required" for="ondc_order_id2">@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation') ONDC @else Network @endif Order ID 2</label>
							<input type="text" class="form-control txtnumerichypenSlashUnederscoreComma" id="ondc_order_id2" name="ondc_order_id2" placeholder="Enter ONDC Order ID 2" maxlength="40" required
							@if(isset($claimId) && !empty($claimOrders[1]->ondc_order_id)) value="{{$claimOrders[1]->ondc_order_id}}" @endif>
							<span class="text-danger form-error" id="ondc_order_id2_error"></span>
						</div>
						
						<div class="form-group col-md-4">
							<label class="form-label required" for="ondc_invoice_number1">Order Invoice Number 1</label>
							<input type="text" class="form-control txtnumerichypenSlashUnederscoreComma" id="ondc_invoice_number1" name="ondc_invoice_number1" placeholder="Enter Invoice Number 1" maxlength="40" required
							@if(isset($claimId) && !empty($claimOrders[0]->invoice_number)) value="{{$claimOrders[0]->invoice_number}}" @endif>
							<input type="text" class="form-control mt-1" id="ondc_invoice_date1" name="ondc_invoice_date1" max="{{ date('Y-m-d') }}" required
							@if(isset($claimId) && !empty($claimOrders[0]->invoice_date)) value="{{$claimOrders[0]->invoice_date}}" @endif>
							<span class="text-danger form-error" id="ondc_invoice_number1_error"></span>
							<span class="text-danger form-error" id="ondc_invoice_date1_error"></span>
						</div>
						
						<div class="form-group col-md-4">
							<label class="form-label required" for="ondc_invoice_number2">Order Invoice Number 2</label>
							<input type="text" class="form-control txtnumerichypenSlashUnederscoreComma" id="ondc_invoice_number2" name="ondc_invoice_number2" placeholder="Enter Invoice Number 2" maxlength="40" required
							@if(isset($claimId) && !empty($claimOrders[1]->invoice_number)) value="{{$claimOrders[1]->invoice_number}}" @endif>
							<input type="text" class="form-control mt-1" id="ondc_invoice_date2" name="ondc_invoice_date2" max="{{ date('Y-m-d') }}" required
							@if(isset($claimId) && !empty($claimOrders[1]->invoice_date)) value="{{$claimOrders[1]->invoice_date}}" @endif>
							<span class="text-danger form-error" id="ondc_invoice_number2_error"></span>
							<span class="text-danger form-error" id="ondc_invoice_date2_error"></span>
						</div>
						
						@foreach ($claimDocuments as $key => $claimDocument)
							<div class="form-group col-md-4">
								<label class="form-label required">{{ $claimDocument->document_category_name }}</label><a class="tooltip-ins"  href="#" data-toggle="tooltip" title="{{ $claimDocument->informations }}"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
								<input type="file" name="{{ $claimDocument->document_category_slug }}"
									   id="{{ $claimDocument->document_category_slug }}"
									   document_category_id="{{ $claimDocument->document_category_id }}"
									   class="form-control upload-pdf" accept="application/pdf" @empty($claimId) required @endempty>
									   
									  

								<input type="hidden" name="claim_documents[]"
									   id="claim_documents_{{ $claimDocument->document_category_id }}" @if(isset($claimId) && !empty($claimDocument->file_upload_id)) value="{{$claimDocument->document_category_id}}|{{$claimDocument->file_upload_id}}" @endif />
								<span class="text-danger form-error" id="{{ $claimDocument->document_category_slug }}_error"></span>
								@isset($claimDocument->file_system_name)
								<a href="{{ url('storage/app/uploads/claim-documents/'.$claimDocument->file_system_name) }}" target="_blank" >
                                    {{$claimDocument->file_name}}
                                </a>
								@endisset

							</div>
							
							
						@endforeach
						<?php /*<div class="form-group col-md-8"> </div>
						<div class="form-group col-md-4 m-0 p-0 ca-cert-download"><a href="{{ url('storage/app/ca_certificate.pdf') }}" download=""> <i class="bi bi-download"></i>CA Certificate Format</a></div> */?>
						
						

						{{--@empty($claimId)--}}
							@if(isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
							<div class="form-group col-md-12 form-check mt-4">
								<input class="form-check-input" type="checkbox" name="incentive_claim" id="incentive_claim" required 
								@if(isset($claimId)){{ $claimDetails['declaration_dual_claim'] == 1 ? 'checked' : '' }}@endif >
								<input type="hidden" name="declaration_dual_claim" id="declaration_dual_claim" value="1">
								<b>No Dual- Incentive Claim :</b>
								<label class="form-check-label" for="incentive_claim">
									We affirm that we have not, and will not, claim incentives for the same set of MSEs or transactions under multiple ONDC-related programs. Specifically, if any MSE has already been onboarded through another ONDC initiative facilitated by institutions such as SIDBI, SFAC, or similar entities, we shall not claim incentives for the same MSE under this program. Furthermore, if incentives have been claimed for any transactions under a different program for a particular MSE, we shall not submit a duplicate claim for those same transactions under this program.
								</label>
							</div>

							<div class="form-group col-md-12 form-check mt-4">
								<input class="form-check-input" type="checkbox" name="eligibility" id="eligibility" required
								@if(isset($claimId)){{ $claimDetails['declaration_eligibility'] == 1 ? 'checked' : '' }}@endif>
								<input type="hidden" name="declaration_eligibility" id="declaration_eligibility" value="1">
								<b>Eligibility of MSE:</b>
								<label class="form-check-label" for="eligibility">
									We confirm that incentives are being claimed only for those MSEs who are either currently not onboarded as sellers on the ONDC platform or have never been onboarded previously. We acknowledge and accept full responsibility for verifying the onboarding status of MSEs and for any duplication or discrepancies arising in this regard.
								</label>
							</div>
							@endif
							@if(isset($claimSlug) && ($claimSlug == 'claim-for-logistics-and-transportation' || $claimSlug == 'claim-for-accounts-management' || $claimSlug == 'claim-for-catalogue-creation'))
							<div class="form-group col-md-12 form-check mt-4">
								<input class="form-check-input" type="checkbox" name="eligibility" id="eligibility" required
								@if(isset($claimId)){{ $claimDetails['declaration_authorization'] == 1 ? 'checked' : '' }}@endif>
								<input type="hidden" name="declaration_authorization" id="declaration_authorization" value="1">
								<b>Declaration:</b>
								<label class="form-check-label" for="eligibility">
									I/We hereby declare that the details furnished above are true and correct to the best of my/our knowledge and belief. If the above information is found to be incorrect, misleading, or false, appropriate action as per the law may be taken against me/us. I/We hereby authorize NSIC/ONDC to share the relevant details for the purpose of scheme administration and support, in compliance with applicable laws. I/We hereby declare that I/We have read the Privacy Policy, Terms and Conditions, Disclaimer, and Data Sharing Policy, Operating Guidelines of the TEAM Initiative, and SOPs of the TEAM Initiative, and agree to abide by them. NSIC reserves the right to change or amend the SOPs with the approval of the Ministry of MSME as per policy and procedural requirements, as and when warranted, and without giving any notice.
								</label>
							</div>
							@endif
						{{--@endempty--}}
						<div class="form-group col-md-12 mt-4">
							<button type="submit" class="btn btn-primary">Submit your Claim</button>
						</div>
					</div>
				</form>

            </div>
        </div>

    </div>

@section('js')

<script>
	var totalTax={{ $tax['totalTax']}};
	var sulg = "{{$claimSlug}}";

	$('#number_of_skus').on('change', function() {
	const number_of_skus = Number($('#number_of_skus').val()) || 0;
	const msme_classification = String($('#msme_classification').val() || '').trim().toLowerCase();
	const msme_category       = String($('#msme_category').val() || '').trim().toLowerCase();
	const target_audience     = String($('#target_audience').val() || '').trim().toLowerCase();

	calculateAmount(number_of_skus, msme_classification, msme_category, target_audience);
	});

	  function calculateAmount(number_of_skus, msme_classification, msme_category, target_audience) {
		const classifications = ['micro', 'small'];
		const categories      = ['manufacturing', 'services'];

		if (
		  classifications.includes(msme_classification) &&
		  categories.includes(msme_category) &&
		  target_audience === 'business to consumers (b2c)'
		) {
			var totalAmount=calculateIncentive(number_of_skus);
			$("#totalAmountDiv").show();
			$("#totalAmount").html('Rs '+totalAmount);
		}

		if (
		  classifications.includes(msme_classification) &&
		  categories.includes(msme_category) &&
		  target_audience === 'business to business (b2b)'
		) {
		    var totalAmount=calculateIncentive(number_of_skus);
			$("#totalAmountDiv").show();
			$("#totalAmount").html('Rs '+totalAmount);
		}
	}
	
	function calculateIncentive(skuCount) {
		// Step 1: Base incentive (₹50 per SKU, max 50 SKUs)
		let base = Math.min(skuCount, 50) * 50;

		// Step 2: Add 18% tax + 5% other charges = 23%
		let finalBeforeCap = base * totalTax;
		console.log(finalBeforeCap);

		// Step 3: Cap at ₹2,500
		let incentive = Math.min(finalBeforeCap, 2500);

		return incentive;
	}


	// GST RATE
	let GST_RATE = parseFloat({{ $tax['gst_charge'] }}) / 100;
	// account constants
	const B2C_RATE = parseFloat('{{ config('constant.B2C_RATE') }}'); // B2C MSEs: 5% of net sales
	const B2B_PER_TXN = parseFloat('{{ config('constant.B2B_PER_TXN') }}'); // 250 per transaction
	const MAX_INCENTIVE = parseFloat('{{ config('constant.MAX_INCENTIVE') }}'); // 5000 max cap

	// logistics constants
	const B2C_LOGISTICS_RATE_PER_ORDER = parseFloat('{{ config('constant.B2C_LOGISTICS_RATE_PER_ORDER') }}'); // 50 per order
	const B2B_LOGISTICS_RATE_PER_ORDER = parseFloat('{{ config('constant.B2B_LOGISTICS_RATE_PER_ORDER') }}'); // 200 per order
	const LOGISTICS_B2B_MAX_INCENTIVE = parseFloat('{{ config('constant.LOGISTICS_B2B_MAX_INCENTIVE') }}'); // 500 max cap
	const LOGISTICS_B2C_MAX_INCENTIVE = parseFloat('{{ config('constant.LOGISTICS_B2C_MAX_INCENTIVE') }}'); // 2000 max cap


	// base calculation function
	function calculateIncentiveAccAndlogi(transactionType) {
		switch (sulg) {
			case 'claim-for-accounts-management':
				let invoiceValue = Number($('#net_sales').val()) || 0;
				let numTransactions = Number($('#no_of_transactions').val()) || 0;
				calculateIncentiveAccount(transactionType, invoiceValue, numTransactions);
				break;

			case 'claim-for-logistics-and-transportation':
				let numberOfOrders = Number($('#number_of_orders').val()) || 0;
				calculateIncentiveLogistics(transactionType, numberOfOrders);
				break;

			default:
				console.warn("No valid slug found for incentive calculation.");
				break;
		}
	}

	// ACCOUNT MANAGEMENT
	function calculateIncentiveAccount(transactionType, invoiceValue, numTransactions = 0) {
		let baseIncentive = 0;

		if (transactionType === 'business to consumers (b2c)') {
			baseIncentive = invoiceValue * (B2C_RATE / 100);
		} else if (transactionType === 'business to business (b2b)') {
			baseIncentive = numTransactions * B2B_PER_TXN;
		}

		baseIncentive = Math.round(baseIncentive);

		// Apply max cap before GST
		let cappedIncentive = Math.min(baseIncentive, MAX_INCENTIVE);

		// Now apply GST reduction
		let finalClaimAmount = Math.round(cappedIncentive * (1 - GST_RATE));
		
		if (finalClaimAmount > 0) {
			$("#netSalestotalAmountDiv").show();
			$("#netSalesTotalAmount").html('Rs ' + finalClaimAmount);
		} else {
			$("#netSalestotalAmountDiv").hide();
		}
	}

	// LOGISTICS MANAGEMENT
	function calculateIncentiveLogistics(transactionType, numberOfOrders = 0) {
		let baseIncentive = 0;
		let finalClaimAmount = 0;

		if (transactionType === 'business to consumers (b2c)') {

			if (numberOfOrders <= 10) {
				baseIncentive = numberOfOrders * B2C_LOGISTICS_RATE_PER_ORDER;

				finalClaimAmount = Math.round(baseIncentive * (1 - GST_RATE));

				// final Claim Amount including gst 
				finalClaimAmount = Math.min(finalClaimAmount, LOGISTICS_B2C_MAX_INCENTIVE);

			}
			else {
				// final claim amount After applying max cap including gst 
				finalClaimAmount = Math.round(LOGISTICS_B2C_MAX_INCENTIVE * (1 - GST_RATE));
			}

		} else if (transactionType === 'business to business (b2b)') {

			if (numberOfOrders <= 10) {
				baseIncentive = numberOfOrders * B2B_LOGISTICS_RATE_PER_ORDER;
				finalClaimAmount = Math.round(baseIncentive * (1 - GST_RATE));
				finalClaimAmount = Math.min(finalClaimAmount, LOGISTICS_B2B_MAX_INCENTIVE);
			}
			else {
				finalClaimAmount = Math.round(LOGISTICS_B2B_MAX_INCENTIVE * (1 - GST_RATE));
			}
		}

		finalClaimAmount = Math.round(finalClaimAmount);
		if (finalClaimAmount > 0) {
			$("#netSalestotalAmountDiv").show();
			$("#netSalesTotalAmount").html('Rs ' + finalClaimAmount);
		} else {
			$("#netSalestotalAmountDiv").hide();
		}
	}


	$('#net_sales, #no_of_transactions').on('change', function () {
		let transactionType = String($('#target_audience').val() || '').trim().toLowerCase();
		let invoiceValue = Number($('#net_sales').val()) || 0;
		let numTransactions = Number($('#no_of_transactions').val()) || 0;
		calculateIncentiveAccAndlogi(transactionType);
	});


	$('#number_of_orders, #minimum_order_value').on('input change', function () {
		let transactionType = String($('#target_audience').val() || '').trim().toLowerCase();
		let numberOfOrders = Number($('#number_of_orders').val()) || 0;
		let totalOrderValue = Number($('#minimum_order_value').val()) || 0;
		let minValue = 0;

		if (numberOfOrders > 0 && totalOrderValue > 0) {
			if (transactionType === 'business to consumers (b2c)') {
				minValue = 100;
			} else if (transactionType === 'business to business (b2b)') {
				minValue = 2500;
			}

			if (totalOrderValue < minValue) {
				$('#minimumOrderValueDiv').show();
				$('#minimumOrderValueSpan').html(
					`Minimum order value should be at least Rs. ${minValue} for ${transactionType.toUpperCase()}`
				);
			} else {
				$('#minimumOrderValueDiv').hide();
			}
		} else {
			$('#minimumOrderValueDiv').hide();
		}
	});



    $(document).ready(function() {
        // Function to validate form
        function validateForm() {
            var allFilled = true;

            // Check for required inputs and checkboxes
            $("#formId").find('input[required], select[required], textarea[required]').each(function() {
                if ((this.type === 'checkbox' && !this.checked) || (this.type !== 'checkbox' && $.trim(this.value) === '')) {
                    allFilled = false;
                }
            });

            // Enable or disable submit button
           if (allFilled) {
                $('#formId button[type="submit"]').prop('disabled', false).removeClass('disabled');
            } else {
                $('#formId button[type="submit"]').prop('disabled', true).addClass('disabled');
            }
        }

        // Initial form validation on page load
        validateForm();

        // Re-run validation on input or change event
        $('#formId').on('input change', 'input, select, textarea', function() {
            validateForm();
        });

        // Handle form submission
        $("#formId").on("submit", function(event) {
            event.preventDefault();
            
            // Validate the form again before submitting
            validateForm();
            
            // If form is valid, submit the request
            if (!$('#formId button[type="submit"]').prop('disabled')) {
                var formData = $(this).serializeArray();
                
                @isset($id)
                    var method = 'POST';
                    var url = "{{ url('/claim-update/' . $id) }}";
                @else
                    var method = 'POST';
                    var url = "{{ url('/claims') }}";
                @endisset

                var requestData = {
                    url: url,
                    method: method,
                    body: formData,
                };

                // Assuming sendRequest is a function that handles AJAX requests
                sendRequest(requestData, "{{ url('claims') }}");
            }
        });
    });




        // $("#formId").on("submit", function(event) {
        //     event.preventDefault();
        //     var formData = $("#formId").serializeArray();
        //     @isset($id)
        //         var method = 'POST';
        //         var url = "{{ url('/claim-update/' . $id) }}";
        //     @else
        //         var method = 'POST';
        //         var url = "{{ url('/claims') }}";
        //     @endisset

        //     var requestData = {
        //         url: url,
        //         method: method,
        //         body: formData,
        //     };

        //     sendRequest(requestData, "{{ url('claims') }}");
        // });



        /*$("#team_registration_id").on("blur", function(e) {
            var teamId = $("#team_registration_id").val();
			if(teamId !=''){
				$.ajax({
					url: "{{ url('claim-msme-details') }}/" + teamId,
					type: 'GET',

					success: function(res) {
						//console.log(res);
						$("#cover-spin").hide();
						$("#msme_name").val(res.data.enterprise_name);
						$("#msme_classification").val(res.data.msme_classification);
						$("#msme_udyam_number").val(res.data.udyam_no);
						$("#msme_category").val(res.data.major_activity);
						$("#target_audience").val(res.data.transaction_type);
						$("#msme_transaction_type").val(res.data.ondc_transaction_type_id);
						$("#cover-spin").hide();
					},
					error: function(xhr, status, error) {
						$("#cover-spin").hide();
						toastr.error(xhr.responseJSON.message);
					}
				});
			}
        });*/



        $('.upload-pdf').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            var document_category_id = $(this).attr('document_category_id');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            //formData.append('application_id', $('#application_id').val());
            formData.append('document_category_id', document_category_id);
            formData.append('file', file);

            $.ajax({
                url: '{{ url('upload-claim-documents') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#claim_documents_' + document_category_id).val(response.data.claim_document);
                        //$('#upload_file').val('');
                    }
                },
                error: function(xhr, status, error) {
                    //console.error('Upload Error:', error);
                    toastr.error('File upload failed.');
                }
            });
        });

		// Apply same datepicker settings to multiple inputs
		$("#onboarding_date, #ondc_invoice_date1, #ondc_invoice_date2").datepicker({
			dateFormat: "dd-mm-yy",
			changeYear: true,
			changeMonth: true,
			maxDate: 0 // disables future dates
		});

			
    </script>
@endsection
@endsection
