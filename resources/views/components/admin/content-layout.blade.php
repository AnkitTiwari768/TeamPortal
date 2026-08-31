@extends('components.admin.layout')

@section('page-content')

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ $title ?? '' }}</h1>
                        @yield('breadcrumb')
                    </div>
                    <div class="action-header ms-auto">
                        @yield('action-header')
                    </div>
                </div>
                @yield('card-content')
            </div>
        </div>
    </div>
</div>

@endsection