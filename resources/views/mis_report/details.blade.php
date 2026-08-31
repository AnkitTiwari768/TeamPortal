@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow my-4">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">MSME Details</h6>
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
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
								'source_of_registration' => 'Source of Registration',
							];
							
							
							$businesses = [
								'organisation_type' => 'Organisation Type',
								'gstin' => 'GSTIN',
								'gstin_no' => 'GSTIN No',
								'pan' => 'PAN',
								'pan_no' => 'PAN No',
								'specially_abled' => 'Specially Abled',
								'incorporation_date' => 'Incorporation Date',
								'pincode' => 'Pincode',
								'ph' => 'PH',
								'nic_code' => 'NIC Code',
								//'enterprise_details' => 'Enterprise Details',
								//'activity_details' => 'Activity Details',
								'total_emp' => 'Total Employees',
								'net_investment_plant_machinery' => 'Net Investment (Plant/Machinery)',
								'turnover' => 'Turnover',
								//'dic_attached' => 'DIC Attached',
								'business_state_name' => 'Current State Business',
								'transaction_type_name' => 'ONDC Transaction Type',
								'select_snp' => 'Selected SNP',
								'product_categories' => 'Product Category',
								'physical_device_business_transactions' => 'Physical Device Transactions',
								'printer' => 'Printer',
								'catalogue_prodcut_details' => 'Catalogue Product Details',
								'attending_ondc_awareness_workshop' => 'Attended ONDC Awareness Workshop',
								'products_geography' => 'Products Geography',
								//'status' => 'Status',
								'team_id' => 'Team Id',
								//'nsic_office' => 'NSIC Office',
								//'agree' => 'Agree',
								'created_at' => 'Created At',
								//'updated_at' => 'Updated At'
							];
						@endphp
							<!-- <div class="col-md-6"> -->
								<h4>Personal Details</h4>
								@foreach ($personals as $key => $label)
									@php
										$value = $detail[$key] ?? null;
									@endphp

									@if (!empty($value) || in_array($key, ['gstin', 'pan']))
										<div class="col-md-4">
											<label><strong>{{ $label }}:</strong></label>
											@if (in_array($key, ['gstin', 'pan','specially_abled','select_snp','printer','physical_device_business_transactions','attending_ondc_awareness_workshop']))
												{{ !empty($value) ? 'Yes' : 'No' }}
											@else
												{{ $value }}
											@endif
										</div>
									@endif
								@endforeach
							<!-- </div> -->
							
							<!-- <div class="col-md-6"> -->
								<h4>Business Details</h4>
								@foreach ($businesses as $key => $label)
									@php
										$value = $detail[$key] ?? null;
									@endphp

									@if (($value !== null && $value !== '') || in_array($key, ['gstin', 'pan']))
										<div class="col-md-4">
											<label><strong>{{ $label }}:</strong></label>
											@if (in_array($key, ['gstin', 'pan','specially_abled','select_snp','printer','physical_device_business_transactions','attending_ondc_awareness_workshop']))
												{{ !empty($value) ? 'Yes' : 'No' }}
											@else
												{{ $value }}
											@endif
										</div>
									@endif
								@endforeach
							<!-- </div> -->
						</div>

				</div>
			</div>
		</div>
	</div>
</div>
@endsection

