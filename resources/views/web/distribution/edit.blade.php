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
                                    <li class="breadcrumb-item active">Edit Fund Distribution</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Back
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
            // Use identical consolidated event handlers, pointing target to update endpoint
            initDistributionCreateForm({
                storeUrl: "{{ url('web/fund-distributions/store/' . $row->id) }}", // PASSING ID to TRIGGER UPDATE
                fetchPoolUrl: "{{ url('web/fund-distributions/api/fetch-pool') }}",
                uploadAssetUrl: "{{ url('web/fund-distributions/api/upload-asset') }}",
                subDurationUrl: "{{ url('web/api/allocation/attribute-values') }}",
                subComponentUrl: "{{ url('web/api/allocation/sub-components') }}",
                redirectIndexUrl: "{{ route('dist.index') }}"
            });

            // Pre-trigger initial check of pool numbers on edit load
            $(document).ready(function () {
                setTimeout(() => { $('.trigger-pool').first().trigger('change'); }, 500);
            });
        </script>
    @endsection
@endsection