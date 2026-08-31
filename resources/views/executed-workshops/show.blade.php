@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">

                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ __('workshop.admin_workshop_details') }}
                        </h6>

                        <button onclick="history.go(-1)" class="btn btn-danger btn-sm">
                            ⬅ {{ __('workshop.back') }}
                        </button>
                    </div>

                    <div class="card-body snp_details">
                        <div class="row g-3">
                            <h4>{{ __('workshop.event_details') }}</h4>

                            {{-- Event Title with Duration and Sub Duration --}}
                            <div class="col-md-12">
                                <div class="row g-3">
                                     <div class="col-md-4">
                                        <label><strong>{{ __('workshop.financial_year') }}:</strong></label>
                                        {{ $event->financial_year ?? '-' }}
                                    </div> 
                                    <div class="col-md-4">
                                        <label><strong>{{ __('workshop.event_title') }}:</strong></label>
                                        {{ $event->event_title ?? '-' }}
                                    </div> 
                                    <div class="col-md-4">
                                        <label><strong>{{ __('workshop.duration') }}:</strong></label>
                                        {{ $duration->duration ?? '-' }}
                                    </div>
                                    <div class="col-md-4">
                                        <label><strong>{{ __('workshop.sub_duration') }}:</strong></label>
                                        {{ $sub_duration->sub_duration ?? '-' }}
                                    </div>
                                      <div class="col-md-4">
                                    <label><strong>{{ __('workshop.workshop_category') }}:</strong></label>
                                    {{ $workshopCategoryName ?? '-' }}
                                    </div>
                                   
                                </div>
                            </div>


                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.organiser_name') }}:</strong></label>
                                {{ $org_name ?? '-' }}
                            </div>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.event_for') }}:</strong></label>
                                @if(!empty($event_for))
                                    @foreach(explode(',', $event_for) as $role)
                                        <span class="badge rounded-pill px-2 py-1" style="background-color: dodgerblue; color: #fff; white-space: normal; word-break: break-word;">
                                            {{ ucfirst(trim($role)) }}
                                        </span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.branch_office') }}:</strong></label>
                                {{ !empty($event->branch_offices_id) ? nsic_branch_offices($event->branch_offices_id) : '-' }}
                            </div>

                            @if(!empty($event->remark))
                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.remark') }}:</strong></label>
                                    {{ ucfirst($event->remark) }}
                                </div>
                            @endif

                            
                            <div class="col-md-12">
                                <label><strong>{{ __('workshop.event_description') }}:</strong></label>
                                <div class="p-3 bg-light rounded text-muted" style="white-space: pre-line; line-height: 1.6;">
                                    {{ $event->event_description ?? __('workshop.no_description_provided') }}
                                </div>
                            </div>

                            <h4>{{ __('workshop.venue_location_details') }}</h4>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.state') }}:</strong></label>
                                {{ !empty($event->state_id) ? state_list($event->state_id) : '-' }}
                            </div>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.district') }}:</strong></label>
                                {{ !empty($event->district_id) ? district_list($event->district_id) : '-' }}
                            </div>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.sub_district') }}:</strong></label>
                                {{ !empty($event->sub_district_id) ? sub_district_list($event->sub_district_id) : '-' }}
                            </div>

                            <div class="col-md-12">
                                <label><strong>{{ __('workshop.venue_address') }}:</strong></label>
                                {{ $event->venue_address ?? '-' }}
                            </div>

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.pincode') }}:</strong></label>
                                {{ $event->pincode ?? '-' }}
                            </div>

                            @if(!empty($event->latitude) && !empty($event->longitude))
                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.coordinates') }}:</strong></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $event->latitude }}, {{ $event->longitude }}</span>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $event->latitude }},{{ $event->longitude }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-map me-1"></i> {{ __('workshop.view_map') }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <label><strong>{{ __('workshop.status') }}:</strong></label><br>

                                @if($event->status)
                                    <span class="btn {{
                                        [
                                            'completed' => 'btn-success',
                                            'cancelled' => 'btn-danger',
                                        ][strtolower($event->status)] ?? 'btn-secondary'
                                    }} btn-sm rounded-pill">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                @else
                                    <span>-</span>
                                @endif
                            </div>

                            <h4>{{ __('workshop.event_images') }}</h4>

                            <div class="col-md-12">
                                @if(!empty($uploadedImage) && count($uploadedImage) > 0)
                                    <div class="row g-2 mt-1">
                                        @foreach($uploadedImage as $img)
                                            <div class="col-sm-3 col-6">
                                                <div class="card h-100 border shadow-sm overflow-hidden">
                                                    <a href="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}" target="_blank">
                                                        <img src="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}"
                                                             class="img-fluid w-100"
                                                             style="height: 120px; object-fit: cover; transition: transform 0.3s;"
                                                             onmouseover="this.style.transform='scale(1.05)'"
                                                             onmouseout="this.style.transform='scale(1)'">
                                                    </a>
                                                    <div class="card-footer p-2 bg-light border-0 text-center">
                                                        <a href="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}" 
                                                           download class="btn btn-sm btn-outline-primary w-100 py-1" style="font-size: 11px;">
                                                            <i class="bi bi-download me-1"></i> {{ __('workshop.download') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </div>

                            @if(!empty($event->attachment))
                                <div class="col-md-12">
                                    <label><strong>{{ __('workshop.document_attachment') }}:</strong></label>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <a href="{{ asset('storage/' . $event->attachment) }}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i> {{ __('workshop.view_attachment') }}
                                        </a>
                                        <a href="{{ asset('storage/' . $event->attachment) }}" download class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-download me-1"></i> {{ __('workshop.download') }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <h4>{{ __('workshop.workshop_expense_details') }}</h4>

                            @if($workshopExpense)
                                {{-- Calculate TDS Amount and Net Amount --}}
                                @php
                                    $expenseAmount = $workshopExpense->expense_amount ?? 0;
                                    $tdsPercentage = $workshopExpense->tds_percentage ?? 0;
                                    $nsicFee = $workshopExpense->nsic_fees ?? 0;
                                    
                                    // TDS Amount = (Expense Amount × TDS%) / 100
                                    $tdsAmount = ($expenseAmount * $tdsPercentage) / 100;
                                    
                                    // Net Amount = Expense Amount - TDS Amount (NSIC Fee excluded as per screenshot)
                                    $netAmount = $expenseAmount - $tdsAmount;
                                @endphp

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.number_of_participants') }}:</strong></label>
                                    {{ $workshopExpense->number_of_participants ?? '-' }}
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.expense_amount') }}:</strong></label>
                                    ₹ {{ number_format($expenseAmount, 2) }}
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.nsic_fees') }} (5%):</strong></label>
                                    ₹ {{ number_format($nsicFee, 2) }}
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.tds_applicable') }}:</strong></label>
                                    {{ ($workshopExpense->is_tds_applicable ?? 0) ? __('workshop.yes') : __('workshop.no') }}
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.tds_percentage') }}:</strong></label>
                                    {{ $tdsPercentage }} %
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.tds_amount') }}:</strong></label>
                                    ₹ {{ number_format($tdsAmount, 2) }}
                                </div>

                                {{-- NET AMOUNT - Auto Calculated (as per screenshot) --}}
                                <div class="col-md-4" style="background-color: #f8f9fa; padding: 10px 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                                    <label><strong>{{ __('workshop.net_amount') }} :</strong></label>
                                    <strong style="font-size: 18px; color: #007bff;">₹ {{ number_format($netAmount, 2) }}</strong>
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.sanction_order_no') }}:</strong></label>
                                    {{ $workshopExpense->sanction_order_no ?? '-' }}
                                </div>

                                <div class="col-md-4">
                                    <label><strong>{{ __('workshop.sanction_order_date') }}:</strong></label>
                                    {{ $workshopExpense->sanction_order_date ?? '-' }}
                                </div>

                                <div class="col-md-12">
                                    <label><strong>{{ __('workshop.remarks') }}:</strong></label>
                                    {{ $workshopExpense->remarks ?? '-' }}
                                </div>

                                @if($workshopExpense && $workshopExpense->supporting_documents->count() > 0)
                                    <div class="col-md-12">
                                        <label><strong>{{ __('workshop.supporting_documents') }}:</strong></label>
                                        <div class="row g-2 mt-1">
                                            @foreach($workshopExpense->supporting_documents as $doc)
                                                @php
                                                    $fileUrl = asset('storage/app/' . $doc->file_path . '/' . $doc->file_system_name);
                                                    $isImage = in_array(strtolower($doc->file_extension), [
                                                        'jpg','jpeg','png','gif','bmp','webp'
                                                    ]);
                                                @endphp

                                                <div class="col-sm-3 col-6">
                                                    <div class="card h-100 border shadow-sm overflow-hidden">
                                                        @if($isImage)
                                                            <a href="{{ $fileUrl }}" target="_blank">
                                                                <img src="{{ $fileUrl }}"
                                                                     class="img-fluid w-100"
                                                                     style="height:120px;object-fit:cover;transition:.3s"
                                                                     onmouseover="this.style.transform='scale(1.05)'"
                                                                     onmouseout="this.style.transform='scale(1)'">
                                                            </a>
                                                        @else
                                                            <a href="{{ $fileUrl }}" target="_blank">
                                                                <div class="d-flex align-items-center justify-content-center bg-light"
                                                                     style="height:120px;">
                                                                    <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                                                                </div>
                                                            </a>
                                                        @endif

                                                        <div class="card-footer p-2 bg-light border-0 text-center">
                                                            <small class="d-block text-truncate mb-2">
                                                                {{ $doc->file_name }}
                                                            </small>
                                                            <a href="{{ $fileUrl }}"
                                                               download="{{ $doc->file_name }}"
                                                               class="btn btn-sm btn-outline-primary w-100">
                                                                <i class="bi bi-download me-1"></i> {{ __('workshop.download') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="col-md-12">
                                    <div class="text-center py-3">
                                        <span class="text-muted">{{ __('workshop.no_expense_details_found') }}</span>
                                    </div>
                                </div>
                            @endif

                            <h4>{{ __('workshop.event_schedules') }}</h4>

                            @php
                                $schedules = $event->schedules;
                                if (is_string($schedules)) {
                                    $schedules = json_decode($schedules, true);
                                }
                                if (is_string($schedules)) {
                                    $schedules = json_decode($schedules, true);
                                }
                                $schedules = is_array($schedules) ? $schedules : [];
                            @endphp

                            @if(!empty($schedules))
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="schedulesTable" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 80px;">{{ __('workshop.s_no') }}</th>
                                                    <th>{{ __('workshop.start_date') }}</th>
                                                    <th>{{ __('workshop.end_date') }}</th>
                                                    <th>{{ __('workshop.start_time') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($schedules as $index => $schedule)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <i class="bi bi-calendar-check me-2 text-success"></i>{{ $schedule['start_date'] ?? '-' }}
                                                        </td>
                                                        <td>
                                                            <i class="bi bi-calendar-x me-2 text-danger"></i>{{ $schedule['end_date'] ?? '-' }}
                                                        </td>
                                                        <td>
                                                            <i class="bi bi-clock me-2 text-muted"></i>{{ $schedule['start_time'] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <div class="text-center py-3">
                                        <i class="bi bi-calendar2-x fs-1 text-muted d-block mb-2"></i>
                                        <span class="text-muted">{{ __('workshop.no_schedules_found') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection