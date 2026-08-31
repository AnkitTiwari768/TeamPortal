@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow m-3">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">Allocation Details</h6>
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							@include('components.admin.buttons.back-button')
						</div>
					</div>
				</div>
				<div class="card-body snp_details">
					<div class="row g-3">
						@php
							$basic_details = [
								'financial_year' => 'Financial Year',
								'duration' => 'Duration',
								'duration_limit' => 'Duration Limit',
								'total_amount_allocated' => 'Total Amount Allocated',
								'sanction_order_no' => 'Sanction Order No',
								'sanction_order_date_format' => 'Sanction Order Date',
								'remarks' => 'Remarks',
							];
							
						@endphp

							<h4>Basic Details</h4>
							@foreach ($basic_details as $key => $label)
								@php
									$value = $row[$key] ?? null;
								@endphp

								@if (!empty($value))
									<div class="col-md-4">
										<label ><strong>{{ $label }}:</strong></label>
											{{ $value }}
									</div>
								@endif
							@endforeach
							@if (!empty($row['allocationDocument']))
								<div class="col-md-4">
									<label><strong>Document:</strong></label><br>
									<a href="{{ url($row['allocationDocument']) }}" download="{{ $row['upload_document_original_name'] }}" >
										View Document
									</a>
								</div>
							@endif
							
                            <h4>Component Details</h4>
                            
                            @if(!empty($row['map_details']))
                                <table class="table table-themed table-striped table-bordered">
                                    <thead>
                                        <tr>
                                        <th>Major Component</th>
                                        <th>Component</th>
                                        <th>Sub Component</th>
                                        <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="container_personality">
                                        @foreach ($row['map_details'] as $key => $value)
                                        <tr>
                                            <td>{{ $value->major_component_name ?? '-' }}</td>
                                            <td>{{ $value->component_name ?? '-' }}</td>
                                            <td>{{ $value->sub_component_name ?? '-' }}</td>
                                            <td>{{ number_format($value->amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
							

				</div>
					
				</div>
			</div>
		</div>
	</div>
</div>


@endsection