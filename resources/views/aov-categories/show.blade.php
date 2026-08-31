@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>View Category Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">Home</li>
                                <li class="breadcrumb-item">Category List</li>
                                <li class="breadcrumb-item active" aria-current="page">View Category</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="action-header ms-auto">
                        <div class="btn-group drop-btn">
                            <a href="{{ url('aov-categories') }}">
                                <button type="button" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Back
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-1">
                    <div class="row mt-3">
                        
                        @if(isset($data['name']))
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Category Name:</label>
                                <p class="text-muted">{{ $data->name ?? '-' }}</p>
                            </div>
                        @endif    
                        
                        @if(isset($data['aov_grouping_type']))
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Aov Type:</label>
                                <p class="text-muted">{{ ucfirst($data->aov_grouping_type ?? '-') }}</p>
                            </div>
                        @endif    
                        @if(isset($data['ondc_domain_id']))
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">ONDC Domain Mapping:</label>
                                <p class="text-muted">{{ $data->ondc_domain_id ?? '-' }}</p>
                            </div>
                        @endif
                        @if(isset($data['status']))
                            <div class="form-group col-md-4">
                                <label><strong>{{ __('message.status') }} :</strong></label>
                                {{ $data['status'] == 1 ? 'Active' : 'In-active' }}
						    </div> 
                        @endif    
                        @if(isset($data['minimum_order_value']))
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Minimun Order Value:</label>
                                <p class="text-muted">{{($data->minimum_order_value ?? '-')}}</p>
                            </div>
                        @endif   
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection
