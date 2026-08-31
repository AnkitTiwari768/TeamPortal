@extends('components.admin.content-layout')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">{{ __('fund_flow.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="#">{{ __('fund_flow.fund_flow_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('fund_flow.configuration_home') }}</li>
        </ol>
    </nav>
@endsection

@section('action-header')
    @php
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
        $endYear = ($startYear + 1) % 100;
        $financialYearString = $startYear . '-' . sprintf('%02d', $endYear);
    @endphp
    <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1" style="background-color: #e6f6ec; color: #15803d; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.875rem;">
        <i data-feather="check-circle" style="width: 14px; height: 14px; stroke-width: 3; color: #15803d;"></i>
        {{ __('fund_flow.active_year_pill', ['year' => $financialYearString]) }}
    </span>
@endsection

@section('card-content')
    <style>
        .config-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0 !important;
        }
        .config-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04) !important;
            border-color: #bfdbfe !important;
        }
        .config-icon-container {
            transition: all 0.3s ease;
        }
        .config-card:hover .config-icon-container {
            transform: scale(1.05);
            background-color: #dbeafe !important;
        }
        .btn-configure {
            background-color: #0b3b82;
            border-color: #0b3b82;
            transition: all 0.2s ease;
        }
        .btn-configure:hover {
            background-color: #082d64 !important;
            border-color: #082d64 !important;
        }
        .btn-configure svg {
            transition: transform 0.2s ease;
        }
        .btn-configure:hover svg {
            transform: translateX(3px);
        }
    </style>

    <div class="card-body p-4">
        <!-- Subtitle -->
        <p class="text-muted mb-4" style="font-size: 1rem;">
            {{ __('fund_flow.subtitle') }}
        </p>

        <!-- Cards Row -->
        <div class="row g-4">
            <!-- Card 1: Component Utilization Mapping -->
            <div class="col-md-6 col-lg-6 col-12">
                <div class="card h-100 p-4 shadow-sm config-card" style="border-radius: 12px; background-color: #ffffff;">
                    <div class="card-body p-0 d-flex flex-column align-items-start">
                        <!-- Icon Container -->
                        <div class="mb-3 d-flex align-items-center justify-content-center config-icon-container" style="width: 48px; height: 48px; background-color: #eff6ff; color: #1e3a8a; border-radius: 10px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="6" r="2" />
                                <circle cx="6" cy="18" r="2" />
                                <circle cx="18" cy="18" r="2" />
                                <path d="M12 8v5M12 13H6v3M12 13h6v3" />
                            </svg>
                        </div>
                        
                        <!-- Card Title -->
                        <h4 class="card-title fw-bold mb-3 text-dark" style="font-size: 1.25rem;">
                            {{ __('fund_flow.component_utilization_mapping') }}
                        </h4>
                        
                        <!-- Card Description -->
                        <p class="card-text text-muted mb-4 flex-grow-1" style="font-size: 0.925rem; line-height: 1.5; color: #475569 !important;">
                            {{ __('fund_flow.component_utilization_mapping_desc') }}
                        </p>
                        
                        <!-- Configure Button -->
                        <a href="{{ url('component-utilization-mapping') }}" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold btn-configure" style="border-radius: 8px; font-size: 0.875rem;">
                            {{ __('fund_flow.configure') }} 
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
