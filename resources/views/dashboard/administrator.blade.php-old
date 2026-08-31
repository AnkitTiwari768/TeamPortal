@extends('components.admin.content-layout')

@section('page-content')

<style>
span.sta.yellow {
    background: #bd9a1a;
    padding: 2px 5px 3px 6px;
    border-radius: 13px;
    font-size: 12px;
    color: #fff;
}

.highcharts-legend {
    display: block !important;
}

.highcharts-credits {
    display: none
}

/* TBD badge for cards pending BA sign-off */
.badge-tbd {
    background: #fff3cd;
    color: #856404;
    font-size: 10px;
    border-radius: 6px;
    padding: 2px 5px;
    font-weight: 600;
    vertical-align: middle;
}
</style>

<div class="container-fluid px-4 py-4">

    {{-- Filters Section --}}
    @include('dashboard.search')

    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                MSE Dashboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims" type="button"
                role="tab" aria-controls="claims" aria-selected="false">
                Claim Dashboard
            </button>
        </li>
    </ul>

    <div class="bg-white mt-0 px-4 py-2 tab-content" id="dashboardTabsContent">

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- MSE TAB --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">

            {{-- ── Row 1: Registered / Open / Direct / Onboarded ── --}}
            <div class="row doc-cards ondc-mse-cards mt-3 mb-4">

                {{-- 1. Total Registered MSE --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'Total Registered MSE',
                'total' => $registeredMse['total_msme'] ?? 0,
                'link' => url('mis-reports-msme'),
                'items' => $registeredMse['major_activities'],
                'iconType' => 'type-1',
                'cssClass' => 'register-mse-card',
                'totalClass' => 'admin_registered_total',
                'listId' => 'ul_admin_registered_major'
                ])

                {{-- 2. Open MSE --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'Open MSE',
                'total' => $openMse['total_msme_open'] ?? 0,
                'link' => url('open-msme'),
                'items' => $openMse['major_activities'],
                'iconType' => 'type-2',
                'cssClass' => 'choosen-option-card-1',
                'totalClass' => 'admin_open_total',
                'listId' => 'ul_admin_open_major'
                ])

                {{-- 3. Direct Selection by MSE --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'Direct Selection by MSE',
                'total' => $directSelection['total_chossen'] ?? 0,
                'link' => url('msme-chossen-me'),
                'items' => $directSelection['major_activities'],
                'iconType' => 'type-3',
                'cssClass' => 'choosen-option-card-2',
                'totalClass' => 'admin_direct_total',
                'listId' => 'ul_admin_direct_major'
                ])

                {{-- 4. Onboarded MSE --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'Onboarded MSE',
                'total' => $onboardedMse['total_onboarded'] ?? 0,
                'link' => url('onboarded-msme'),
                'items' => $onboardedMse['major_activities'],
                'iconType' => 'type-4',
                'cssClass' => 'onboarded-card',
                'totalClass' => 'admin_onboarded_total',
                'listId' => 'ul_admin_onboarded_major'
                ])

            </div>{{-- /Row 1 --}}

            {{-- ── Row 2: Women / Bulk Upload / Two Step ── --}}
            <div class="row doc-cards ondc-mse-cards mb-4">

                {{-- 5. Women Owned MSEs --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'Total Women Owned MSEs',
                'total' => $womenMse['total_women'] ?? 0,
                'link' => '#',
                'items' => $womenMse['major_activities'],
                'iconType' => 'type-1',
                'cssClass' => 'register-mse-card',
                'totalClass' => 'admin_women_total',
                'listId' => 'ul_admin_women_major',
                'colWidth' => 'col-lg-4'
                ])

                {{-- 6. MSE Registration via Bulk Upload --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'MSE Registration via Bulk Upload',
                'total' => $bulkUpload['total'] ?? 0,
                'link' => '#',
                'items' => [
                'SNP' => $bulkUpload['snp'] ?? 0,
                'IA' => $bulkUpload['ia'] ?? 0
                ],
                'iconType' => 'type-2',
                'cssClass' => 'choosen-option-card-1',
                'totalClass' => 'admin_bulk_total',
                'listId' => 'ul_admin_bulk_major',
                'colWidth' => 'col-lg-4'
                ])

                {{-- 7. MSE Registered via Two Step --}}
                @include('dashboard.components.mse-stat-card', [
                'title' => 'MSE Registered via Two Step',
                'total' => $twoStep['total'] ?? 0,
                'link' => '#',
                'items' => [
                'SNP Assistance' => $twoStep['snp_assist'] ?? 0,
                'Helpdesk' => $twoStep['helpdesk'] ?? 0,
                'Self Registration' => $twoStep['self'] ?? 0
                ],
                'iconType' => 'type-3',
                'cssClass' => 'choosen-option-card-2',
                'totalClass' => 'admin_twostep_total',
                'listId' => 'ul_admin_twostep_major',
                'colWidth' => 'col-lg-4'
                ])

            </div>{{-- /Row 2 --}}

            {{-- ── NP Registration Cards ── --}}
            <h6 class="fw-bold mt-4">Network Participants (NP)</h6>

            <div class="row g-4 mb-4">

                {{-- 8. SNP --}}
                @include('dashboard.components.np-registration-card', [
                'title' => 'Seller Network Participant',
                'cardClass' => 'snp-card',
                'total' => $snpCount['total'] ?? 0,
                'totalClass' => 'admin_snp_total',
                'stats' => [
                ['label' => 'Verification Pending', 'value' => $snpCount['pending'] ?? 0, 'class' =>
                'admin_snp_pending'],
                ['label' => 'Verified SNP', 'value' => $snpCount['verified'] ?? 0, 'class' => 'admin_snp_verified'],
                ['label' => 'Rejected SNP', 'value' => $snpCount['rejected'] ?? 0, 'class' => 'admin_snp_rejected'],
                ['label' => 'Reverted SNP', 'value' => $snpCount['reverted'] ?? 0, 'class' => 'admin_snp_reverted']
                ]
                ])

                {{-- 9. BNP --}}
                @include('dashboard.components.np-registration-card', [
                'title' => 'Buyer Network Participant',
                'cardClass' => 'bnp-card',
                'total' => $bnpCount['total'] ?? 0,
                'totalClass' => 'admin_bnp_total',
                'stats' => [
                ['label' => 'Verification Pending', 'value' => $bnpCount['pending'] ?? 0, 'class' =>
                'admin_bnp_pending'],
                ['label' => 'Verified BNP', 'value' => $bnpCount['verified'] ?? 0, 'class' => 'admin_bnp_verified'],
                ['label' => 'Rejected BNP', 'value' => $bnpCount['rejected'] ?? 0, 'class' => 'admin_bnp_rejected'],
                ['label' => 'Reverted BNP', 'value' => $bnpCount['reverted'] ?? 0, 'class' => 'admin_bnp_reverted']
                ]
                ])

                {{-- 10. LSP --}}
                @include('dashboard.components.np-registration-card', [
                'title' => 'Logistics Service Provider',
                'cardClass' => 'claim-card',
                'total' => $lspCount['total'] ?? 0,
                'totalClass' => 'admin_lsp_total',
                'stats' => [
                ['label' => 'Verification Pending', 'value' => $lspCount['pending'] ?? 0, 'class' =>
                'admin_lsp_pending'],
                ['label' => 'Verified LSP', 'value' => $lspCount['verified'] ?? 0, 'class' => 'admin_lsp_verified'],
                ['label' => 'Rejected LSP', 'value' => $lspCount['rejected'] ?? 0, 'class' => 'admin_lsp_rejected'],
                ['label' => 'Reverted LSP', 'value' => $lspCount['reverted'] ?? 0, 'class' => 'admin_lsp_reverted']
                ]
                ])

                {{-- 11. Associations --}}
                @include('dashboard.components.np-registration-card', [
                'title' => 'Assisted Registration',
                'cardClass' => 'snp-card',
                'total' => $associationsCount['total'] ?? 0,
                'totalClass' => 'admin_assoc_total',
                'stats' => [
                ['label' => 'Verification Pending', 'value' => $associationsCount['pending'] ?? 0, 'class' =>
                'admin_assoc_pending'],
                ['label' => 'Verified', 'value' => $associationsCount['verified'] ?? 0, 'class' =>
                'admin_assoc_verified'],
                ['label' => 'Rejected', 'value' => $associationsCount['rejected'] ?? 0, 'class' =>
                'admin_assoc_rejected']
                ]
                ])

            </div>{{-- /NP Row --}}

            <div class="row mb-4">
                <div class="col-lg-7 mb-3">
                    @include('dashboard.msme-category-pie-chart', ['url' =>
                    url('admin-dashboard-msme-category-count-onboarded')])
                </div>
                <div class="col-lg-5 mb-3">
                    @include('dashboard.top-performer-nps-chart', ['url' => url('admin-dashboard-top-performer-nps')])
                </div>
                <div class="col-lg-12 mb-3">
                    @include('dashboard.msme-gender-percentage-chart', ['url' =>
                    url('admin-dashboard-msme-gender-percentage')])
                </div>
                <div class="row mb-4 ">
                    <div class="col-lg-12 mb-3">
                        @include('dashboard.msme-state-wise-bar-chart', [
                        'url' => url('admin-dashboard-state-wise-msme-count')
                        ])
                    </div>
                </div>
                {{-- Add this where you want the monthly chart to appear --}}
                <div class="row mb-4">
                    <div class="col-lg-12 mb-3">
                        @include('dashboard.registered-vs-onboarded-monthly-chart', [
                        'url' => url('admin-dashboard-registered-vs-onboarded-monthly')
                        ])
                    </div>
                </div>

            </div>

        </div>{{-- /MSE tab --}}


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- CLAIM TAB --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="claims" role="tabpanel" aria-labelledby="claims-tab">

            <div class="row mt-3">
                <div class="col-lg-12">
                    <h1 class="page-title">Claim Management</h1>
                </div>
            </div><br>

             @include('dashboard.components.claims-dashboard-cards')

            {{-- ── Grand-total summary cards ────────────────── --}}
            <!-- <div class="col-lg-12 mb-4 mt-3">
                <div class="stat-row row">
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#e9eaff;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Claims Submitted</h6>
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>

                            </div>
                            <h4 class="fw-bold text-primary admin_all_total">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#f8f9fa;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Draft Claims</h6>
                                <i class="bi bi-pencil-square text-secondary fs-3"></i>

                            </div>
                            <h4 class="fw-bold text-secondary admin_all_draft">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#e0fbe5;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Approved Claims</h6>
                                <i class="bi bi-check2-circle text-success fs-3"></i>

                            </div>
                            <h4 class="fw-bold text-success admin_all_approved">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#fff7f0;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Pending Claims</h6>
                                <i class="bi bi-clock-history text-warning fs-3"></i>

                            </div>

                            <h4 class="fw-bold text-warning admin_all_pending">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#fff2f2;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Rejected Claims</h6>
                                <i class="bi bi-x-circle text-danger fs-3"></i>

                            </div>

                            <h4 class="fw-bold text-danger admin_all_rejected">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#f3fff6;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Payment Completed</h6>
                                <i class="bi bi-currency-rupee text-success fs-3"></i>

                            </div>

                            <h4 class="fw-bold text-success admin_all_payment">0</h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="align-items-baseline d-flex flex-column justify-content-center p-3 shadow-sm stat-card"
                            style="background:#fff8e1;">
                            <div class="d-flex justify-content-between w-100">
                                <h6 class="mt-0">Total Amount Disbursed</h6>
                                <i class="bi bi-cash-stack text-warning fs-3"></i>

                            </div>

                            <h4 class="fw-bold text-warning admin_all_amount">&#8377; 0</h4>
                        </div>
                    </div>
                </div>
            </div> -->

            {{-- ── Claim Type Wise Breakdown ────────────────── --}}
            <h6 class="fw-bold mt-2 mb-3">Claim Type Wise Breakdown</h6>
            <div id="admin_claim_per_type_container" class="row g-4 mb-4">
                <div class="col-12 text-center text-muted py-4">Loading…</div>
            </div>

            {{-- ── Entity Wise Overview ─────────────────────── --}}
            <h6 class="fw-bold mt-2 mb-3">Entity Wise Overview</h6>
            <div id="admin_entity_wise_container" class="row g-4 mb-4">
                <div class="col-12 text-center text-muted py-4">Loading…</div>
            </div>

        </div>{{-- /Claim tab --}}

    </div>{{-- /tab-content --}}
</div>{{-- /container --}}

@endsection

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
@include('dashboard.datepicker')

<script>
$(document).ready(function() {

    /* ── Filter buttons ───────────────────────────────── */
    $('#filterSearch').on('click', function() {
        if (typeof window.reloadAllCharts === 'function') {
            window.reloadAllCharts(true);
        } else {
            reloadAdminMse(true);
            loadAdminClaims(true);
        }
    });

    $('#filterReset').on('click', function() {
        $('#year').val('');
        $('#from_date_new, #to_date_new').val('');
        if (typeof window.reloadAllCharts === 'function') {
            window.reloadAllCharts(false);
        } else {
            reloadAdminMse(false);
            loadAdminClaims(false);
        }
    });

    /* ── Global wrapper for search.blade.php onchange ── */
    window.reloadAllCharts = function(isDateSearch) {
        reloadAdminMse(isDateSearch);
        loadAdminClaims(isDateSearch);
    };

    /* ── Initial load ─────────────────────────────────── */
    reloadAdminMse(false);
    loadAdminClaims(false); // load both on page ready

    /* ── Also reload on tab click (after filter change) ── */
    $('#claims-tab').on('shown.bs.tab', function() {
        // only reload if a filter search has been done but claims weren't refreshed
        // (the flag is reset by filterSearch/filterReset)
        if ($(this).data('needs-reload')) {
            loadAdminClaims(false);
            $(this).data('needs-reload', false);
        }
    });

    function reloadAdminMse(isDateSearch) {
        if (typeof window.getMsmeCategoryCountOnboarded === 'function') {
            window.getMsmeCategoryCountOnboarded(isDateSearch);
        }
        if (typeof window.getTopPerformerNpsChart === 'function') {
            window.getTopPerformerNpsChart(isDateSearch);
        }
        if (typeof window.getMsmePercetageByGender === 'function') {
            window.getMsmePercetageByGender(isDateSearch);
        }
        // Add the new state-wise chart
        if (typeof window.getMsmeStateWiseCount === 'function') {
            window.getMsmeStateWiseCount(isDateSearch);
        }
        if (typeof window.getRegisteredVsOnboardedMonthly === 'function') {
            window.getRegisteredVsOnboardedMonthly(isDateSearch);
        }

        var params = getFilterParams(isDateSearch);
        $.ajax({
            url: "{{ url('/admin-dashboard-mse-summary') }}",
            type: 'GET',
            data: params,
            beforeSend: function() {
                $("#ajax-loader").show();
            },
            success: function(data) {
                updateMseCards(data);
            },
            error: function(xhr) {
                console.error('MSE error:', xhr.responseText);
            },
            complete: function() {
                $("#ajax-loader").hide();
            }
        });
    }

    function updateMseCards(data) {
        /* Registered */
        $('.admin_registered_total').text(data.registeredMse.total_msme || 0);
        populateUl('#ul_admin_registered_major', data.registeredMse.major_activities);

        /* Open */
        $('.admin_open_total').text(data.openMse.total_msme_open || 0);
        populateUl('#ul_admin_open_major', data.openMse.major_activities);

        /* Direct Selection */
        $('.admin_direct_total').text(data.directSelection.total_chossen || 0);
        populateUl('#ul_admin_direct_major', data.directSelection.major_activities);

        /* Onboarded */
        $('.admin_onboarded_total').text(data.onboardedMse.total_onboarded || 0);
        populateUl('#ul_admin_onboarded_major', data.onboardedMse.major_activities);

        /* Women */
        $('.admin_women_total').text(data.womenMse.total_women || 0);
        populateUl('#ul_admin_women_major', data.womenMse.major_activities);

        /* Bulk Upload */
        $('.admin_bulk_total').text(data.bulkUpload.total || 0);
        populateUl('#ul_admin_bulk_major', {
            'SNP': data.bulkUpload.snp || 0,
            'IA': data.bulkUpload.ia || 0
        });

        /* Two Step */
        $('.admin_twostep_total').text(data.twoStep.total || 0);
        populateUl('#ul_admin_twostep_major', {
            'SNP Assistance': data.twoStep.snp_assist || 0,
            'Helpdesk': data.twoStep.helpdesk || 0,
            'Self Registration': data.twoStep.self || 0
        });

        /* SNP */
        $('.admin_snp_total').text(data.snpCount.total || 0);
        $('.admin_snp_pending').text(data.snpCount.pending || 0);
        $('.admin_snp_verified').text(data.snpCount.verified || 0);
        $('.admin_snp_rejected').text(data.snpCount.rejected || 0);
        $('.admin_snp_reverted').text(data.snpCount.reverted || 0);

        /* BNP */
        $('.admin_bnp_total').text(data.bnpCount.total || 0);
        $('.admin_bnp_pending').text(data.bnpCount.pending || 0);
        $('.admin_bnp_verified').text(data.bnpCount.verified || 0);
        $('.admin_bnp_rejected').text(data.bnpCount.rejected || 0);
        $('.admin_bnp_reverted').text(data.bnpCount.reverted || 0);

        /* LSP */
        $('.admin_lsp_total').text(data.lspCount.total || 0);
        $('.admin_lsp_pending').text(data.lspCount.pending || 0);
        $('.admin_lsp_verified').text(data.lspCount.verified || 0);
        $('.admin_lsp_rejected').text(data.lspCount.rejected || 0);
        $('.admin_lsp_reverted').text(data.lspCount.reverted || 0);

        /* Associations */
        $('.admin_assoc_total').text(data.associationsCount.total || 0);
        $('.admin_assoc_pending').text(data.associationsCount.pending || 0);
        $('.admin_assoc_verified').text(data.associationsCount.verified || 0);
        $('.admin_assoc_rejected').text(data.associationsCount.rejected || 0);
    }

    /* ─────────────────────────────────────────────────── */
    /*  CLAIM AJAX                                         */
    /* ─────────────────────────────────────────────────── */
    function loadAdminClaims(isDateSearch) {
        var params = getFilterParams(isDateSearch);
        $.ajax({
            url: "{{ url('/admin-dashboard-claim-summary') }}",
            type: 'GET',
            data: params,
            beforeSend: function() {
                $("#ajax-loader").show();
            },
            success: function(res) {
                updateClaimCards(res);
            },
            error: function(xhr) {
                console.error('Claim error:', xhr.responseText);
            },
            complete: function() {
                $("#ajax-loader").hide();
            }
        });
    }

    function updateClaimCards(res) {
        /* ── Grand-total row ─────────────────────────── */
        var all = res.all || {};
        $('.admin_all_total').text(all.totalClaim || 0);
        $('.admin_all_approved').text(all.approvedCount || 0);
        $('.admin_all_pending').text(all.pendingCount || 0);
        $('.admin_all_rejected').text(all.rejectedCount || 0);
        $('.admin_all_payment').text(all.paymentCompletedCount || 0);
        $('.admin_all_draft').text(all.totalDraft || 0);
        $('.admin_all_amount').html('&#8377; ' + formatAmount(all.totalAmount));

        /* ── Claim Type Wise ─────────────────────────── */
        var container = $('#admin_claim_per_type_container');
        container.empty();

        if (!res.data || res.data.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-4">No data available.</div>');
        } else {
            var colors = ['snp-card', 'bnp-card', 'claim-card', 'snp-card', 'bnp-card'];
            $.each(res.data, function(i, ct) {
                var cls = colors[i % colors.length];
                var html = `
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card custom-card-nsic ${cls}">
                                    <div class="custom-card-nsic-header"></div>
                                    <div class="card-body">
                                        <h5 class="fw-bold">${ct.claimTypeName || ct.claimType}</h5>
                                        <div class="stats-box-nsic">
                                            <div class="stats-icon"><i class="bi bi-clipboard-check"></i></div>
                                            <div>
                                                <small class="text-primary d-block">Total Claims Submitted</small>
                                                <h5 class="fw-bold mb-0">${ct.totalClaim || 0}</h5>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <p class="info-label">Draft</p>
                                                <span class="info-value">${ct.totalDraft || 0}</span>
                                            </div>
                                            <div class="col-4">
                                                <p class="info-label">Pending</p>
                                                <span class="info-value">${ct.pendingCount || 0}</span>
                                            </div>
                                            <div class="col-4">
                                                <p class="info-label">Approved</p>
                                                <span class="info-value">${ct.approvedCount || 0}</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="info-label">Rejected</p>
                                                <span class="info-value">${ct.rejectedCount || 0}</span>
                                            </div>
                                            <div class="col-6">
                                                <p class="info-label">Payment Done</p>
                                                <span class="info-value">${ct.paymentCompletedCount || 0}</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="info-label">Amount Disbursed</p>
                                                <span class="info-value text-success">&#8377; ${formatAmount(ct.totalAmount)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                container.append(html);
            });
        }

        /* ── Entity Wise Overview ────────────────────── */
        var econt = $('#admin_entity_wise_container');
        econt.empty();

        var ew = res.entity_wise || {};
        var entityDefs = [{
                key: 'snp',
                label: 'Claims Submitted by SNP',
                cls: 'snp-card'
            },
            {
                key: 'bnp',
                label: 'Claims Submitted by BNP',
                cls: 'bnp-card'
            },
            {
                key: 'lsp',
                label: 'Claims Submitted by LSP',
                cls: 'claim-card'
            }
        ];

        $.each(entityDefs, function(i, def) {
            var d = ew[def.key] || {};
            var html = `
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic ${def.cls}">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Total ${def.label}</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon"><i class="bi bi-clipboard-check"></i></div>
                                        <div>
                                            <small class="text-primary d-block">Total Claims Submitted</small>
                                            <h5 class="fw-bold mb-0">${d.totalClaim || 0}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">
                                            <p class="info-label">Draft</p>
                                            <span class="info-value">${d.totalDraft || 0}</span>
                                        </div>
                                        <div class="col-4">
                                            <p class="info-label">Pending</p>
                                            <span class="info-value">${d.pendingCount || 0}</span>
                                        </div>
                                        <div class="col-4">
                                            <p class="info-label">Approved</p>
                                            <span class="info-value">${d.approvedCount || 0}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected</p>
                                            <span class="info-value">${d.rejectedCount || 0}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Payment Done</p>
                                            <span class="info-value">${d.paymentCompletedCount || 0}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="info-label">Amount Disbursed</p>
                                            <span class="info-value text-success">&#8377; ${formatAmount(d.totalAmount)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            econt.append(html);
        });
    }

    function formatAmount(val) {
        if (!val) return '0';
        return parseFloat(val).toLocaleString('en-IN', {
            maximumFractionDigits: 2
        });
    }
});

// Expose globally for components
function getFilterParams(isDateSearch) {
    var year = $('#year').val();
    var from = $('#from_date_new').val();
    var to = $('#to_date_new').val();
    var params = {
        year: '',
        from_date_new: '',
        to_date_new: ''
    };

    if (year === '') {
        params.type = 1;
    }

    if (isDateSearch && from && to) {
        params.from_date_new = from;
        params.to_date_new = to;
        return params;
    }

    if (year) {
        params.year = year;
    }

    return params;
}
window.getFilterParams = getFilterParams;

function populateUl(selector, activities, key) {
    var ul = $(selector);
    ul.empty();
    if (!activities) return;
    $.each(activities, function(activity, counts) {
        var value = (key && typeof counts === 'object') ?
            (counts[key] != null ? counts[key] : 0) :
            (counts != null ? counts : 0);
        ul.append(`<li><span>${activity}</span><span>${value}</span></li>`);
    });
}
</script>
@endsection