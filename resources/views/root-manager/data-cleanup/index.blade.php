@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body">

    <p class="text-muted">
        These actions permanently delete data and cannot be undone. Please proceed with caution.
    </p>

    <div class="row g-4">

        <div class="col-md-6 col-lg-4">
            <div class="cleanup-card">
                <h5>All Claims Data Delete</h5>
                <p class="text-muted small">
                    dy_queries, qms_attachments, qms_messages, qms_queries, temporary_claims,
                    temporary_claim_orders, claims, claim_orders, dy_batches, dy_batch_claims,
                    dy_workflow_logs, dy_workflow_instances
                </p>
                <form method="POST" action="{{ route('data-cleanup.claims') }}"
                    onsubmit="return confirm('Are you sure you want to delete ALL claims data? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Delete All Claims Data</button>
                </form>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="cleanup-card">
                <h5>All Component Utilization Delete</h5>
                <p class="text-muted small">
                    component_utilization_mappings, component_utilization_mapping_details
                </p>
                <form method="POST" action="{{ route('data-cleanup.component-utilization') }}"
                    onsubmit="return confirm('Are you sure you want to delete ALL component utilization data? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Delete Component Utilization</button>
                </form>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="cleanup-card">
                <h5>All Fund Allocation Delete</h5>
                <p class="text-muted small">
                    fund_allocations, fund_allocations_map, fund_allocation_component_mappings,
                    fund_allocation_histories, fund_carry_forwards, fund_carry_forward_details,
                    fund_carry_forward_logs
                </p>
                <form method="POST" action="{{ route('data-cleanup.fund-allocation') }}"
                    onsubmit="return confirm('Are you sure you want to delete ALL fund allocation data? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Delete Fund Allocation</button>
                </form>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="cleanup-card">
                <h5>All Fund Distribution Delete</h5>
                <p class="text-muted small">
                    fund_distribution, fund_distributions, fund_pools
                </p>
                <form method="POST" action="{{ route('data-cleanup.fund-distribution') }}"
                    onsubmit="return confirm('Are you sure you want to delete ALL fund distribution data? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Delete Fund Distribution</button>
                </form>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="cleanup-card">
                <h5>All Workshop Data Delete</h5>
                <p class="text-muted small">
                    workshops, workshop_expenses
                </p>
                <form method="POST" action="{{ route('data-cleanup.workshop') }}"
                    onsubmit="return confirm('Are you sure you want to delete ALL workshop data? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Delete Workshop Data</button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.cleanup-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.cleanup-card h5 {
    font-weight: 600;
    margin-bottom: 10px;
}
.cleanup-card p {
    min-height: 60px;
}
</style>

@push('js')
<script>
    @if (session('success'))
        toastr.success(@json(session('success')));
    @endif
    @if (session('error'))
        toastr.error(@json(session('error')));
    @endif
</script>
@endpush

@endsection
