@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">

				<div class="card-header py-3 d-flex align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">{{ $title }}</h6>
                    @include('components.admin.buttons.back-button')
				</div>

				<div class="card-body snp_details">
					<div class="row g-3">

						@php
                            $basic_details = [
                                'owner_name' => 'Owner Name',
                                'store_name' => 'Store Name',
                                'mobile' => 'Mobile',
                                'email' => 'Email',
                                'pin_code' => 'Pin Code',
                                'address' => 'Address',
                                'type_of_business_name' => 'Type Of Business',
                                'other' => 'Other Business',
                                'remark' => 'Remark',
                                'is_bulk' => 'Is Bulk',
                                'type' => 'Type',
                            ];
                        @endphp

						<h4 class="mb-3">Basic Details</h4>

                        @foreach ($basic_details as $key => $label)
                            @if (isset($data[$key]) && $data[$key] !== '')
                                
                                @php
                                    $value = $data[$key];

                                    // TYPE BADGE
                                    if ($key == 'type') {
                                        if ($value == 1) {
                                            $value = '<span class="badge bg-primary">SNP</span>';
                                        } elseif ($value == 2) {
                                            $value = '<span class="badge bg-info text-dark">IA</span>';
                                        } elseif ($value == 3) {
                                            $value = '<span class="badge bg-secondary">Individual</span>';
                                        } else {
                                            $value = '-';
                                        }
                                    }

                                    // BULK BADGE
                                    if ($key == 'is_bulk') {
                                        if ($value == 1) {
                                            $value = '<span class="badge bg-success">Bulk</span>';
                                        } else {
                                            $value = '<span class="badge bg-warning text-dark">Individual</span>';
                                        }
                                    }

                                    // BLANK HANDLE
                                    if ($value === null || $value === '') {
                                        $value = '-';
                                    }
                                @endphp

                                <div class="col-md-4">
                                    <label><strong>{{ $label }}:</strong></label><br>
                                    {!! $value !!}
                                </div>

                            @endif
                        @endforeach


                        {{-- DOCUMENT SECTION --}}
                        @if(!empty($data['cancelled_cheque_file']) || !empty($data['vishwakarma_form_copy_file']))

                            <h4 class="mt-4">Documents</h4>

                            @if(!empty($data['cancelled_cheque_file']))
                            <div class="col-md-4">
                                <label><strong>Cancelled Cheque:</strong></label><br>
                                <a href="{{ url('storage/app/uploads/cancelled_cheque/' . $data['cancelled_cheque_file']) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-primary">
                                   <i class="fa fa-download"></i> {{ $data['cancelled_cheque_file_name'] ?? 'Download' }}
                                </a>
                            </div>
                            @endif

                            @if(!empty($data['vishwakarma_form_copy_file']))
                            <div class="col-md-4">
                                <label><strong>Vishwakarma Form:</strong></label><br>
                                <a href="{{ url('storage/app/uploads/vishwakarma_form_copy/' . $data['vishwakarma_form_copy_file']) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-primary">
                                   <i class="fa fa-download"></i> {{ $data['vishwakarma_form_file_name'] ?? 'Download' }}
                                </a>
                            </div>
                            @endif	

                        @endif	

				    </div>
				</div>

			</div>
		</div>
	</div>
</div>
@endsection