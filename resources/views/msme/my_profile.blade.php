@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4 mt-4">

                <div class="card-header py-3 d-flex align-items-center justify-content-between"> 
                    <h6 class="m-0 font-weight-bold text-primary">MSE Profile Details</h6>
                </div>

                <div class="card-body snp_details">
                    <div class="row g-3">

                        @php
                            $personals = [
                                'udyam_no' => 'Udyam Number',
                                'mobile' => 'Mobile',
                                'email' => 'Email',
                                'entrepreneur_name' => 'Entrepreneur Name',
                                'enterprise_name' => 'Enterprise Name',
                                'organisation_type' => 'Organisation Type',
                                'gender' => 'Gender',
                                'address' => 'Address',
                                'social_category' => 'Social Category',
                                'state_name' => 'State',
                                'district_name' => 'District',
                                'major_activity' => 'Major Activity',
                                'msme_classification' => 'MSME Classification',
                            ];

                            $businesses = [
                                'gstin_no' => 'GSTIN No',
                                'pan_no' => 'PAN No',
                                'specially_abled' => 'Specially Abled',
                                'incorporation_date' => 'Incorporation Date',
                                'pincode' => 'Pincode',
                                'ph' => 'Phone Number',
                                'nic_code' => 'NIC Code',
                                'total_emp' => 'Total Employees',
                                'net_investment_plant_machinery' => 'Net Investment (Plant/Machinery)',
                                'turnover' => 'Turnover',
                                'business_state_name' => 'Current State Business',
                                'transaction_type_name' => 'ONDC Transaction Type',
                                'select_snp' => 'Selected SNP',
                                'product_categories' => 'Product Category',
                                'physical_device_business_transactions' => 'Physical Device Transactions',
                                'printer' => 'Printer',
                                'catalogue_prodcut_details' => 'Catalogue Product Details',
                                'attending_ondc_awareness_workshop' => 'Attended ONDC Awareness Workshop',
                                'products_geography' => 'Products Geography',
                                'team_id' => 'Team ID',
                                'created_at' => 'Created At'
                            ];

                            $booleanKeys = [
                                'specially_abled',
                                'select_snp',
                                'printer',
                                'physical_device_business_transactions',
                                'attending_ondc_awareness_workshop',
                                'catalogue_prodcut_details'
                            ];
                        @endphp

                        <h4>Personal Details</h4>

                        @foreach ($personals as $key => $label)
                            @php $value = $data->$key ?? null; @endphp

                            @if(!empty($value))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label> {{ $value }}
                                </div>
                            @endif
                        @endforeach

                        <h4>Business Details</h4>

                        @foreach ($businesses as $key => $label)
                            @php $value = $data->$key ?? null; @endphp

                            @if(!empty($value) || in_array($key, $booleanKeys))
                                <div class="form-group col-md-4">
                                    <label><strong>{{ $label }}:</strong></label>

                                    @if(in_array($key, $booleanKeys))
                                        {{ !empty($value) ? 'Yes' : 'No' }}
                                    @else
                                        {{ $value }}
                                    @endif
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
