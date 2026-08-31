@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">

                    <?php /*<div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">SNP Details

                            <button onclick="history.go(-1)" class="btn btn-danger btn-sm">⬅ Back</button>

                        </h6>

                    </div> */
                    ?>

                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            NP Details
                        </h6>

                        <button onclick="history.go(-1)" class="btn btn-danger btn-sm">
                            ⬅ Back
                        </button>
                    </div>
                    <div class="card-body snp_details">
                        <div class="row g-3">
                            <h4>Basic Details</h4>


                            <div class="col-md-4">
                                <label><strong>Role(s):</strong></label>
                                {{ $networkProvider->role_names ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Organization ID (as on ONDC):</strong></label>
                                {{ $networkProvider->organization_id ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Organization/Legal Name:</strong></label>
                                {{ $networkProvider->organization_name ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Official/Primary Email ID:</strong></label>
                                {{ $networkProvider->email ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>BPPId.ProviderID:</strong></label>
                                {{ $networkProvider->bppid_providerid ?? '' }}
                            </div>
                            <h4>Contact Details</h4>


                            <div class="col-md-4">
                                <label><strong>Official/Primary Email ID:</strong></label>
                                {{ $networkProvider->contact_details['email'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Primary Contact No:</strong></label>
                                {{ $networkProvider->contact_details['primary_contact_no'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>WhatsApp No/Channel:</strong></label>
                                {{ $networkProvider->contact_details['whatsapp_no'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Website:</strong></label>
                                {{ $networkProvider->contact_details['website'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>App Store Link(s):</strong></label>
                                {{ $networkProvider->contact_details['app_store_links'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Social Media Handles:</strong></label>
                                {{ $networkProvider->contact_details['social_media_handles'] ?? '' }}
                            </div>

                            <h4>Authorised Person(s)</h4>


                            <div class="col-md-4">
                                <label><strong>Name of Authorised Person(s):</strong></label>
                                {{ $networkProvider->authorized_person_details[0]['name'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Role/Designation:</strong></label>
                                {{ $networkProvider->authorized_person_details[0]['designation'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Phone/WhatsApp Number:</strong></label>
                                {{ $networkProvider->authorized_person_details[0]['phone'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Email Address:</strong></label>
                                {{ $networkProvider->authorized_person_details[0]['email'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Authorized Certificate:</strong></label>
                                <a href="{{ $networkProvider->authorized_person_certificate ?? '' }}"
                                    download="{{ $networkProvider->authorized_person_certificate_name ?? '' }}">
                                    {{ $networkProvider->authorized_person_certificate_name ?? '' }}
                                </a>
                            </div>

                            <h4>Configuration Details</h4>


                            <div class="col-md-4">
                                <label><strong>GST Number:</strong></label>
                                {{ $networkProvider->configuration_details['gst_number'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>PAN:</strong></label>
                                {{ $networkProvider->configuration_details['pan'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>CIN:</strong></label>
                                {{ $networkProvider->configuration_details['cin'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Startup ID/DPIIT Recognition:</strong></label>
                                {{ $networkProvider->configuration_details['startup_id'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>FSSAI Number:</strong></label>
                                {{ $networkProvider->configuration_details['fssai_number'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>IEC Number:</strong></label>
                                {{ $networkProvider->configuration_details['iec_number'] ?? '' }}
                            </div>

                            <h4>Bank Details</h4>


                            <div class="col-md-4">
                                <label><strong>Bank Name:</strong></label>
                                {{ $networkProvider->bank_details['bank_name'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>IFSC CODE:</strong></label>
                                {{ $networkProvider->bank_details['ifsc_code'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>A/C No:</strong></label>
                                {{ $networkProvider->bank_details['account_number'] ?? '' }}
                            </div>

                            <h4>Role Selection</h4>

                            @isset($networkProvider->role_selection_details)
                                @foreach ($networkProvider->role_selection_details as $roleSelection)
                                    <div class="col-md-4">
                                        <label><strong>Domain / Product Type:</strong></label>
                                        {{ $roleSelection['domain_name'] ?? '' }}
                                    </div>
                                    <div class="col-md-4">
                                        <label><strong>Transaction Type:</strong></label>
                                        {{ $roleSelection['transaction_type_name'] ?? '' }}
                                    </div>
                                    <div class="col-md-4">
                                        <label><strong>Role:</strong></label>
                                        {{ $roleSelection['role_name'] ?? '' }}
                                    </div>
                                    <div class="col-md-4">
                                        <label><strong>Status:</strong></label>
                                        {{ $roleSelection['status_name'] ?? '' }}
                                    </div>
                                    <div class="col-md-4">
                                        <label><strong>Serviceability:</strong></label>
                                        {{ $roleSelection['serviceability_name'] ?? '' }}
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong>ONDC Domain Mapping:</strong></label>
                                        {{ $roleSelection['ondc_domain_mapping'] ?? '' }}
                                    </div>
                                @endforeach
                            @endisset


                            <h4>Value Proposition for MSEs</h4>

                            {{-- {{ dd($networkProvider) }} --}}
                            <div class="col-md-4">
                                <label><strong>Short Description:</strong></label>
                                {{ $networkProvider->value_proposition_details['short_description'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Additional Service for MSMEs:</strong></label>
                                {{ $networkProvider->value_proposition_details['additional_services'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Language Supported:</strong></label>
                                {{ $networkProvider->value_proposition_details['language_supported_name'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Team Scheme Landing Page:</strong></label>
                                {{ $networkProvider->value_proposition_details['team_scheme_landing_page'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Short Video Pitch:</strong></label>
                                {{ $networkProvider->value_proposition_details['short_video_pitch'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Flyer / Presentation:</strong></label>
                                <a href="{{ $networkProvider->flyer_document ?? '' }}"
                                    download="{{ $networkProvider->flyer_document_name ?? '' }}">
                                    {{ $networkProvider->flyer_document_name ?? '' }}
                                </a>
                            </div>

                            <h4>Commercial Model</h4>


                            <div class="col-md-4">
                                <label><strong>Do you Charge a Fee:</strong></label>
                                {{ $networkProvider->commercial_model_details['fee_charge'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Fee Type:</strong></label>
                                {{ $networkProvider->fee_type ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Flat Fee / Subscription Amount:</strong></label>
                                {{ $networkProvider->commercial_model_details['flat_fee'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Subscription Type:</strong></label>
                                {{ $networkProvider->commercial_model_details['subscription_type_name'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Commission Per Transaction:</strong></label>
                                {{ $networkProvider->commercial_model_details['commission_per_transaction'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Other Fees:</strong></label>
                                {{ $networkProvider->commercial_model_details['other_fees'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Special Offers / Discounts / Add-ons:</strong></label>
                                {{ $networkProvider->commercial_model_details['special_offers'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Special Offers for Team Scheme:</strong></label>
                                {{ $networkProvider->commercial_model_details['team_scheme_offers'] ?? '' }}
                            </div>


                            <div class="col-md-4">
                                <label><strong>Link to Commercial Model:</strong></label>
                                {{ $networkProvider->commercial_model_details['commercial_model_link'] ?? '' }}
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
@endsection
