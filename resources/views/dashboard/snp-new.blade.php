@extends('components.admin.content-layout')

@section('page-content')
@php
    $roleIdPrefix = 'snp';
@endphp
<style>
    span.sta.yellow {
        background: #bd9a1a;
        padding: 2px 5px 3px 6px;
        border-radius: 13px;
        font-size: 12px;
        color: #fff;
    }
    .highcharts-legend { display: block !important; }
    .highcharts-credits { display: none; }
</style>

<div class="container-fluid px-4 py-4">

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    @include('dashboard.search')

    {{-- ── Tab Headers ─────────────────────────────────────────────────── --}}
    <ul class="nav nav-tabs" id="{{ $roleIdPrefix }}DashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="{{ $roleIdPrefix }}-dashboard-tab"
                data-bs-toggle="tab" data-bs-target="#{{ $roleIdPrefix }}-dashboard"
                type="button" role="tab" aria-controls="{{ $roleIdPrefix }}-dashboard"
                aria-selected="true">
                MSE Dashboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="{{ $roleIdPrefix }}-claims-tab"
                data-bs-toggle="tab" data-bs-target="#{{ $roleIdPrefix }}-claims"
                type="button" role="tab" aria-controls="{{ $roleIdPrefix }}-claims"
                aria-selected="false">
                Claim Dashboard
            </button>
        </li>
    </ul>

    {{-- ── Tab Content ──────────────────────────────────────────────────── --}}
    <div class="bg-white mt-0 px-4 py-2 tab-content" id="{{ $roleIdPrefix }}DashboardTabsContent">

        {{-- ── MSE Dashboard Tab ──────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="{{ $roleIdPrefix }}-dashboard"
             role="tabpanel" aria-labelledby="{{ $roleIdPrefix }}-dashboard-tab">

            {{-- ── Row 1: MSE Stat Cards ──────────────────────────────── --}}
            <div class="row doc-cards ondc-mse-cards mt-3 mb-4">
@php
    // Open MSEs
    $openTotal = is_array($msmeForMeCounts)
        ? ($msmeForMeCounts['total_msme'] ?? 0)
        : ($msmeForMeCounts->total_msme ?? 0);

    $openItems = is_array($msmeForMeCounts)
        ? ($msmeForMeCounts['major_activities'] ?? [])
        : ($msmeForMeCounts->major_activities ?? []);

    // Direct Selection
    $selectedTotal = is_array($msmeChoosenCounts)
        ? ($msmeChoosenCounts['total']['chossen'] ?? 0)
        : ($msmeChoosenCounts->total->chossen ?? 0);

    $selectedItems = is_array($msmeChoosenCounts)
        ? ($msmeChoosenCounts['major_activities'] ?? [])
        : ($msmeChoosenCounts->major_activities ?? []);

    // Onboarded
    $onboardedTotal = is_array($msmeCounts)
        ? ($msmeCounts['total']['onboarded'] ?? 0)
        : ($msmeCounts->total->onboarded ?? 0);

    $onboardedItems = is_array($msmeCounts)
        ? ($msmeCounts['major_activities'] ?? [])
        : ($msmeCounts->major_activities ?? []);
@endphp

{{-- Open MSEs --}}
@include('dashboard.components.mse-stat-card', [
    'colWidth'   => 'col-lg-4',
    'title'      => 'Open MSEs',
    'total'      => $openTotal,
    'link'       => url('open-msme'),
    'items'      => $openItems,
    'itemKey'    => null,
    'iconType'   => 'type-1',
    'cssClass'   => 'open-msme-card',
    'totalClass' => 'openMsmeForMeCounts_total_msme',
    'listId'     => 'ul__open_msmeForMeCounts_major_activities',
    'listClass'  => 'open_msmeForMeCounts_major_activities',
])

{{-- Direct Selection By MSE --}}
@include('dashboard.components.mse-stat-card', [
    'colWidth'   => 'col-lg-4',
    'title'      => 'Direct Selection By MSE',
    'total'      => $selectedTotal,
    'link'       => url('msme-chossen-me'),
    'items'      => $selectedItems,
    'itemKey'    => 'chossen',
    'iconType'   => 'type-2',
    'cssClass'   => 'msme-select-card',
    'totalClass' => 'selected_me_msmeCounts_total_chossen',
    'listId'     => 'ul__selected_me_msmeCounts_chossen',
    'listClass'  => 'selected_me_msmeCounts_major_activities',
])

{{-- Onboarded MSEs --}}
@include('dashboard.components.mse-stat-card', [
    'colWidth'   => 'col-lg-4',
    'title'      => 'Onboarded MSEs',
    'total'      => $onboardedTotal,
    'link'       => url('onboarded-msme'),
    'items'      => $onboardedItems,
    'itemKey'    => 'onboarded',
    'iconType'   => 'type-3',
    'cssClass'   => 'onboarded-card',
    'totalClass' => 'onboarded_mses_msmeCounts_total_onboarded',
    'listId'     => 'ul__onboarded_mses_msmeCounts_onboarded',
    'listClass'  => 'msmeCounts_major_activities',
])
            </div>{{-- /Row 1 --}}

            {{-- ── Charts ──────────────────────────────────────────────── --}}
            <div class="row mb-4">
                <div class="col-lg-12 mb-3">
                    <div class="card common-card h-100">
                        <div class="card-body px-0 py-0">
                            @include('dashboard.msme-category-pie-chart', [
                                'url' => url('snp-dashboard-msme-category-count-onboarded'),
                            ])
                        </div>
                    </div>
                </div>
            </div>

           <div class="row mb-4">
            <div class="col-lg-12 mb-3">
                @include('dashboard.msme-onboarded-by-month-chart')
            </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-12 mb-3">
                    @include('dashboard.msme-state-wise-bar-chart')
                </div>
            </div>

        </div>{{-- /#snp-dashboard --}}

        {{-- ── Claim Dashboard Tab ─────────────────────────────────────── --}}
        <div class="tab-pane fade" id="{{ $roleIdPrefix }}-claims"
             role="tabpanel" aria-labelledby="{{ $roleIdPrefix }}-claims-tab">

            <div class="row mt-3">
                <div class="col-lg-12">
                    <h1 class="page-title">Claims</h1>
                </div>
            </div>

            <div class="col-lg-12 mb-4 mt-3">
                <div id="claim-summary" class="stat-row"></div>
            </div>

            @include('dashboard.claim-status-card')

        </div>{{-- /#snp-claims --}}

    </div>{{-- /.tab-content --}}
</div>{{-- /.container-fluid --}}
@endsection

@section('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>

    @include('dashboard.datepicker')
    
    {{-- Instead of redefining all JS logic, we'll keep the specific SNP chart JS here --}}
    <script>
        const isCA = false; // SNP dashboard is never CA
        
        window.getFilterParams = function (isDateSearch = false, isState = false) {
            let year = $('#year').val();
            let from = $('#from_date_new').val();
            let to = $('#to_date_new').val();
            let params = {
                year: '',
                from_date_new: '',
                to_date_new: '',
                type: 1
            };

            if (isDateSearch && from && to) {
                params.from_date_new = from;
                params.to_date_new = to;
                params.type = 2;
                return params;
            }

            if (year) {
                params.type = 2;
                if (isState) params.syear = year;
                else params.year = year;
            }

            return params;
        };

        function reloadDashboardCards(isDateSearch = false) {
            var params = getFilterParams(isDateSearch);

            $.ajax({
                url: "{{ url('/dashboard') }}",
                type: 'GET',
                data: params,
                beforeSend: function() {
                    $("#ajax-loader").show();
                },
                success: function(response) {
                    $('.openMsmeForMeCounts_total_msme').text(response.msmeForMeCounts?.total_msme || 0);
                    $('.selected_me_msmeCounts_total_chossen').text(response.msmeChoosenCounts?.total?.chossen || 0);
                    $('.onboarded_mses_msmeCounts_total_onboarded').text(response.msmeCounts?.total?.onboarded || 0);

                    populateUl('#ul__open_msmeForMeCounts_major_activities', response.msmeForMeCounts?.major_activities, null);
                    populateUl('#ul__selected_me_msmeCounts_chossen', response.msmeChoosenCounts?.major_activities, 'chossen');
                    populateUl('#ul__onboarded_mses_msmeCounts_onboarded', response.msmeCounts?.major_activities, 'onboarded');
                },
                error: function(xhr) {
                    console.error('Error reloading cards:', xhr.responseText);
                },
                complete: function() {
                    $("#ajax-loader").hide();
                }
            });
        }

        function populateUl(ulId, activities, key) {
            const ul = $(ulId);
            ul.empty();
            if (!activities) return;
            $.each(activities, function (activity, counts) {
                let value;
                if (key && typeof counts === 'object') {
                    value = counts[key] != null ? counts[key] : 0;
                } else {
                    value = counts != null ? counts : 0;
                }
                ul.append(`<li><span>${activity}</span><span>${value}</span></li>`);
            });
        }





        function loadClaimSummary(isDateSearch = false) {
            var params = getFilterParams(isDateSearch);
            $.ajax({
                url:  "{{ url('get-claim-counts-by-status') }}",
                type: 'GET',
                data: params,
                success: function (response) {
                    if (response.success) { renderClaimCards(response.data); }
                }
            });
        }

        function renderClaimCards(data) {
            const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";
            let html = `<div class="row doc-cards">`;
            data.forEach(function (item) {
                const typeTitle = item.claimTypeName || (item.claimType || '').replace(/-/g, ' ');
                html += `
                <div class="col-lg-4 col-md-6 col-12 mb-4 common-claim-card">
                    <div class="card w-100 h-100">
                        <div class="card-body d-flex gap-3">
                            <div class="right text-start w-100">
                                <div class="top-header-card d-flex align-items-center justify-content-between">
                                    <div class="left align-items-center icon type-3">
                                        <img src="${iconSrc}" alt="${typeTitle}">
                                    </div>
                                    <span>
                                        <p class="mb-1 text-wrap">${typeTitle}</p>
                                        <h5><span>${item.totalClaim}</span></h5>
                                    </span>
                                </div>
                                <div class="card-detail">
                                    <ul>
                                        <li><span>Approved</span><span>${item.approvedCount}</span></li>
                                        <li><span>Pending</span><span>${item.pendingCount}</span></li>
                                        <li><span>Rejected</span><span>${item.rejectedCount}</span></li>
                                        <li><span>Payment Completed</span><span>${item.paymentCompletedCount}</span></li>
                                        <li class="total_amount">
                                            <span>Total Amount</span>
                                            <span>₹${Number(item.totalAmount).toLocaleString()}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            html += `</div>`;
            $('#claim-summary').html(html);
        }

        function reloadAllCharts(isDateSearch = false) {
            getMappingMsmeCount(isDateSearch);
            getMsmeStateWiseCount(isDateSearch);
            getMsmeCategoryCountOnboarded(isDateSearch);
            reloadDashboardCards(isDateSearch);
            loadClaimSummary(isDateSearch);
        }

        $(document).ready(function() {
            $('#filterReset').on('click', function() {
                $('#year').val('');
                $('#from_date_new').val('');
                $('#to_date_new').val('');
                reloadAllCharts(false);
            });

            $('#filterSearch').on('click', function() {
                reloadAllCharts(true);
            });

            $('#snp-claims-tab').on('shown.bs.tab', function() {
                if (!$('#claim-summary').hasClass('loaded')) {
                    loadClaimSummary(false);
                    $('#claim-summary').addClass('loaded');
                }
            });

            $('#snp-dashboard-tab').on('shown.bs.tab', function() {
                reloadAllCharts(false);
            });

            // Initial load
            reloadAllCharts(false);
        });
    </script>
@endsection
