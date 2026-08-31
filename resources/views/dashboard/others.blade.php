@extends('components.admin.content-layout')

@section('page-content')
    {{-- @include('dashboard.in-progress') --}}

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
    </style>

    <div class="container-fluid px-4 py-4">
        {{-- Filters Section --}}
        @include('dashboard.search')


        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            @if (!hasRole('ca'))
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                        type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                        MSE Dashboard
                    </button>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link @if (hasRole('ca')) active @endif" id="claims-tab"
                    data-bs-toggle="tab" data-bs-target="#claims" type="button" role="tab" aria-controls="claims"
                    aria-selected="{{ hasRole('ca') ? 'true' : 'false' }}">
                    Claim Dashboard
                </button>
            </li>
        </ul>



        <div class="tab-content mt-4" id="dashboardTabsContent">
            @if (!hasRole('ca'))
                <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">

                    <div class="row doc-cards ondc-mse-cards mt-3 mb-4">
                        <div class="col-lg-3 register-mse-card">
                            <a href="{{ url('mis-reports-msme') }}" class="">
                                <div class="card w-100">
                                    <div class="card-body d-flex gap-3">
                                        <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-1">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>Total Registered MSEs</p>
                                                    <h5><span id="total_application"
                                                            class="registered_msme_total">{{ $registeredMsmeCounts['total_msme'] }}</span>
                                                    </h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul id="ul_registered_counts_major_activities"
                                                    class="registered_counts_major_activitie">
                                                    @foreach ($registeredMsmeCounts['major_activities'] as $activity => $count)
                                                        <li>
                                                            <span>{{ $activity }}</span>
                                                            <span>{{ $count }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 choosen-option-card-1">
                            <a href="{{ url('open-msme') }}" class="">
                                <div class="card w-100">
                                    <div class="card-body d-flex gap-3">
                                        <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-2">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>Open MSEs</p>
                                                    <h5><span id="total_application"
                                                            class="total_msme_open">{{ $msmeOpenCounts['total']['total_msme_open'] ?? '' }}</span>
                                                    </h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul id="ul_msmeCounts_major_activities" class="msmeCounts_major_activities">
                                                    @foreach ($msmeOpenCounts['major_activities'] as $activity => $counts)
                                                        <li>
                                                            <span>{{ $activity }}</span>
                                                            <span>{{ $counts['total_msme_open'] ?? '' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 choosen-option-card-2">
                            <a href="{{ url('msme-chossen-me') }}" class="">
                                <div class="card w-100">
                                    <div class="card-body d-flex gap-3">
                                        <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-3">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p class="text-nowrap">Direct Selection By MSE</p>
                                                    <h5><span id="total_application"
                                                            class="choosen_msme_counts_totals">{{ $msmechoosenCounts['total']['chossen'] }}</span>
                                                    </h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul id="ul_major_choosen_msme_counts_totals"
                                                    class="major_choosen_msme_counts_totals">
                                                    @foreach ($msmechoosenCounts['major_activities'] as $activity => $counts)
                                                        <li>
                                                            <span>{{ $activity }}</span>
                                                            <span>{{ $counts['chossen'] ?? '' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 onboarded-card">
                            <a href="{{ url('onboarded-msme') }}" class="">
                                <div class="card w-100">
                                    <div class="card-body d-flex gap-3">
                                        <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-4">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>Total Onboarded MSEs</p>
                                                    <h5><span id="total_application"
                                                            class="msme_counts_total_onboarded">{{ $msmeOnboardedCounts['total']['onboarded'] }}</span>
                                                    </h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul id="ul_major_msme_counts_total_onboarded"
                                                    class="major_msme_counts_total_onboarded">
                                                    @foreach ($msmeOnboardedCounts['major_activities'] as $activity => $counts)
                                                        <li>
                                                            <span>{{ $activity }}</span>
                                                            <span>{{ $counts['onboarded'] ?? '' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
					<h6 class="fw-bold">Network Participants (NP)</h6><hr>
					<div class="row g-4 mb-4">
                        <!-- Seller Network Participants (SNP) -->
						 <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic snp-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Seller Network Participants (SNP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 snpCount_totalSnp">
                                                {{ $snp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value snpCount_pendingSnp">{{ $snp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified SNP</p>
                                            <span
                                                class="info-value snpCount_approvedSnp">{{ $snp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected SNP</p>
                                            <span
                                                class="info-value snpCount_rejectedSnp">{{ $snp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted SNP</p>
                                            <span
                                                class="info-value snpCount_revertedSnp">{{ $snp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>
						
						<!-- Buyer Network Participants (BNP)-->
						<div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic bnp-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Buyer Network Participants (BNP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 bnpCount_totalBnp">
                                                {{ $bnp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value bnpCount_pendingBnp">{{ $bnp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified SNP</p>
                                            <span
                                                class="info-value bnpCount_approvedBnp">{{ $bnp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected SNP</p>
                                            <span
                                                class="info-value bnpCount_rejectedBnp">{{ $bnp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted SNP</p>
                                            <span
                                                class="info-value bnpCount_revertedBnp">{{ $bnp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>
						
						 <!-- Seller Network Participants (SNP) -->
						<div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic claim-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Logistics Service Providers (LSP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 lspCount_totalLsp">
                                                {{ $lsp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value lspCount_pendingLsp">{{ $lsp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified LSP</p>
                                            <span
                                                class="info-value lspCount_approvedLsp">{{ $lsp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected LSP</p>
                                            <span
                                                class="info-value lspCount_rejectedLsp">{{ $lsp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted LSP</p>
                                            <span
                                                class="info-value lspCount_revertedLsp">{{ $lsp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="row g-4 mb-4">

                        <?php /*<div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic snp-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Network Participants (NP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 snpCount_totalSnp">
                                                {{ $np['total_registered_nps'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value snpCount_pendingSnp">{{ $np['total_pending_nps'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified NPs</p>
                                            <span
                                                class="info-value snpCount_approvedSnp">{{ $np['total_verified_nps'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected NPs</p>
                                            <span
                                                class="info-value snpCount_rejectedSnp">{{ $np['total_rejected_nps'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted NPs</p>
                                            <span
                                                class="info-value snpCount_revertedSnp">{{ $np['total_reverted_nps'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div> */?>

                        <!-- Repeat for other cards -->
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic ia-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Association Management</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 ia_count_total"> {{ $ia['total_registered_ia'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending</p>
                                            <span class="info-value ia_count_pending">{{ $ia['total_pending_ia'] ?? '' }} </span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified Associations</p>
                                            <span class="info-value ia_count_approved">{{ $ia['total_verified_ia'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected Associations</p>
                                            <span class="info-value ia_count_rejected">{{ $ia['total_rejected_ia'] ?? '' }}</span>
                                        </div>
                                   
                                    </div>
                                    {{-- <a href="{{ url('mis-bnp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>



                        <!-- Repeat for other cards -->
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic claim-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Claims</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Claims</small>
                                            <h5 class="fw-bold mb-0 claimCounts_totalClaims1">0<?php /*{{ $claimCounts['totalClaims'] ?? '' }}*/?></h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Total Approved Claims</p>
                                            <span class="info-value claimCounts_approvedCount1">0<?php /*{{ $claimCounts['approvedCount'] ?? '' }} */?></span>
                                        </div>
                                        <?php /*<div class="col-6">
                                <p class="info-label">Total Reverted Claims</p>
                                <span class="info-value claimCounts_revertedCount1">0{{ $claimCounts['revertedCount'] ?? '' }}</span>
                                </div>*/
                                        ?>
                                        <div class="col-6">
                                            <p class="info-label">Total Pending Claims</p>
                                            <span class="info-value claimCounts_pendingCount1">0<?php /*{{ $claimCounts['pendingCount'] ?? '' }}*/?></span>
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-6">
                                            <p class="info-label">Total Rejected Claims</p>
                                            <span class="info-value claimCounts_rejectedCount">0<?php /*{{ $claimCounts['rejectedCount'] ?? '' }}*/?></span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- You can duplicate the above col for remaining 2 cards -->

                    </div>

                    <div class="row mb-4">

                        <div class="col-lg-7">

                            <div class="card common-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between main-charts-headding-wrap pb-2 ">
                                        <h3> MSE By Top 10 Categories</h3>
                                    </div>

                                    <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                        <figure class="highcharts-figure">
                                            <div id="pieChart"></div>

                                        </figure>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card common-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
                                        <h3> MSE Onboarded Percentage (Gender Wise)</h3>
                                    </div>

                                    <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                        <figure class="highcharts-figure">
                                            <div id="pieChartPercentage"></div>

                                        </figure>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">

                        <div class="col-lg-6 ">
                            <div class="card common-card">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between main-charts-headding-wrap">
                                        <div class="pb-2">
                                            <h3>MSE Onboarded Per Month</h3>
                                            <span> Visual representation of MSE Onboarded with the system </span>
                                        </div>
                                        <!-- <div class="year-filter">
                                            {!! Form::select('year', year_list(), $row['year'] ?? null, [
                                                'class' => 'form-select',
                                                'id' => 'year',
                                                'onchange' => 'getMappingMsmeCount',
                                            ]) !!}
                                            </div> -->
                                    </div>


                                    <div class="graph-area mt-2">
                                        <figure class="highcharts-figure">
                                            <div id="bargraph_msme_onboarded"></div>
                                        </figure>
                                    </div>


                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6 ">
                            <div class="card common-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
                                        <h3> Top Contributing SNP</h3>
                                    </div>

                                    <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                        <figure class="highcharts-figure">
                                            <div id="top-performer-snp"></div>

                                        </figure>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card common-card">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between main-charts-headding-wrap">
                                        <div class="pb-2">
                                            <h3>State Wise Onboarded MSE</h3>
                                            <span> Visual Representation of MSEs registered with the SNP </span>
                                        </div>
                                        <!-- <div class="year-filter">
                                                    {!! Form::select('year', year_list(), $row['year'] ?? null, [
                                                        'class' => 'form-select',
                                                        'id' => 'year',
                                                        'onchange' => 'getMappingMsmeCount',
                                                    ]) !!}
                                                    </div> -->
                                    </div>

                                    <div class="graph-area mt-2">
                                        <figure class="highcharts-figure">
                                            <div id="bargraph_msme_state_wise_onboarded"></div>
                                        </figure>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            <div class="tab-pane fade @if (hasRole('ca')) show active @endif" id="claims"
                role="tabpanel" aria-labelledby="claims-tab">

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h1 class="page-title">Claims</h1>
                    </div>
                </div>

                <div class="col-lg-12 mb-4 mt-3">
                    <div id="claim-summary" class="stat-row"></div>
                </div>

                @include('dashboard.claim-status-card')

                <?php /*
                <div class="col-lg-12 mb-4">
                    <div class="stat-row row">
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background: #e9eaff;">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Claimsssss' : 'Total Batch' }}</h6>
                                <h4 class="fw-bold text-primary totalCliamCounts">0</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background: #e0fbe5;">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Approved Claims' : 'Total Approved Batch'}}</h6>
                                <h4 class="fw-bold text-success claimsApprovedClaimsCount">0</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class=" stat-card shadow-sm" style="background:#fffbea;">
                                <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Reverted Claims' : 'Total Revert Batch'}}</h6>
                                <h4 class="fw-bold text-warning claimCountsRevertedCount">0</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                                <i class="bi bi-clock-history text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Pending Claims' : 'Total Pending Batch'}}</h6>
                                <h4 class="fw-bold text-warning claimsPendingCounts">0</h4>
                            </div>
                        </div>
                        @if(!hasRole('ca'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#fff2f2;">
                                <i class="bi bi-x-circle text-danger fs-3"></i>
                                <h6 class="mt-2">Total Rejected Claims</h6>
                                <h4 class="fw-bold text-danger claimRejectedCounts">0</h4>
                            </div>
                        </div>
                        @if(hasRole('snp'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#eef6ff;">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                <h6 class="mt-2">Total Draft</h6>
                                <h4 class="fw-bold text-primary claimCountsDraftCount">0</h4>
                            </div>
                        </div>
                        @endif
                        @endif
                        @if(!hasRole('ca'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background:#f3fff6;">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                                <h6 class="mt-2">Total Payment Completed</h6>
                                <h4 class="fw-bold text-success claimCountsPaymentCompleted">0</h4>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>*/
                ?>

            </div>


        </div>
    @endsection


    @section('js')
        <script src="https://code.highcharts.com/highcharts.js"></script>
        @include('dashboard.datepicker')

        <script>
            const isCA = {{ hasRole('ca') ? 'true' : 'false' }};
            $(document).ready(function() {

                /*$( "#from_date_new" ).datepicker({
                    dateFormat: "dd-mm-yy",
                    changeYear: true,
                    changeMonth: true,
                    //minDate: new Date(),
                    onSelect: function(selected) {
                    $("#to_date_new").datepicker("option","minDate", selected)
                    },
                    onClose: function(selected) {
                        $("#reset_btn").show();
                    }
                });

                $( "#to_date_new" ).datepicker({
                    dateFormat: "dd-mm-yy",
                    changeYear: true,
                    changeMonth: true, 
                    minDate: new Date(),
                    onSelect: function(selected) {
                    $("#from_date_new").datepicker("option","maxDate", selected); 
                    },
                    onClose: function(selected) {
                    $("#reset_btn").show();
                    }
                }); 

                applyYearRestriction();

                $('#year').on('change', function () {
                    const selectedYear = $(this).val();

                    if (!selectedYear) {
                        $('#from_date_new, #to_date_new').datepicker('option', {
                            yearRange: 'c-10:c+10'
                        });
                        return;
                    }

                    const defaultDate = new Date(selectedYear, 0, 1);

                    $('#from_date_new, #to_date_new').datepicker('option', {
                        defaultDate: defaultDate,
                        yearRange: selectedYear + ':' + selectedYear
                    });

                    $('#from_date_new, #to_date_new').val('');
                });

                $('#from_date_new, #to_date_new').on('focus', function () {
                    const year = $('#year').val();
                    if (!year) return;

                    $(this).datepicker('setDate', new Date(year, 0, 1));
                    $(this).datepicker('setDate', null);
                });*/




                $('#filterReset').on('click', function() {
                    resetFilters();
                    reloadAllCharts(false);
                    loadClaimSummary(false);
                });

                $('#filterSearch').on('click', function() {
                    reloadAllCharts(true);
                    loadClaimSummary(true);
                });

                if (!isCA) {
                    reloadAllCharts(false);
                } else {
                    loadClaimSummary(false);
                }

                $('#claims-tab').on('shown.bs.tab', function() {
                    // resetFilters();
                    if (!$('#claim-summary').hasClass('loaded')) {

                        loadClaimSummary(false);
                        $('#claim-summary').addClass('loaded');
                    }
                });

                $('#dashboard-tab').on('shown.bs.tab', function() {
                    //resetFilters();
                    reloadAllCharts(false);
                });

                function resetFilters() {
                    // $('#year').val('');
                    // if ($.fn.datepicker) {
                    //     $('#from_date_new, #to_date_new').datepicker('setDate', new Date());
                    // } else {
                    //     $('#from_date_new, #to_date_new').val(formattedDate);
                    // }

                    $('#year').val('');
                    $('#from_date_new').val('');
                    $('#to_date_new').val('');
                }
            });

            /*function applyYearRestriction() {
                const selectedYear = $('#year').val();

                if (!selectedYear) {
                    $('#from_date_new, #to_date_new').datepicker('option', {
                        yearRange: 'c-10:c+10'
                    });
                    return;
                }

                const defaultDate = new Date(selectedYear, 0, 1);

                $('#from_date_new, #to_date_new').datepicker('option', {
                    defaultDate: defaultDate,
                    yearRange: selectedYear + ':' + selectedYear
                });

                $('#from_date_new, #to_date_new').val('');
            }


            $('#year').on('change', function () {
                applyYearRestriction();
            });*/




            function getFilterParams(isDateSearch = false, isState = false) {
                let year = $('#year').val();
                let from = $('#from_date_new').val();
                let to = $('#to_date_new').val();
                let params = {
                    year: '',
                    from_date_new: '',
                    to_date_new: ''
                };

                if (year == '') {
                    params = {
                        year: '',
                        from_date_new: '',
                        to_date_new: '',
                        'type': 1
                    };
                }

                //alert();

                //  DATE SEARCH MODE
                if (isDateSearch && from && to) {
                    params.from_date_new = from;
                    params.to_date_new = to;
                    return params;
                }

                //  YEAR MODE
                if (year) {
                    if (isState) params.syear = year;
                    else params.year = year;
                }


                return params;
            }


            function reloadAllCharts(isDateSearch = false) {

                getMappingMsmeCount(isDateSearch);
                getMsmeStateWiseCount(isDateSearch);
                getMsmeCategoryCountOnboarded(isDateSearch);
                getMsmePercetageByGender(isDateSearch);
                topPerformerSnpBarChart(isDateSearch);
                reloadDashboardCards(isDateSearch);
                loadClaimSummary(isDateSearch);
            }


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
                        updateDashboardCards(response);
                    },
                    error: function(xhr) {
                        console.error('Error reloading cards:', xhr.responseText);
                    },
					complete: function() {
						$("#ajax-loader").hide();
					}
                });
            }



            function updateDashboardCards(data) {
                //console.log(data.bnp.total_verified_sbl);

                $('.registered_msme_total').text(data.registeredMsmeCounts.total_msme || 0);
                $('.total_msme_open').text(data.msmeOpenCounts.total_msme_open || 0);
                $('.choosen_msme_counts_totals').text(data.msmechoosenCounts.total.chossen || 0);
                $('.msme_counts_total_onboarded').text(data.msmeOnboardedCounts.total.onboarded || 0);


                function populateUl(ulId, activities, key) {
                    const ul = $(ulId);
                    ul.empty();
                    if (activities) {
                        $.each(activities, function(activity, counts) {
                            let value = counts;
                            if (key && typeof counts === 'object') {
                                value = counts[key] != null ? counts[key] : 0;
                            } else if (key == null) {
                                value = counts != null ? counts : 0;
                            }
                            ul.append(`<li><span>${activity}</span><span>${value}</span></li>`);
                        });
                    }
                }

                populateUl('#ul_registered_counts_major_activities', data.registeredMsmeCounts.major_activities, 'total_msme');
                populateUl('#ul_msmeCounts_major_activities', data.msmeOpenCounts.major_activities, 'total_msme_open');
                populateUl('#ul_major_choosen_msme_counts_totals', data.msmechoosenCounts.major_activities, 'chossen');
                populateUl('#ul_major_msme_counts_total_onboarded', data.msmeOnboardedCounts.major_activities, 'onboarded');

                //$('.msmeCounts_total_open').text(data.msmeOpenCounts.total.option1 || 0);
                //$('.msmeCounts_total_option2').text(data.msmeCounts.total.option2 || 0);
                //$('.msmeCounts_total_onboarded').text(data.msmeCounts.total.onboarded || 0);

                /*$('.snpCount_totalSnp').text(data.np.total_registered_nps || 0);
                $('.snpCount_pendingSnp').text(data.np.total_pending_nps || 0);
                $('.snpCount_approvedSnp').text(data.np.total_verified_nps || 0);
                $('.snpCount_rejectedSnp').text(data.np.total_rejected_nps || 0);
                $('.snpCount_revertedSnp').text(data.np.total_reverted_nps || 0);

                if (data.np.total_registered_nps > 0) {
                    $('.snp-card .explore-link').show();
                } else {
                    $('.snp-card .explore-link').hide();
                }*/
				
				$('.snpCount_totalSnp').text(data.snp.total_registered_sbl || 0);
                $('.snpCount_pendingSnp').text(data.snp.total_pending_sbl || 0);
                $('.snpCount_approvedSnp').text(data.snp.total_verified_sbl || 0);
                $('.snpCount_rejectedSnp').text(data.snp.total_rejected_sbl || 0);
                $('.snpCount_revertedSnp').text(data.snp.total_reverted_sbl || 0);

                if (data.snp.total_registered_sbl > 0) {
                    $('.snp-card .explore-link').show();
                } else {
                    $('.snp-card .explore-link').hide();
                }
				
				
				$('.bnpCount_totalBnp').text(data.bnp.total_registered_sbl || 0);
                $('.bnpCount_pendingBnp').text(data.bnp.total_pending_sbl || 0);
                $('.bnpCount_approvedBnp').text(data.bnp.total_verified_sbl || 0);
                $('.bnpCount_rejectedBnp').text(data.bnp.total_rejected_sbl || 0);
                $('.bnpCount_revertedBnp').text(data.bnp.total_reverted_sbl || 0);

                if (data.bnp.total_registered_sbl > 0) {
                    $('.bnp-card .explore-link').show();
                } else {
                    $('.bnp-card .explore-link').hide();
                }
				
				$('.lspCount_totalLsp').text(data.lsp.total_registered_sbl || 0);
                $('.lspCount_pendingLsp').text(data.lsp.total_pending_sbl || 0);
                $('.lspCount_approvedLsp').text(data.lsp.total_verified_sbl || 0);
                $('.lspCount_rejectedLsp').text(data.lsp.total_rejected_sbl || 0);
                $('.lspCount_revertedLsp').text(data.lsp.total_reverted_sbl || 0);

                if (data.lsp.total_registered_sbl > 0) {
                    $('.lsp-card .explore-link').show();
                } else {
                    $('.lsp-card .explore-link').hide();
                }
				
				
				$('.ia_count_total').text(data.ia.total_registered_ia || 0);
                $('.ia_count_pending').text(data.ia.total_pending_ia || 0);
                $('.ia_count_approved').text(data.ia.total_verified_ia || 0);
                $('.ia_count_rejected').text(data.ia.total_rejected_ia || 0);

                if (data.ia.total_registered_ia > 0) {
                    $('.ia-card .explore-link').show();
                } else {
                    $('.ia-card .explore-link').hide();
                }


                /*$('.bnpCount_totalBnp').text(data.bnpCount.totalBnp || 0);
                $('.bnpCount_pendingBnp').text(data.bnpCount.pendingBnp || 0);
                $('.bnpCount_approvedBnp').text(data.bnpCount.approvedBnp || 0);
                $('.bnpCount_rejectedBnp').text(data.bnpCount.rejectedBnp || 0);
                $('.bnpCount_revertedBnp').text(data.bnpCount.revertedBnp || 0);

                if (data.bnpCount.totalBnp > 0) {
                   $('.bnp-card .explore-link').show();
                } else {
                   $('.bnp-card .explore-link').hide();
                } */

                $('.claimCounts_totalClaims').text(data.claimCounts.totalClaims || 0);
                $('.claimCounts_approvedCount').text(data.claimCounts.approvedCount || 0);
                $('.claimCounts_revertedCount').text(data.claimCounts.revertedCount || 0);
                $('.claimCounts_pendingCount').text(data.claimCounts.pendingCount || 0);
                $('.claimCounts_rejectedCount').text(data.claimCounts.rejectedCount || 0);

                $('.totalCliamCounts').text(0);
                $('.claimsApprovedClaimsCount').text(0);
                $('.claimCountsRevertedCount').text(0);
                $('.claimsPendingCounts').text(0);
                $('.claimRejectedCounts').text(0);
                $('.claimCountsDraftCount').text(0);
                $('.claimCountsPaymentCompleted').text(0);

                if (data.claimCounts.totalClaims > 0) {
                    $('.claim-card .explore-link').show();
                } else {
                    $('.claim-card .explore-link').hide();
                }
            }



            function getMappingMsmeCount(isDateSearch = false) {
                $.ajax({
                    url: "{{ url('get-mapping-msme-count') }}",
                    type: "GET",
                    data: getFilterParams(isDateSearch),
                    success: getMappingMsmeCountBarGraph,
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            function getMappingMsmeCountBarGraph(response) {
                Highcharts.chart('bargraph_msme_onboarded', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'MSME Count'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormat: '<b>{point.y}</b> MSMEs'
                    },
                    series: [{
                        name: 'MSME Count',
                        colorByPoint: true,
                        groupPadding: 0,
                        data: response
                    }]
                });
            }


            function getMsmeStateWiseCount(isDateSearch = false) {
                $.ajax({
                    url: "{{ url('get-msme-state-wise-count') }}",
                    type: "GET",
                    data: getFilterParams(isDateSearch, true),
                    success: getMsmeStateWiseCountBarGraph,
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            function getMsmeStateWiseCountBarGraph(response) {
                Highcharts.chart('bargraph_msme_state_wise_onboarded', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'MSME Count'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormat: '<b>{point.y}</b> MSMEs'
                    },
                    series: [{
                        name: 'MSME Count',
                        colorByPoint: true,
                        groupPadding: 0,
                        data: response
                    }]
                });
            }

            function getMsmeCategoryCountOnboarded(isDateSearch = false) {
                $.ajax({
                    url: "{{ url('get-msme-category-count-onboarded') }}",
                    type: "GET",
                    data: getFilterParams(isDateSearch),
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: getMsmeCategoryCountOnboardedPiechart,
                    error: function(xhr) {
                        console.error("Error fetching category data:", xhr.responseText);
                    }
                });
            }

            function getMsmeCategoryCountOnboardedPiechart(response) {
                const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

                Highcharts.chart('pieChart', {
                    chart: {
                        type: 'pie',
                        events: {
                            render() {
                                const chart = this,
                                    series = chart.series[0];
                                if (!chart.customLabel) {
                                    chart.customLabel = chart.renderer.label('').css({
                                        textAnchor: 'middle'
                                    }).add();
                                }
                                chart.customLabel.attr({
                                    text: `Total<br/><strong>${totalCount}</strong>`,
                                    x: series.center[0] + chart.plotLeft,
                                    y: series.center[1] + chart.plotTop - 10
                                }).css({
                                    fontSize: `${series.center[2] / 12}px`
                                });
                            }
                        }
                    },
                    title: {
                        text: ""
                    },
                    tooltip: {
                        pointFormat: '<b>{point.count}</b> ({point.percentage:.1f}%)'
                    },
                    plotOptions: {
                        series: {
                            innerSize: '75%',
                            borderRadius: 8,
                            dataLabels: [{
                                    enabled: true,
                                    distance: 15,
                                    format: '{point.name}'
                                },
                                {
                                    enabled: true,
                                    distance: -20,
                                    format: '{point.percentage:.0f}%'
                                }
                            ]
                        }
                    },
                    series: [{
                        name: 'Categories',
                        colorByPoint: true,
                        data: response.data
                    }]
                });
            }


            function getMsmePercetageByGender(isDateSearch = false) {
                $.ajax({
                    url: "{{ url('get-msme-gender-percentege') }}",
                    type: "GET",
                    data: getFilterParams(isDateSearch),
                    dataType: "json",
                    success: getMsmePercentageGenderWisePiechart,
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            function getMsmePercentageGenderWisePiechart(response) {
                const chartData = response.data.map(item => ({
                    name: item.name,
                    y: item.percentage,
                    count: item.count
                }));

                Highcharts.chart('pieChartPercentage', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: ''
                    },
                    tooltip: {
                        pointFormat: '<b>{point.y:.1f}%</b> ({point.count} MSMEs)'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: [{
                                enabled: true,
                                distance: 20
                            }, {
                                enabled: true,
                                distance: -40,
                                format: '{point.name}: {point.y:.1f}% ({point.count})',
                                style: {
                                    fontSize: '0.9em',
                                    textOutline: 'none',
                                    opacity: 0.7
                                }
                            }]
                        }
                    },
                    series: [{
                        name: 'Percentage',
                        colorByPoint: true,
                        data: chartData
                    }]
                });
            }


            function topPerformerSnpBarChart(isDateSearch = false) {
                $.ajax({
                    url: "{{ url('get-top-performer-snps') }}",
                    type: 'GET',
                    data: getFilterParams(isDateSearch),
                    success: function(response) {
                        const data = response.data || [];
                        const categories = data.map(item => item.snp_name);
                        const values = data.map(item => item.total);

                        Highcharts.chart('top-performer-snp', {
                            chart: {
                                type: 'bar',
                                backgroundColor: '#fff',
                                borderRadius: 5
                            },
                            title: {
                                text: ''
                            },
                            xAxis: {
                                categories
                            },
                            yAxis: {
                                min: 0,
                                title: null
                            },
                            legend: {
                                enabled: false
                            },
                            plotOptions: {
                                bar: {
                                    dataLabels: {
                                        enabled: true,
                                        style: {
                                            fontSize: '12px'
                                        }
                                    },
                                    borderRadius: 5
                                }
                            },
                            series: [{
                                name: 'Onboarded MSE',
                                color: '#1E88E5',
                                data: values
                            }]
                        });
                    },
                    error: function() {
                        console.error('Failed to load SNP data.');
                    }
                });
            }

            function renderClaimCards(data) {
                const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";

                let html = `<div class="row doc-cards">`;

                data.forEach((item, index) => {
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
                                            <li>
                                                <span>Approved</span>
                                                <span>${item.approvedCount}</span>
                                            </li>
											
                                            <li>
                                                <span>Pending</span>
                                                <span>${item.pendingCount}</span>
                                            </li>
                                           
                                            ${!isCA ? `<li>
                        												<span>Rejected</span>
                        												<span>${item.rejectedCount}</span>
                        											</li>` : ''}
                                            ${!isCA ? `<li>
                                                <span>Payment Completed</span>
                                                <span>${item.paymentCompletedCount}</span>
                                            </li>
                                            <li class="total_amount">
                                                <span>Total Amount</span>
                                                <span>₹${Number(item.totalAmount).toLocaleString()}</span>
                                            </li>` : ''}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
				`;
                });

                html += `</div>`;
                html += `
				<style>
					.card:hover {
						transform: translateY(-5px);
						box-shadow: 0 6px 15px rgba(0,0,0,0.1);
					}
					.common-claim-card .card {
						min-height: 180px;
						min-width: 280px;
					}
					.card h4 {
						font-size: 28px;
					}
					.small {
						font-size: 13px;
					}
					.doc-cards { margin-left: 0; margin-right: 0; }
					.onboarded-card .card { border-radius: 8px; }
					.top-header-card p { font-weight: 600; margin: 0; }
					.card-detail ul { list-style: none; padding: 0; margin: 0; }
					.card-detail ul li { display:flex; justify-content:space-between; padding:6px 0; border-bottom: 1px dashed #eee; }
					.card-detail ul li:last-child { border-bottom: none; }
				</style>
			`;

                $('#claim-summary').html(html);
            }



            // rendor claim card for ca
            function renderClaimCardForCA(data) {
                const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";
                let html = `<div class="row doc-cards">`;

                Object.entries(data).forEach(([key, item]) => {
                    const typeTitle = item.name || (item.name || '').replace(/-/g, ' ');
                    html += `
                <div class="col-lg-4 common-claim-card">
                    <div class="card w-100">
                        <div class="card-body d-flex gap-3">
                            <div class="right text-start w-100">
                                <div class="top-header-card d-flex align-items-center justify-content-between">
                                    <div class="left align-items-center icon type-3">
                                        <img src="${iconSrc}" alt="${typeTitle}">
                                    </div>
                                    <span>
                                        <p class="mb-1">${typeTitle}</p>
                                        <h5><span>${item.totalClaim}</span></h5>
                                    </span>
                                </div>

                                <div class="card-detail">
                                    <ul>
                                        <li>
                                            <span>Approved</span>
                                            <span>${item.approvedCount}</span>
                                        </li>
                                        <li>
                                            <span>Pending</span>
                                            <span>${item.pendingCount}</span>
                                        </li>
                                        ${!isCA ? `
                        										<li>
                        											<span>Rejected</span>
                        											<span>${item.rejectedCount}</span>
                        										</li>
                        										<li class="total_amount">
                        											<span>Total Amount</span>
                        											<span>₹${Number(item.totalAmount).toLocaleString()}</span>
                        										</li>` : ''}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				`;
                });

                html += `</div>`;

                html += `
            <style>
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
                }
                .card h4 {
                    font-size: 28px;
                }
                .small {
                    font-size: 13px;
                }
                .doc-cards { margin-left: 0; margin-right: 0; }
                .onboarded-card .card { border-radius: 8px; }
                .top-header-card p { font-weight: 600; margin: 0; }
                .card-detail ul { list-style: none; padding: 0; margin: 0; }
                .card-detail ul li { display:flex; justify-content:space-between; padding:6px 0; border-bottom: 1px dashed #eee; }
                .card-detail ul li:last-child { border-bottom: none; }
            </style>
			`;
                $('#claim-summary').html(html);
            }
        </script>

        @include('dashboard.dashboard-js')
    @endsection
