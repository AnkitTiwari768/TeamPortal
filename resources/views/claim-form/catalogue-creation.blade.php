@extends('components.admin.content-layout')
@section('action-header')
    <style>
        input[readonly],
        textarea[readonly],
        select[readonly] {
            background-color: #e9ecef !important;
            /* light grey (Bootstrap disabled color) */
            cursor: not-allowed;
            pointer-events: none;
            /* stops focus / caret */
            opacity: 1;
            /* keep text readable */
        }

        /* Default disabled state styling */
        button[disabled],
        .disabled {
            background-color: #ccc;
            /* Light grey background */
            color: #666;
            /* Light grey text */
            cursor: not-allowed;
            /* Change cursor to indicate it's not clickable */
            opacity: 0.6;
            /* Slightly transparent */
        }

        /* Optional: Hover state for disabled button */
        button[disabled]:hover,
        .disabled:hover {
            background-color: #ccc;
            /* Keep it the same or darker for hover */
        }

        .collapse-header {
            cursor: pointer;
            background: #f5f7fa;
            padding: 12px 16px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            border: 1px solid #e1e4e8;
        }
        .success-dynamic-box {
            cursor: pointer;
            background: #f5f7fa;
            padding: 12px 16px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            border: 1px solid #e1e4e8;
        }

        .collapse-arrow {
            transition: transform 0.3s ease;
        }

        .collapse-arrow.rotate {
            transform: rotate(180deg);
        }
        .order-details-scope td input.is-invalid,
        .order-details-scope td select.is-invalid {
            border-color: #dc3545 !important;
        }
        .order-error-text {
            display: block;
            font-size: 12px;
            color: #dc3545;
            margin-top: 4px;
        }
        
    </style>
    <?php /* <div class="btn-group drop-btn">

        @include('components.admin.buttons.back-button')

    </div> */?>
@endsection
@section('card-content')
    <div class="card-body pt-1">

        <div class="card1 mb-4">
            <!-- <div class="card-header d-flex justify-content-between">
                                                                                                                                        <h5 class="m-0 box-heading heading-1">
                                                                                                                                            @if (!empty($row['id']))
    Update {{ $title }}
@else
    Add {{ $title }}
    @endif

                                                                                                                                        </h5>
                                                                                                                                    </div> -->

            <div class="card1 card-body">
                <form id="formId" novalidate>
                    <input type="hidden" class="form-control" id="claim_type_id" name="claim_type_id"
                        value="{{ $claimSlug }}">
                    <input type="hidden" class="form-control" id="msme_id" name="msme_id"
                        value="{{ $msmeDetails->id ?? '' }}">
                    @if (isset($claimId))
                        <input type="hidden" class="form-control" id="id" name="id"
                            value="{{ $claimId }}">
                    @endif
                    <div class="row g-3">
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                            <div class="form-group col-md-4">
                                <label class="form-label required" for="snp_id">SNP ID</label>
                                <input type="text" class="form-control" id="snp_id" name="snp_id"
                                    placeholder="Enter SNP ID" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['snp_id'])) value="{{ $claimDetails['snp_id'] }}" @else value="{{ auth()->user()->username }} @endif">
                                <span class="text-danger form-error" id="snp_id_error"></span>
                            </div>
                        @endif
                        <div class="form-group col-md-4">
                            <label class="form-label required" for="team_registration_id">MSME TEAM Registration ID</label>
                            <input type="text" class="form-control" id="team_registration_id" name="team_registration_id"
                                placeholder="Enter TEAM Registration ID" readonly required
                                @if (isset($claimId) && !empty($claimDetails['team_registration_id'])) value="{{ $claimDetails['team_registration_id'] }}" @else value="{{ $msmeDetails->team_id }} @endif">
                            <span class="text-danger form-error" id="team_registration_id_error"></span>
                        </div>

                        <div class="form-group col-md-4">
                            <label class="form-label required" for="msme_udyam_number">MSME Udyam Number</label>
                            <input type="text" class="form-control" id="msme_udyam_number" name="msme_udyam_number"
                                placeholder="Enter MSME Udyam Number" readonly required
                                @if (isset($claimId) && !empty($claimDetails['msme_udyam_number'])) value="{{ $claimDetails['msme_udyam_number'] }}" @else  value="{{ $msmeDetails->udyam_no }} @endif">
                            <span class="text-danger form-error" id="msme_udyam_number_error"></span>
                        </div>
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation' || $claimSlug == 'claim-for-accounts-management')
                        <div class="form-group col-md-4">
                            <label class="form-label required" for="msme_udyam_number">Product Category of MSE</label>
                            <select id="category_id" class="form-select select2" multiple ></select>
                            <div id="aovMessage" class="mt-2 form-erro" style="display:none;"></div>
                        </div>
                        @endif
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation' || $claimSlug == 'claim-for-transportation-and-logistic' )
                            <div class="form-group col-md-4">
                                <label class="form-label required" for="msme_name">MSME Name</label>
                                <input type="text" class="form-control" id="msme_name" name="msme_name"
                                    placeholder="Enter MSME Name" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['msme_name'])) value="{{ $claimDetails['msme_name'] }}" @else value="{{ $msmeDetails->entrepreneur_name }} @endif">
                                <span class="text-danger form-error" id="msme_name_error"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="msme_classification">MSME Classification</label>
                                <input type="text" class="form-control" id="msme_classification"
                                    name="msme_classification" placeholder="Enter MSME Classification" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['msme_classification'])) value="{{ $claimDetails['msme_classification'] }}" @else value="{{ $msmeDetails->msme_classification }} @endif">
                                <span class="text-danger form-error" id="msme_classification_error"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="msme_category">MSME Category</label>
                                <input type="text" class="form-control" id="msme_category" name="msme_category"
                                    placeholder="Enter MSME Category" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['msme_category'])) value="{{ $claimDetails['msme_category'] }}" @else value="{{ $msmeDetails->major_activity }} @endif">
                                <span class="text-danger form-error" id="msme_category_error"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="target_audience">MSME @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                                        Target Audience
                                    @else
                                        Transacation Type
                                    @endif
                                </label>

                                <input type="text" class="form-control" id="target_audience" name="target_audience"
                                    placeholder="Enter target audience" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['msme_transaction_type_name'])) value="{{ $claimDetails['msme_transaction_type_name'] }}" @else value="{{ $msmeDetails->transaction_type }} @endif">

                                <input type="hidden" class="form-control" id="msme_transaction_type"
                                    name="msme_transaction_type"
                                    @if (isset($claimId) && !empty($claimDetails['msme_transaction_type'])) value="{{ $claimDetails['msme_transaction_type'] }}" @else value="{{ $msmeDetails->ondc_transaction_type_id }} @endif">

                                <span class="text-danger form-error" id="target_audience_error"></span>
                            </div>

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="seller_provider_id">MSME Seller Provider ID</label>
                                <input type="text" class="form-control" id="seller_provider_id" name="seller_provider_id"
                                    placeholder="Enter Seller Provider ID" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['seller_provider_id'])) value="{{ $claimDetails['seller_provider_id'] }}" @else value="{{ $msmeDetails->seller_provider_id }} @endif">
                                <span class="text-danger form-error" id="seller_provider_id_error"></span>
                            </div> -->
                            <div class="form-group col-md-4">
                                <label class="form-label required" for="bpp_id">Bppid Provider ID</label>
                                <input type="text" class="form-control integer" id="bpp_id" name="bpp_id"
                                    placeholder="Enter Bppid Provider ID"  required
                                    @if (isset($msmeDetails) && !empty($msmeDetails->bpp_id)) value="{{ $msmeDetails->bpp_id }}" readonly  @endif>
                                <span class="text-danger form-error" id="bpp_id_error"></span>
                            </div>

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="msme_category">Catalogue Type</label>
                                <input type="text" class="form-control" placeholder="By SNP" readonly>
                                <input type="hidden" class="form-control" value="Manual" id="catalogue_type"
                                    name="catalogue_type">
                                <span class="text-danger form-error" id="catalogue_type_error"></span>
                            </div> -->

                            <div class="form-group col-md-4">
                                @php
                                    if (in_array($claimSlug, ['claim-for-accounts-management', 'claim-for-packaging'])) {
                                        $label     = 'Organisation ID of Seller NP';
                                        $fieldId   = 'organisation_id_seller_np';
                                        $fieldName = 'organisation_id_seller_np';
                                        $fieldVal  = $claimDetails['organisation_id_seller_np'] ?? '';
                                    } elseif ($claimSlug === 'claim-for-transportation-and-logistic') {
                                        $label     = 'Organisation ID of Seller LSP';
                                        $fieldId   = 'organisation_id_seller_lsp';
                                        $fieldName = 'organisation_id_seller_lsp';
                                        $fieldVal  = $claimDetails['organisation_id_seller_lsp'] ?? '';
                                    } else {
                                        $label     = 'Organisation ID';
                                        $fieldId   = 'organisation_id';
                                        $fieldName = 'organisation_id';
                                        $fieldVal  = $claimDetails['organisation_id'] ?? '';
                                    }
                                @endphp

                                <label class="form-label required"
                                    for="organisation_id_seller_np">{{ $label }}</label>
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip" title="Enter the Entity ID (As on ONDC Participation Portal)."><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control integer" id="{{ $fieldId }}"
                                    name="{{ $fieldName }}"
                                    placeholder="Enter {{ $label }}"
                                    required
                                     value="{{ old($fieldName, $fieldVal) }}" >
                                    <span class="text-danger form-error"
                                        id="{{ $fieldName }}_error">
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required"
                                    for="seller_np_configuration">Seller NP Configuration</label>
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                    title="Enter the BPP ID"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control integer" id="seller_np_configuration"
                                    name="seller_np_configuration"
                                    placeholder="Enter Seller NP Configuration"
                                    required
                                    @if (isset($claimId) && !empty($claimDetails['seller_np_configuration'])) value="{{ $claimDetails['seller_np_configuration'] }}" @endif>
                                    <span class="text-danger form-error" id="seller_np_configuration_error"></span>

                            </div>

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required"
                                    for="seller_np_configuration">Seller NP Configuration</label>
                                <input type="text" class="form-control" id="seller_np_configuration"
                                    name="seller_np_configuration"
                                    placeholder="Enter Seller NP Configuration"
                                    required
                                    @if (isset($claimId) && !empty($claimDetails['seller_np_configuration'])) value="{{ $claimDetails['seller_np_configuration'] }}" @endif>
                            </div> -->

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_seller_network_id">ONDC Seller Network
                                    ID</label>
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                    title="Enter the bppid.providerid"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control integer" id="ondc_seller_network_id"
                                    name="ondc_seller_network_id" placeholder="Enter ONDC Seller Network ID" required
                                    @if (isset($claimId) && !empty($claimDetails['ondc_seller_network_id'])) value="{{ $claimDetails['ondc_seller_network_id'] }}" @endif>
                                    <span class="text-danger form-error" id="ondc_seller_network_id_error"></span>
                            </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label required" for="seller_credential_report">Seller Credential
                                        Report</label>
                                         <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                            title="Enter credential report ID"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <input type="text" class="form-control integer" id="seller_credential_report"
                                        name="seller_credential_report" placeholder="Enter Seller Credential Report"
                                        required
                                        @if (isset($claimId) && !empty($claimDetails['seller_credential_report'])) value="{{ $claimDetails['seller_credential_report'] }}" @endif>
                                        <span class="text-danger form-error" id="seller_credential_report_error"></span>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-label required" for="catalogue_score_report">Catalogue Score
                                        Report</label>
                                        <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                            title="Enter Catalogue Score Report ID"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <input type="text" class="form-control integer" id="catalogue_score_report"
                                        name="catalogue_score_report" placeholder="Enter Catalogue Score Report" required
                                        @if (isset($claimId) && !empty($claimDetails['catalogue_score_report'])) value="{{ $claimDetails['catalogue_score_report'] }}" @endif>
                                        <span class="text-danger form-error" id="catalogue_score_report_error"></span>
                                </div>

                         

                            <?php /* <div class="form-group col-md-4">
							<label class="form-label required" for="catalogue_type">Catalogue Type</label>
							<select class="form-select" id="catalogue_type" name="catalogue_type" required>
								<option value="">Select</option>
								<option value="AI" @if(isset($claimId)){{ $claimDetails['catalogue_type'] == 'AI' ? 'selected' : '' }}@endif >AI</option>
								<option value="Manual" @if(isset($claimId)){{ $claimDetails['catalogue_type'] == 'Manual' ? 'selected' : '' }}@endif>Manual</option>
							</select>
							<span class="text-danger form-error" id="catalogue_type_error"></span>
						</div> */
                            ?>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="onboarding_date">Date of onboarding of MSME on
                                    ONDC</label>
                                <input type="text" class="form-control" id="onboarding_date" name="onboarding_date"
                                    required max="{{ date('Y-m-d') }}"
                                    @if (isset($claimId) && !empty($claimDetails['onboarding_date'])) value="{{ $claimDetails['onboarding_date'] }}" @endif>
                                <span class="text-danger form-error" id="onboarding_date_error"></span>
                            </div>
                        @endif
                        
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                            <div class="form-group col-md-4">
                                <label class="form-label required" for="number_of_skus">Number of SKU</label>
                                <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                    title="Number of SKUs available in the catalogue"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" maxlength="6" class="form-control integer" id="number_of_skus"
                                    name="number_of_skus" placeholder="Enter number of SKUs" required
                                    @if (isset($claimId) && !empty($claimDetails['number_of_skus'])) value="{{ $claimDetails['number_of_skus'] }}" @endif>
                                <span class="text-danger form-error" id="number_of_skus_error"></span>

                                <!-- <p style="display:none" id="totalAmountDiv">Claimed Amount <b><span
                                            id="totalAmount"></span></b> (including GST <b>{{ $tax['gst_charge'] }}%</b>)
                                </p> -->
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="date_of_sku_update">Date of SKU Update</label>
                                <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                    title="Enter the SKU Count Date"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control" id="date_of_sku_update"
                                    name="date_of_sku_update" required
                                    @if (isset($claimId) && !empty($claimDetails['date_of_sku_update'])) value="{{ $claimDetails['date_of_sku_update'] }}" @endif>
                                    <span class="text-danger form-error" id="date_of_sku_update_error"></span>
                            </div>
                        @endif

                        

                        @if (isset($claimSlug) &&
                                ($claimSlug == 'claim-for-packaging' || $claimSlug == 'claim-for-accounts-management'))
                            <input type="hidden"
                                @if (isset($claimId) && !empty($claimDetails['snp_id'])) value="{{ $claimDetails['snp_id'] }}" @else value="{{ auth()->user()->username }} @endif"
                                name="snp_id">
                            <div class="form-group col-md-4">
                                <label class="form-label required" for="target_audience">MSME @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                                        Target Audience
                                    @else
                                        Transacation Type
                                    @endif
                                </label>

                                <input type="text" class="form-control" id="target_audience" name="target_audience"
                                    placeholder="Enter target audience" readonly required
                                    @if (isset($claimId) && !empty($claimDetails['msme_transaction_type_name'])) value="{{ $claimDetails['msme_transaction_type_name'] }}" @else value="{{ $msmeDetails->transaction_type }} @endif">

                                <input type="hidden" class="form-control" id="msme_transaction_type"
                                    name="msme_transaction_type"
                                    @if (isset($claimId) && !empty($claimDetails['msme_transaction_type'])) value="{{ $claimDetails['msme_transaction_type'] }}" @else value="{{ $msmeDetails->ondc_transaction_type_id }} @endif">

                                <span class="text-danger form-error" id="target_audience_error"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="bpp_id">Bppid Provider ID</label>
                                <input type="text" class="form-control integer" id="bpp_id" name="bpp_id"
                                    placeholder="Enter Bppid Provider ID"  required
                                    @if (isset($msmeDetails) && !empty($msmeDetails->bpp_id)) value="{{ $msmeDetails->bpp_id }}" readonly  @endif>
                                <span class="text-danger form-error" id="bpp_id_error"></span>
                            </div>    
                            <div class="form-group col-md-4">
                                @php
                                    if (in_array($claimSlug, ['claim-for-accounts-management', 'claim-for-packaging'])) {
                                        $label     = 'Organisation ID of Seller NP';
                                        $fieldId   = 'organisation_id_seller_np';
                                        $fieldName = 'organisation_id_seller_np';
                                        $fieldVal  = $claimDetails['organisation_id_seller_np'] ?? '';
                                    } elseif ($claimSlug === 'logistics-management') {
                                        $label     = 'Organisation ID of Seller LSP';
                                        $fieldId   = 'organisation_id_seller_lsp';
                                        $fieldName = 'organisation_id_seller_lsp';
                                        $fieldVal  = $claimDetails['organisation_id_seller_lsp'] ?? '';
                                    } else {
                                        $label     = 'Organisation ID';
                                        $fieldId   = 'organisation_id';
                                        $fieldName = 'organisation_id';
                                        $fieldVal  = $claimDetails['organisation_id'] ?? '';
                                    }
                                @endphp

                                <label class="form-label required"
                                    for="organisation_id_seller_np">{{ $label }}</label>
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip" title="Enter the Entity ID (As on ONDC Participation Portal)."><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control integer" id="{{ $fieldId }}"
                                    name="{{ $fieldName }}"
                                    placeholder="Enter {{ $label }}"
                                    required
                                     value="{{ old($fieldName, $fieldVal) }}" >
                                    <span class="text-danger form-error"
                                        id="{{ $fieldName }}_error">
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_seller_network_id">ONDC Seller Network
                                    ID</label>
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                    title="Enter the bppid.providerid"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <input type="text" class="form-control integer" id="ondc_seller_network_id"
                                    name="ondc_seller_network_id" placeholder="Enter ONDC Seller Network ID" required
                                    @if (isset($claimId) && !empty($claimDetails['ondc_seller_network_id'])) value="{{ $claimDetails['ondc_seller_network_id'] }}" @endif>
                                    <span class="text-danger form-error"  id="ondc_seller_network_id_error">
                            </div>
                            @if ($claimSlug == 'claim-for-packaging')
                                <div class="form-group col-md-4">
                                    <label class="form-label required" for="number_of_orders">No of Orders </label>
                                        <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                        title="Enter the no of orders"><i
                                            class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <input type="text" class="form-control integer" id="number_of_orders"
                                        name="number_of_orders" placeholder="Enter No of Orders" required
                                        @if (isset($claimId) && !empty($claimDetails['number_of_orders'])) value="{{ $claimDetails['number_of_orders'] }}" @endif>
                                        <span class="text-danger form-error"  id="number_of_orders_error">
                                </div>
                            @endif
                            @if ($claimSlug == 'claim-for-accounts-management' || $claimSlug == 'claim-for-packaging')
                                <div class="form-group col-md-4">
                                    <label class="form-label required" for="seller_credential_report">Seller Credential
                                        Report</label>
                                        <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                            title="Enter credential report ID"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <input type="text" class="form-control integer" id="seller_credential_report"
                                        name="seller_credential_report" placeholder="Enter Seller Credential Report"
                                        required
                                        @if (isset($claimId) && !empty($claimDetails['seller_credential_report'])) value="{{ $claimDetails['seller_credential_report'] }}" @endif>
                                        <span class="text-danger form-error"  id="seller_credential_report_error">
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-label required" for="catalogue_score_report">Catalogue Score
                                        Report</label>
                                        <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                            title="Enter Catalogue Score Report ID"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <input type="text" class="form-control integer" id="catalogue_score_report"
                                        name="catalogue_score_report" placeholder="Enter Catalogue Score Report" required
                                        @if (isset($claimId) && !empty($claimDetails['catalogue_score_report'])) value="{{ $claimDetails['catalogue_score_report'] }}" @endif>
                                        <span class="text-danger form-error"  id="catalogue_score_report_error">
                                </div>
                            @endif

                            <div class="form-group col-md-4">
                                <label class="form-label required" for="date_of_onboarding">Date of onboarding of MSE on
                                    ONDC</label>
                                <input type="text" class="form-control" id="date_of_onboarding"
                                    name="date_of_onboarding" required
                                    @if (isset($claimId) && !empty($claimDetails['date_of_onboarding'])) value="{{ $claimDetails['date_of_onboarding'] }}" @endif>
                                    <span class="text-danger form-error"  id="date_of_onboarding_error">
                            </div>


                            
                        @endif
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation' || $claimSlug == 'claim-for-accounts-management' || $claimSlug == 'claim-for-packaging' )
                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_order_id1">
                                    @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                                        ONDC
                                    @else
                                        Network
                                    @endif Order ID 1
                                </label>
                                @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                        title="ONDC network Order IDs for the first two successful orders"><i
                                            class="fa fa-question-circle" aria-hidden="true"></i></a>
                                @endif
                                <input type="text" class="form-control txtnumerichypenSlashUnederscoreComma"
                                    id="ondc_order_id1" name="ondc_order_id1" placeholder="Enter ONDC Order ID 1"
                                    maxlength="40" required
                                    @if (isset($claimId) && !empty($claimOrders[0]->ondc_order_id)) value="{{ $claimOrders[0]->ondc_order_id }}" @endif>
                                <span class="text-danger form-error" id="ondc_order_id1_error"></span>
                            </div> -->

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_order_id2">
                                    @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                                        ONDC
                                    @else
                                        Network
                                    @endif Order ID 2
                                </label>
                                <input type="text" class="form-control txtnumerichypenSlashUnederscoreComma"
                                    id="ondc_order_id2" name="ondc_order_id2" placeholder="Enter ONDC Order ID 2"
                                    maxlength="40" required
                                    @if (isset($claimId) && !empty($claimOrders[1]->ondc_order_id)) value="{{ $claimOrders[1]->ondc_order_id }}" @endif>
                                <span class="text-danger form-error" id="ondc_order_id2_error"></span>
                            </div> -->

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_invoice_number1">Order Invoice Number
                                    1</label>
                                <input type="text" class="form-control txtnumerichypenSlashUnederscoreComma"
                                    id="ondc_invoice_number1" name="ondc_invoice_number1"
                                    placeholder="Enter Invoice Number 1" maxlength="40" required
                                    @if (isset($claimId) && !empty($claimOrders[0]->invoice_number)) value="{{ $claimOrders[0]->invoice_number }}" @endif>
                                <input type="text" class="form-control mt-1" id="ondc_invoice_date1"
                                    name="ondc_invoice_date1" max="{{ date('Y-m-d') }}" required
                                    @if (isset($claimId) && !empty($claimOrders[0]->invoice_date)) value="{{ $claimOrders[0]->invoice_date }}" @endif>
                                <span class="text-danger form-error" id="ondc_invoice_number1_error"></span>
                                <span class="text-danger form-error" id="ondc_invoice_date1_error"></span>
                            </div> -->

                            <!-- <div class="form-group col-md-4">
                                <label class="form-label required" for="ondc_invoice_number2">Order Invoice Number
                                    2</label>
                                <input type="text" class="form-control txtnumerichypenSlashUnederscoreComma"
                                    id="ondc_invoice_number2" name="ondc_invoice_number2"
                                    placeholder="Enter Invoice Number 2" maxlength="40" required
                                    @if (isset($claimId) && !empty($claimOrders[1]->invoice_number)) value="{{ $claimOrders[1]->invoice_number }}" @endif>
                                <input type="text" class="form-control mt-1" id="ondc_invoice_date2"
                                    name="ondc_invoice_date2" max="{{ date('Y-m-d') }}" required
                                    @if (isset($claimId) && !empty($claimOrders[1]->invoice_date)) value="{{ $claimOrders[1]->invoice_date }}" @endif>
                                <span class="text-danger form-error" id="ondc_invoice_number2_error"></span>
                                <span class="text-danger form-error" id="ondc_invoice_date2_error"></span>
                            </div> -->
                            
                            @foreach ($claimDocuments as $key => $claimDocument)
                                <div class="form-group col-md-4">
                                    <label
                                        class="form-label required">{{ $claimDocument->document_category_name }}</label><a
                                        class="tooltip-ins" href="#" data-toggle="tooltip"
                                        title="{{ $claimDocument->informations }}"><i class="fa fa-question-circle"
                                            aria-hidden="true"></i></a>
                                    <input type="file" name="{{ $claimDocument->document_category_slug }}"
                                        id="{{ $claimDocument->document_category_slug }}"
                                        document_category_id="{{ $claimDocument->document_category_id }}"
                                        class="form-control upload-pdf" accept="application/pdf"
                                        @empty($claimId) required @endempty>



                                    <input type="hidden" name="claim_documents[]"
                                        id="claim_documents_{{ $claimDocument->document_category_id }}"
                                        @if (isset($claimId) && !empty($claimDocument->file_upload_id)) value="{{ $claimDocument->document_category_id }}|{{ $claimDocument->file_upload_id }}" @endif />
                                    <span class="text-danger form-error"
                                        id="{{ $claimDocument->document_category_slug }}_error"></span>
                                    @isset($claimDocument->file_system_name)
                                        <a href="{{ url('storage/app/uploads/claim-documents/' . $claimDocument->file_system_name) }}"
                                            target="_blank">
                                            {{ $claimDocument->file_name }}
                                        </a>
                                    @endisset

                                </div>
                            @endforeach
                        @endif

                            <div class="collapse-header">
                                Order Details for each claim txn *
                            </div>

                            <div class="mt-3">

                                <div class="row" id="order_details_table">
                                    <div class="col-lg-12">
                                        <table class="table table-themed table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Network Transaction ID</th>
                                                    <th>Transaction Date</th>
                                                    <th>Transaction Completed</th>
                                                    <th>Invoice Number</th>
                                                    <th>Add/Remove</th>
                                                </tr>
                                            </thead>

                                            <tbody id="orderDetailsContainer" class="orderDetailsContainer order-details-scope">
                                                <!-- Mustache will add rows here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        
                        <?php /*<div class="form-group col-md-8"> </div>
						<div class="form-group col-md-4 m-0 p-0 ca-cert-download"><a href="{{ url('storage/app/ca_certificate.pdf') }}" download=""> <i class="bi bi-download"></i>CA Certificate Format</a></div> */
                        ?>



                        {{-- @empty($claimId) --}}
                        @if (isset($claimSlug) && $claimSlug == 'claim-for-catalogue-creation')
                            <div class="form-group col-md-12 form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="incentive_claim"
                                    id="incentive_claim" required
                                    @if (isset($claimId)) {{ $claimDetails['declaration_dual_claim'] == 1 ? 'checked' : '' }} @endif>
                                <input type="hidden" name="declaration_dual_claim" id="declaration_dual_claim"
                                    value="1">
                                <b>No Dual- Incentive Claim :</b>
                                <label class="form-check-label" for="incentive_claim">
                                    We affirm that we have not, and will not, claim incentives for the same set of MSEs or
                                    transactions under multiple ONDC-related programs. Specifically, if any MSE has already
                                    been onboarded through another ONDC initiative facilitated by institutions such as
                                    SIDBI, SFAC, or similar entities, we shall not claim incentives for the same MSE under
                                    this program. Furthermore, if incentives have been claimed for any transactions under a
                                    different program for a particular MSE, we shall not submit a duplicate claim for those
                                    same transactions under this program.
                                </label>
                            </div>

                            <div class="form-group col-md-12 form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="eligibility" id="eligibility"
                                    required
                                    @if (isset($claimId)) {{ $claimDetails['declaration_eligibility'] == 1 ? 'checked' : '' }} @endif>
                                <input type="hidden" name="declaration_eligibility" id="declaration_eligibility"
                                    value="1">
                                <b>Eligibility of MSE:</b>
                                <label class="form-check-label" for="eligibility">
                                    We confirm that incentives are being claimed only for those MSEs who are either
                                    currently not onboarded as sellers on the ONDC platform or have never been onboarded
                                    previously. We acknowledge and accept full responsibility for verifying the onboarding
                                    status of MSEs and for any duplication or discrepancies arising in this regard.
                                </label>
                            </div>
                        @endif
                        @if (isset($claimSlug) &&
                                ($claimSlug == 'claim-for-transportation-and-logistic' ||
                                    $claimSlug == 'claim-for-accounts-management' ||
                                    $claimSlug == 'claim-for-catalogue-creation'))
                            <div class="form-group col-md-12 form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="eligibility" id="eligibility"
                                    required
                                    @if (isset($claimId)) {{ $claimDetails['declaration_authorization'] == 1 ? 'checked' : '' }} @endif>
                                <input type="hidden" name="declaration_authorization" id="declaration_authorization"
                                    value="1">
                                <b>Declaration:</b>
                                <label class="form-check-label" for="eligibility">
                                    I/We hereby declare that the details furnished above are true and correct to the best of
                                    my/our knowledge and belief. If the above information is found to be incorrect,
                                    misleading, or false, appropriate action as per the law may be taken against me/us. I/We
                                    hereby authorize NSIC/ONDC to share the relevant details for the purpose of scheme
                                    administration and support, in compliance with applicable laws. I/We hereby declare that
                                    I/We have read the Privacy Policy, Terms and Conditions, Disclaimer, and Data Sharing
                                    Policy, Operating Guidelines of the TEAM Initiative, and SOPs of the TEAM Initiative,
                                    and agree to abide by them. NSIC reserves the right to change or amend the SOPs with the
                                    approval of the Ministry of MSME as per policy and procedural requirements, as and when
                                    warranted, and without giving any notice.
                                </label>
                            </div>
                        @endif
                        {{-- @endempty --}}
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
        var ADD_ICON = "{{ url('assets/img-new/add-btn.svg') }}";
        var DELETE_ICON = "{{ url('assets/img-new/dlt-btn.svg') }}";
    </script>
<script id="add_more_order_template" type="x-tmpl-mustache">

    <tr class="order_row" id="order_row_@{{randomId}}" data-row-index="@{{index}}">
         <input type="hidden" class="order_row_id" value="@{{response.id}}" required>
        <td class="order_index"></td>

        <td>
            <input type="text"
                name="order_details[@{{randomId}}][network_transaction_id]"
                class="form-control validate-required"
                value="@{{response.network_transaction_id}}" required>
                <span class="text-danger form-error"
      id="network_transaction_id_@{{randomId}}_error"></span>
        </td>

        <td>
            <input type="text"
                name="order_details[@{{randomId}}][network_transaction_date]"
                class="form-control order_datepicker network_transaction_date validate-required"
                value="@{{response.network_transaction_date}}" required>
               <span id="network_transaction_date_@{{randomId}}_error"
      class="text-danger form-error"></span>
        </td>

        <td>
            <select name="order_details[@{{randomId}}][transaction_status]" class="form-control validate-required" required>
                <option value="">Select</option>
                <option value="0" @{{#isYes}}selected@{{/isYes}}>Yes</option>
                <option value="1" @{{#isNo}}selected@{{/isNo}}>No</option>
            </select>
            <span id="transaction_status_@{{randomId}}_error"
      class="text-danger form-error"></span>
        </td>

        <td>
            <input type="text"
                name="order_details[@{{randomId}}][order_invoice_number]"
                class="form-control validate-required"
                value="@{{response.order_invoice_number}}" required>
                 <span id="order_invoice_number_@{{randomId}}_error"
      class="text-danger form-error"></span>
        </td>

        <td>
            <a class="add_more_order" href="javascript:void(0)"><img src="@{{addIcon}}"></a>
            <div class="delete_order_button mt-2" style="display:none;">
                <a class="remove_order_row" data-id="@{{randomId}}" href="javascript:void(0)"><img src="@{{deleteIcon}}"></a>
            </div>
        </td>

    </tr>

</script>


    <script>
        var totalTax = {{ $tax['totalTax'] }};
        var slug = "{{ $claimSlug }}";
        var id = "{{ $msmeId }}";
        var existingCategoryId = @json(json_decode($msmeDetails->product_category_id ?? '[]', true));
        //console.log(slug);

        $('#number_of_skus').on('change', function() {
            const number_of_skus = Number($('#number_of_skus').val()) || 0;
            const msme_classification = String($('#msme_classification').val() || '').trim().toLowerCase();
            const msme_category = String($('#msme_category').val() || '').trim().toLowerCase();
            const target_audience = String($('#target_audience').val() || '').trim().toLowerCase();

            calculateAmount(number_of_skus, msme_classification, msme_category, target_audience);
        });

        function calculateAmount(number_of_skus, msme_classification, msme_category, target_audience) {
            const classifications = ['micro', 'small'];
            const categories = ['manufacturing', 'services'];

            if (
                classifications.includes(msme_classification) &&
                categories.includes(msme_category) &&
                target_audience === 'business to consumers (b2c)'
            ) {
                var totalAmount = calculateIncentive(number_of_skus);
                $("#totalAmountDiv").show();
                $("#totalAmount").html('Rs ' + totalAmount);
            }

            if (
                classifications.includes(msme_classification) &&
                categories.includes(msme_category) &&
                target_audience === 'business to business (b2b)'
            ) {
                var totalAmount = calculateIncentive(number_of_skus);
                $("#totalAmountDiv").show();
                $("#totalAmount").html('Rs ' + totalAmount);
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
        const B2C_LOGISTICS_RATE_PER_ORDER = parseFloat(
            '{{ config('constant.B2C_LOGISTICS_RATE_PER_ORDER') }}'); // 50 per order
        const B2B_LOGISTICS_RATE_PER_ORDER = parseFloat(
            '{{ config('constant.B2B_LOGISTICS_RATE_PER_ORDER') }}'); // 200 per order
        const LOGISTICS_B2B_MAX_INCENTIVE = parseFloat(
            '{{ config('constant.LOGISTICS_B2B_MAX_INCENTIVE') }}'); // 500 max cap
        const LOGISTICS_B2C_MAX_INCENTIVE = parseFloat(
            '{{ config('constant.LOGISTICS_B2C_MAX_INCENTIVE') }}'); // 2000 max cap


        // base calculation function
        function calculateIncentiveAccAndlogi(transactionType) {
            switch (slug) {
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

                } else {
                    // final claim amount After applying max cap including gst 
                    finalClaimAmount = Math.round(LOGISTICS_B2C_MAX_INCENTIVE * (1 - GST_RATE));
                }

            } else if (transactionType === 'business to business (b2b)') {

                if (numberOfOrders <= 10) {
                    baseIncentive = numberOfOrders * B2B_LOGISTICS_RATE_PER_ORDER;
                    finalClaimAmount = Math.round(baseIncentive * (1 - GST_RATE));
                    finalClaimAmount = Math.min(finalClaimAmount, LOGISTICS_B2B_MAX_INCENTIVE);
                } else {
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


        $('#net_sales, #no_of_transactions').on('change', function() {
            let transactionType = String($('#target_audience').val() || '').trim().toLowerCase();
            let invoiceValue = Number($('#net_sales').val()) || 0;
            let numTransactions = Number($('#no_of_transactions').val()) || 0;
            calculateIncentiveAccAndlogi(transactionType);
        });


        $('#number_of_orders, #minimum_order_value').on('input change', function() {
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



        /*$(document).ready(function() {

            //make category dropdawn 
            makeCategoryDropdown('category_id', "{{ url('/get-product-categories-public') }}");

            // Function to validate form
            function validateForm() {
                var allFilled = true;

                // Check for required inputs and checkboxes
                $("#formId").find('input[required], select[required], textarea[required]').each(function() {
                    if ((this.type === 'checkbox' && !this.checked) || (this.type !== 'checkbox' && $.trim(
                            this.value) === '')) {
                        allFilled = false;
                    }
                });

                // Validate dynamic Mustache fields
                $(".validate-required").each(function () {
                    if ($.trim($(this).val()) === "") {
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
        });*/

        $(document).ready(function() {

            makeCategoryDropdown('category_id', "{{ url('/get-product-categories-public') }}",true);
            //$('input, select, textarea').removeAttr('required');

            function validateForm(focusOnError = false) {
                let allFilled = true;
                let firstInvalid = null;

                $("#formId")
                    .find('input[required], select[required], textarea[required], .validate-required')
                    .each(function () {

                        if (
                            $(this).attr('type') === 'hidden' ||
                            $(this).is(':disabled') ||
                            !$(this).is(':visible')
                        ) {
                            return;
                        }

                        if (
                            (this.type === 'checkbox' && !this.checked) ||
                            (this.type !== 'checkbox' && $.trim($(this).val()) === '')
                        ) {
                            allFilled = false;
                            if (!firstInvalid) {
                                firstInvalid = $(this);
                            }
                        }
                    });

                if (!allFilled && focusOnError && firstInvalid) {
                    const top = firstInvalid.offset().top - 120;

                    window.scrollTo({
                        top: top,
                        behavior: 'smooth'
                    });

                    setTimeout(() => {
                        firstInvalid.focus();
                    }, 300);
                }

                return allFilled;
            }

            validateForm();

            $('#formId').on('input change', 'input, select, textarea', function() {
                validateForm();
            });
            var redirectUrl = '';

            if (slug) {
                switch (slug) {

                    case 'claim-for-catalogue-creation':
                        redirectUrl = "{{ url('/claims')}}"; 
                        break;

                    case 'claim-for-accounts-management':
                        redirectUrl = "{{url('/accounts-and-management-claim')}}";
                        break;

                    case 'claim-for-packaging':
                        redirectUrl = "{{url('/packaging-support-claim')}}";
                        break;

                    default:
                        redirectUrl = "{{url('/claims')}}"; 
                        break;
                }
            }

            $("#formId").on("submit", function(event) {
                event.preventDefault();
                if (!validateForm(true)) {
                    return false;
                }
                if (!$('#formId button[type="submit"]').prop('disabled')) {
                    var formData = $(this).serializeArray();

                    @isset($claimId)
                        var method = 'POST';
                        var url = "{{ url('/claim-update/' . $claimId) }}";
                    @else
                        var method = 'POST';
                        var url = "{{ url('/claims') }}";
                    @endisset

                    var requestData = {
                        url: url,
                        method: method,
                        body: formData,
                    };
                    customSendRequest(requestData, redirectUrl);
                }
            });
        });

        function customSendRequest(requestData, redirectTo = '') {

            disableSubmit();
            $('#ajax-loader').show();

            $.ajax({
                url: requestData.url,
                method: requestData.method,
                dataType: 'json',
                data: requestData.body,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function (responseData) {

                $('#ajax-loader').hide();
                enableSubmit();

                if (responseData.status === true) {
                    // /toastr.success(responseData.message);
                        if (responseData.message && responseData.message.trim() !== '') {
                            toastr.success(responseData.message);
                        }

                        if (responseData.message_first && responseData.message_first.trim() !== '') {
                            alert(responseData.message_first);
                        }

                        if (redirectTo !== '') {
                            redirect(redirectTo);
                        }
                    return;
                }

                if (responseData.status === false && (!responseData.errors || responseData.errors.length === 0)) {
                    handleBusinessError(responseData);
                    return;
                }

                if (responseData.errors) {
                    renderLaravelErrors(responseData.errors);
                    return;
                }

            })
            .fail(function (xhr) {

                $('#ajax-loader').hide();
                enableSubmit();

                const res = xhr.responseJSON;

                if (res && res.errors) {
                    renderLaravelErrors(res.errors);
                    return;
                }

                if (res) {
                    handleBusinessError(res);
                    return;
                }

                toastr.error('Something went wrong.');
            });
        }

        function renderLaravelErrors(errors) {

            $('.form-error').text('');

            if (errors.__business__) {
                errors.__business__.forEach(msg => toastr.error(msg));
                return;
            }

            Object.entries(errors).forEach(([key, messages]) => {

                if (!messages || !messages.length) return;

                const message = messages[0];


                const parts = key.split('.');

                if (parts.length >= 3) {

                    const group = parts[0];
                    const rowId = parts[1];
                    const field = parts[2];

                    let errorSelector = `#${field}_${rowId}_error`;

                    if (!$(errorSelector).length) {
                        errorSelector = `#${key.replace(/\./g, '_')}_error`;
                    }

                    if (!$(errorSelector).length) {
                        errorSelector = `#${field}_error`;
                    }

                    if ($(errorSelector).length) {
                        $(errorSelector).text(message);
                        return;
                    }
                }
                const flatSelector = `#${key}_error`;
                if ($(flatSelector).length) {
                    $(flatSelector).text(message);
                    return;
                }
                toastr.error(message);
            });
        }


        function handleBusinessError(responseData) {

            if (
                responseData.message &&
                (
                    responseData.message.includes('transaction') ||
                    responseData.message.includes('invoice')
                )
            ) {
                $('#orderDetailsContainer')
                    .closest('.order-details-scope')
                    .find('.form-error')
                    .first()
                    .text(responseData.message);
                return;
            }

            toastr.error(responseData.message || 'Request failed.');
        }


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
        $("#onboarding_date, #ondc_invoice_date1, #ondc_invoice_date2,#date_of_onboarding,#date_of_sku_update").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0 // disables future dates
        });

        var MAX_ORDER_ROWS = (slug == "claim-for-catalogue-creation") ? 2 : 50;
        var order_json = @json($claimOrders ?? []);
        //console.log(order_json);

        let PACKAG_ALLOWED_ROWS = 0;
        $('#number_of_orders').on('input', function () {

            if (slug !== 'claim-for-packaging') return;

            PACKAG_ALLOWED_ROWS = parseInt(this.value) || 0;
        });

        function addOrderComponent(response = {}) {

            let totalRows = $(".orderDetailsContainer .order_row").length;

            let limit = (slug == 'claim-for-packaging' && PACKAG_ALLOWED_ROWS > 0) ? PACKAG_ALLOWED_ROWS : MAX_ORDER_ROWS;

            if (totalRows >= limit) {

                if (slug === 'claim-for-packaging') {

                    if (PACKAG_ALLOWED_ROWS <= 0) {
                        toastr.error(
                            "Please enter the number of orders first. You can add rows only up to that number."
                        );
                    } else {
                        toastr.error(
                            "You have entered " + PACKAG_ALLOWED_ROWS +
                            " in Number of Orders. You cannot add more than that."
                        );
                    }

                } else {
                    toastr.error("Only " + limit + " rows are allowed for this claim.");
                }
                return;
            }

            var randomId = Date.now();
            var source = $("#add_more_order_template").html();

            if (!source) {
                console.error("Template not found");
                return;
            }
            Mustache.parse(source);
            var rowIndex = $('.orderDetailsContainer .order_row').length;
            var rendered = Mustache.render(source, {
                randomId: randomId,
                index: rowIndex,
                response: response,
                isYes: response.transaction_status == 0,
                isNo: response.transaction_status == 1,
                addIcon: ADD_ICON,
                deleteIcon: DELETE_ICON
            });

            $(".orderDetailsContainer").append(rendered);

            reorderOrderIndex();
            updateOrderButtons();
            initOrderDatePicker();
        }

        if (order_json && Object.keys(order_json).length > 0) {
            $.each(order_json, function(i, res) {
                addOrderComponent(res);
            });
        } else {
            addOrderComponent(); // only for NEW FORM
        }



        $(document).on("click", ".add_more_order", function() {
            addOrderComponent();
            updateOrderButtons();
        });

        $(document).on("click", ".remove_order_row", function() {
            let id = $(this).data("id");
            $("#order_row_" + id).remove();
            reorderOrderIndex();
            updateOrderButtons();
        });

        // $(document).on("click", ".remove_order_row", function() {

        //     let randomId = $(this).data("id");
        //     let row = $("#order_row_" + randomId);
        //     let orderId = row.find(".order_row_id").val();

        //     if (orderId) {
        //         //if (!confirm("Are you sure you want to delete this row?")) return;

        //         $.ajax({
        //             url: "{{ url('delete-claim-order') }}/",
        //             type: "POST",
        //             data: {
        //                 orderId: orderId,
        //                 _token: "{{ csrf_token() }}"
        //             },
        //             success: function(res) {
        //                 toastr.success("Row deleted successfully");
        //                 row.remove();
        //                 reorderOrderIndex();
        //                 updateOrderButtons();
        //             },
        //             error: function() {
        //                 toastr.error("Error deleting row.");
        //             }
        //         });

        //     } else {
        //         row.remove();
        //         reorderOrderIndex();
        //         updateOrderButtons();
        //     }
        // });


        function reorderOrderIndex() {
            $(".orderDetailsContainer tr").each(function(i) {
                $(this).find(".order_index").text(i + 1);
            });
        }

        function updateOrderButtons() {
            let rows = $(".orderDetailsContainer tr");

            rows.find(".add_more_order").hide();
            rows.find(".delete_order_button").show();

            let firstRow = rows.first();

            firstRow.find(".add_more_order").show();
            firstRow.find(".delete_order_button").hide();
             if (rows.length >= MAX_ORDER_ROWS) {
                rows.find(".add_more_order").hide();
            }
        }

        function initOrderDatePicker() {
            $(".order_datepicker").datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                maxDate: 0
            });
        }

        $(document).on('change', '#category_id', function () {

            var aovType = $(this).find(':selected').data('aov');

            if (!aovType) {
                $('#aovMessage').hide();
                return;
            }

            aovType = aovType.toString().toUpperCase();

            $('#aovMessage').show();

            if (aovType.includes('LOW')) {
                $('#aovMessage').html('This category falls under <b>LOW AOV</b>');
            } 
            else if (aovType.includes('HIGH')) {
                $('#aovMessage').html('This category falls under <b>HIGH AOV</b>');
            }
        });
        
        




    </script>
@endsection
@endsection
