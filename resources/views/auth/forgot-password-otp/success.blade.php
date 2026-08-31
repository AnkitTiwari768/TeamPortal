@extends('components.front.auth-layout-v2')

@section('auth-form')
    <div id="success-box" class="text-center">
        <div class="d-flex justify-content-center align-items-center mb-4">
            <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 250px;">
        </div>

        <div class="mb-4">
            <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
        </div>

        <h3 class="fw-bold fs-4 text-dark mb-3">Successful Reset</h3>
        <p class="text-muted mb-4">Your password has been changed successfully. You may Login Now.</p>

        <a href="{{ route('login') }}" class="btn btn-primary-custom w-100 text-white text-decoration-none">Login Now</a>
    </div>
@endsection
