@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">

                <div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
                    <h6 class="m-0 font-weight-bold text-primary">SNP Details</h6>
                </div>

                <div class="card-body snp_details">
                    <div class="row g-3">

                        @php
                            $basic_details = [
                                'organization_id' => 'Organization Id',
                                'organization_name' => 'Organization Name',
                                'brand_name' => 'App Name / Brand name',
                                'snp_name' => 'Authorized Person Name',
                                'designation' => 'Designation',
                                'user_email' => 'Email',
                                'mobile' => 'Mobile No',
                            ];

                            $configuration_details = [
                                'domain' => 'Domain',
                                'sub_domain' => 'Sub Domain',
                                'transaction_type' => 'Transaction Type',
                                'state_id' => 'States',
                            ];

                            $bank_details = [
                                'bank_name' => 'Bank Name',
                                'ifsc_code' => 'IFSC Code',
                                'account_no' => 'Account No',
                                'pan' => 'PAN',
                                'gst_number' => 'GST Number',
                            ];

                            $commercial_details = [
                                'commercial_model' => 'Commercial Model',
                                'live_seller' => 'No of live sellers on boarded',
                                'date_of_going_live_on_ondc' => 'Date of going live on ONDC for the SNP',
                                'no_of_transactions_done' => 'No of transactions done as on',
                                'short_description' => 'Short Description',
                            ];

                            function showVal($val){
                                return is_array($val) ? implode(', ', $val) : $val;
                            }
                        @endphp


                        {{-- ============= BASIC DETAILS ============= --}}
                        <h4>Basic Details</h4>

                        @foreach ($basic_details as $key => $label)
                            @php $value = $snpDetail->$key ?? null; @endphp

                            @if (!empty($value))
                                <div class="col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach

                    
                        {{-- ============= CONFIGURATION DETAILS ============= --}}
                        <h4>Configuration Details</h4>

                        @foreach ($configuration_details as $key => $label)
                            @php 
                                $value = showVal($snpDetail->$key ?? null);
                            @endphp

                            @if (!empty($value))
                                <div class="col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach



                        {{-- ============= BANK DETAILS ============= --}}
                        <h4>Bank Details</h4>

                        @foreach ($bank_details as $key => $label)
                            @php $value = $snpDetail->$key ?? null; @endphp

                            @if (!empty($value))
                                <div class="col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach

                        {{-- ============= COMMERCIAL DETAILS ============= --}}
                        <h4>Commercial Details</h4>

                        @foreach ($commercial_details as $key => $label)
                            @php $value = $snpDetail->$key ?? null; @endphp

                            @if (!empty($value))
                                @if($key == 'short_description')
                                    <div class="col-md-12">
                                @else
                                    <div class="col-md-4">
                                @endif

                                    <label><strong>{{ $label }}:</strong></label>
                                    {{ $value }}
                                </div>
                            @endif
                        @endforeach


                        @if (!empty($snpDetail->description_document))
                            <div class="col-md-4">
                                <label><strong>Description:</strong></label><br>
                                <a href="{{ asset('storage/'.$snpDetail->description_document) }}"
                                   download="{{ $snpDetail->description_document_original_name }}">
                                    View Description
                                </a>
                            </div>
                        @endif


                    </div> {{-- END ROW --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
