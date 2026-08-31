@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-muted small">
                        <li class="breadcrumb-item"><a href="{{ route('dist.index') }}"
                                class="text-decoration-none">Distribution</a></li>
                        <li class="breadcrumb-item active">New Fund Distribution</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('dist.index') }}" class="btn btn-light border text-muted shadow-sm">
                    <i class="fa fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="distForm" autocomplete="off">
                    @include('web.distribution.partials.form')
                </form>
            </div>
        </div>
    </div>

    @section('js')
        <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
        <script src="{{ asset('assets/js/allocation/distribution.js') }}"></script>
        <script>
            initDistributionCreateForm({
                fetchPoolUrl: "{{ url('web/fund-distributions/api/fetch-pool') }}",
                subDurationUrl: "{{ url('web/api/allocation/attribute-values') }}/{{ config('allocation.duration_code', 'duration') }}",
                subComponentUrl: "{{ url('web/api/allocation/sub-components') }}",
                uploadAssetUrl: "{{ url('web/fund-distributions/api/upload-asset') }}",
                storeUrl: "{{ route('dist.store') }}",
                redirectIndexUrl: "{{ route('dist.index') }}"
            });
        </script>
    @endsection
@endsection