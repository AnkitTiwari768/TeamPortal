@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ $title }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('dist.index') }}" class="text-decoration-none">Distribution</a></li>
                                    <li class="breadcrumb-item active">New Fund Distribution</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ route('dist.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-2"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-1">
                        <form id="distForm" autocomplete="off">
                            @include('web.distribution.partials.form')
                        </form>
                    </div>
                </div>
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