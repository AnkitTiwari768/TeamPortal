@extends('components.admin.content-layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-lock" style="font-size: 4rem; color: #dc3545;"></i>
                    </div>
                    <h2 class="text-danger mb-3">Access Denied</h2>
                    <p class="text-muted mb-4">
                        {{ $message ?? 'You do not have sufficient permissions to access the dashboard. Please contact your administrator.' }}
                    </p>
                  
                </div>
            </div>
        </div>
    </div>
</div>
@endsection