<div class="col-lg-12 mb-4">
    <div class="stat-row row d">
        <div class="col-lg-3">
            <div class="stat-card shadow-sm" style="background: #e9eaff;">
                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                <h6 class="mt-2   ">Total Claims</h6>
                <h4 class="fw-bold text-primary claimCounts_totalClaims">
                    {{ $claimCounts['totalClaims'] ?? '' }}</h4>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="stat-card shadow-sm" style="background: #e0fbe5;">
                <i class="bi bi-check2-circle text-success fs-3"></i>
                <h6 class="mt-2   ">Total Approved Claims</h6>
                <h4 class="fw-bold text-success claimCounts_approvedCount">
                    {{ $claimCounts['approvedCount'] ?? '' }}</h4>
            </div>
        </div>

        {{-- <div class="col-lg-3">
                            <div class=" stat-card shadow-sm" style="background:#fffbea;">
                                <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                                <h6 class="mt-2   ">Total Reverted Claims</h6>
                                <h4 class="fw-bold text-warning claimCounts_revertedCount">
                                    {{ $claimCounts['revertedCount'] ?? '' }}</h4>
                            </div>
                        </div> --}}

        <div class="col-lg-3">
            <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                <i class="bi bi-clock-history text-warning fs-3"></i>
                <h6 class="mt-2   ">Total Pending Claims</h6>
                <h4 class="fw-bold text-warning claimCounts_pendingCount">
                    {{ $claimCounts['pendingCount'] ?? '' }}</h4>
            </div>
        </div>

        @if(!hasRole('ca'))
        <div class="col-lg-3">
            <div class="stat-card shadow-sm " style="background:#fff2f2;">
                <i class="bi bi-x-circle text-danger fs-3"></i>
                <h6 class="mt-2   ">Total Rejected Claims</h6>
                <h4 class="fw-bold text-danger claimCounts_rejectedCount">
                    {{ $claimCounts['rejectedCount'] ?? '' }}</h4>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="stat-card shadow-sm " style="background:#eef6ff;">
                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                <h6 class="mt-2   ">Total Draft</h6>
                <h4 class="fw-bold text-primary claimCounts_draftCount">
                    {{ $claimCounts['draftCount'] ?? '' }}</h4>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="stat-card shadow-sm" style="background:#f3fff6;">
                <i class="bi bi-check2-circle text-success fs-3"></i>
                <h6 class="mt-2">Total Payment Completed</h6>
                <h4 class="fw-bold text-success claimCounts_paymentCompletedCount">
                    {{ $claimCounts['paymentCompletedCount'] ?? '' }}</h4>
            </div>
        </div>
        @endif
    </div>
</div>
