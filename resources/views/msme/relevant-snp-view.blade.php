@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4 mt-4">

                <div class="card-header py-3 d-flex align-items-center justify-content-between"> 
                    <h6 class="m-0 font-weight-bold text-primary">NP Profile Details</h6>
                    @include('components.admin.buttons.back-button')
                </div>

                <div class="card-body snp_details">
                    <div class="row g-3">

                        @php
                            $personals = [
                                'snp_id'            => 'NP ID',
                                'snp_name'          => 'NP Name',
                                'organization_name' => 'Organization Name',
                                'email'             => 'Official Email',
                                'role_names'        => 'Role(s)',
                                'domain_names'      => 'Domain(s)',
                                'state_name'        => 'State(s)',
                                'bppid_providerid'  => 'BPPId.ProviderID',
                            ];

                            $contactDetails = [
                                'email'              => 'Contact Email',
                                'primary_contact_no' => 'Primary Contact No',
                                'whatsapp_no'        => 'WhatsApp No',
                                'website'            => 'Website',
                                'app_store_links'    => 'App Store Links',
                                'social_media_handles'=> 'Social Media Handles',
                            ];

                            $authPerson = [
                                'name'             => 'Name',
                                'designation_name' => 'Role / Designation',
                                'phone'            => 'Phone / WhatsApp',
                                'email'            => 'Email',
                            ];

                            $configs = [
                                'gst_number'  => 'GST Number',
                                'pan'         => 'PAN',
                                'cin'         => 'CIN',
                                'startup_id'  => 'Startup ID',
                                'iec_number'  => 'IEC Number',
                                'fssai_number'=> 'FSSAI Number',
                            ];

                            $roles = [
                                'domain_name'            => 'Domain / Product Type',
                                'transaction_type_name'  => 'Transaction Type',
                                'role_name'              => 'Role',
                                'status_name'            => 'Status',
                                'serviceability_name'    => 'Serviceability',
                            ];

                            $valueProps = [
                                'short_description'        => 'Short Description',
                                'additional_services'      => 'Additional Services',
                                'language_supported_name'  => 'Language Supported',
                                'team_scheme_landing_page' => 'Team Scheme Landing Page',
                            ];

                            $commercials = [
                                'fee_charge'     => 'Do you Charge a Fee',
                                'flat_fee'       => 'Flat Fee',
                                'other_fees'     => 'Other Fees',
                                'special_offers' => 'Special Offers',
                            ];
                        @endphp

                        <h4>Personal Details</h4>

                        @foreach ($personals as $key => $label)
                            @php $value = $data->$key ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach

                        <h4>Contact Details</h4>

                        @foreach ($contactDetails as $key => $label)
                            @php $value = $data->contact_details[$key] ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach


                        <h4>Authorised Person(s)</h4>

                        @foreach ($data->authorized_person_details as $person)

                            @foreach ($authPerson as $key => $label)
                                @php $value = $person[$key] ?? null; @endphp

                                @if(!empty($value))
                                    <div class="form-group col-md-4">
                                        <label><strong>{{ $label }}:</strong></label>
                                        {{ $value }}
                                    </div>
                                @endif
                            @endforeach
                        @endforeach

                        <h4>Configuration Details</h4>

                        @foreach ($configs as $key => $label)
                            @php $value = $data->configuration_details[$key] ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach


                        <h4>Role Selection</h4>

                        @foreach ($data->role_selection_details as $role)

                            @foreach ($roles as $key => $label)
                                @php $value = $role[$key] ?? null; @endphp

                                @if(!empty($value))
                                    <div class="form-group col-md-4">
                                        <label><strong>{{ $label }}:</strong></label>
                                        {{ $value }}
                                    </div>
                                @endif
                            @endforeach
                        @endforeach


                        <h4>Value Proposition for MSEs</h4>

                        @foreach ($valueProps as $key => $label)
                            @php $value = $data->value_proposition_details[$key] ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach

                        <h4>Commercial Model</h4>

                        @foreach ($commercials as $key => $label)
                            @php $value = $data->commercial_model_details[$key] ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
