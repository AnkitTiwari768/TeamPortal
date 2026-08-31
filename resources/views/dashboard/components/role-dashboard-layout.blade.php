@php
    $roleIdPrefix = $roleIdPrefix ?? 'role';
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

            {{-- ── Row 1: 4 MSE Stat Cards ──────────────────────────────── --}}
            <div class="row doc-cards ondc-mse-cards mt-3 mb-4">

                {{-- Total Registered MSEs --}}
                @include('dashboard.components.mse-stat-card', [
                    'title'      => 'Total Registered MSEs',
                    'total'      => $registeredMsmeCounts['total_msme'] ?? 0,
                    'link'       => url('mis-reports-msme'),
                    'items'      => $registeredMsmeCounts['major_activities'] ?? [],
                    'itemKey'    => null,
                    'iconType'   => 'type-1',
                    'cssClass'   => 'register-mse-card',
                    'totalClass' => 'registered_msme_total',
                    'listId'     => 'ul_registered_counts_major_activities',
                    'listClass'  => 'registered_counts_major_activitie',
                ])

                {{-- Open MSEs --}}
                @include('dashboard.components.mse-stat-card', [
                    'title'      => 'Open MSEs',
                    'total'      => $msmeOpenCounts['total_msme_open'] ?? '',
                    'link'       => url('open-msme'),
                    'items'      => $msmeOpenCounts['major_activities'] ?? [],
                    'itemKey'    => null,
                    'iconType'   => 'type-2',
                    'cssClass'   => 'choosen-option-card-1',
                    'totalClass' => 'total_msme_open',
                    'listId'     => 'ul_msmeCounts_major_activities',
                    'listClass'  => 'msmeCounts_major_activities',
                ])

                {{-- Direct Selection By MSE --}}
                @include('dashboard.components.mse-stat-card', [
                    'title'      => 'Direct Selection By MSE',
                    'total'      => $msmechoosenCounts['total']['chossen'] ?? '',
                    'link'       => url('msme-chossen-me'),
                    'items'      => $msmechoosenCounts['major_activities'] ?? [],
                    'itemKey'    => 'chossen',
                    'iconType'   => 'type-3',
                    'cssClass'   => 'choosen-option-card-2',
                    'totalClass' => 'choosen_msme_counts_totals',
                    'listId'     => 'ul_major_choosen_msme_counts_totals',
                    'listClass'  => 'major_choosen_msme_counts_totals',
                ])

                {{-- Total Onboarded MSEs --}}
                @include('dashboard.components.mse-stat-card', [
                    'title'      => 'Total Onboarded MSEs',
                    'total'      => $msmeOnboardedCounts['total']['onboarded'] ?? '',
                    'link'       => url('onboarded-msme'),
                    'items'      => $msmeOnboardedCounts['major_activities'] ?? [],
                    'itemKey'    => 'onboarded',
                    'iconType'   => 'type-4',
                    'cssClass'   => 'onboarded-card',
                    'totalClass' => 'msme_counts_total_onboarded',
                    'listId'     => 'ul_major_msme_counts_total_onboarded',
                    'listClass'  => 'major_msme_counts_total_onboarded',
                ])

            </div>{{-- /Row 1 --}}

            {{-- ── Row 2: Network Participants (NP) Cards ───────────────── --}}
            <h6 class="fw-bold mt-4">Network Participants (NP)</h6>

            
            <div class="row g-4 mb-4">

                {{-- SNP --}}
                @include('dashboard.components.np-registration-card', [
                    'title'      => 'Seller Network Participants (SNP)',
                    'cardClass'  => 'snp-card',
                    'total'      => $snpCount['total']   ?? 0,
                    'totalClass' => 'role_snp_total',
                    'stats'      => [
                        ['label' => 'Verification Pending', 'value' => $snpCount['pending']  ?? 0, 'class' => 'role_snp_pending'],
                        ['label' => 'Verified SNP',         'value' => $snpCount['verified'] ?? 0, 'class' => 'role_snp_verified'],
                        ['label' => 'Rejected SNP',         'value' => $snpCount['rejected'] ?? 0, 'class' => 'role_snp_rejected'],
                        ['label' => 'Reverted SNP',         'value' => $snpCount['reverted'] ?? 0, 'class' => 'role_snp_reverted'],
                    ],
                ])

                {{-- BNP --}}
                @include('dashboard.components.np-registration-card', [
                    'title'      => 'Buyer Network Participants (BNP)',
                    'cardClass'  => 'bnp-card',
                    'total'      => $bnpCount['total']   ?? 0,
                    'totalClass' => 'role_bnp_total',
                    'stats'      => [
                        ['label' => 'Verification Pending', 'value' => $bnpCount['pending']  ?? 0, 'class' => 'role_bnp_pending'],
                        ['label' => 'Verified BNP',         'value' => $bnpCount['verified'] ?? 0, 'class' => 'role_bnp_verified'],
                        ['label' => 'Rejected BNP',         'value' => $bnpCount['rejected'] ?? 0, 'class' => 'role_bnp_rejected'],
                        ['label' => 'Reverted BNP',         'value' => $bnpCount['reverted'] ?? 0, 'class' => 'role_bnp_reverted'],
                    ],
                ])

                {{-- LSP --}}
                @include('dashboard.components.np-registration-card', [
                    'title'      => 'Logistics Service Providers (LSP)',
                    'cardClass'  => 'claim-card',
                    'total'      => $lspCount['total']   ?? 0,
                    'totalClass' => 'role_lsp_total',
                    'stats'      => [
                        ['label' => 'Verification Pending', 'value' => $lspCount['pending']  ?? 0, 'class' => 'role_lsp_pending'],
                        ['label' => 'Verified LSP',         'value' => $lspCount['verified'] ?? 0, 'class' => 'role_lsp_verified'],
                        ['label' => 'Rejected LSP',         'value' => $lspCount['rejected'] ?? 0, 'class' => 'role_lsp_rejected'],
                        ['label' => 'Reverted LSP',         'value' => $lspCount['reverted'] ?? 0, 'class' => 'role_lsp_reverted'],
                    ],
                ])

                {{-- Associations --}}
                @include('dashboard.components.np-registration-card', [
                    'title'      => 'Association Management',
                    'cardClass'  => 'snp-card',
                    'total'      => $associationsCount['total']   ?? 0,
                    'totalClass' => 'role_assoc_total',
                    'stats'      => [
                        ['label' => 'Verification Pending',  'value' => $associationsCount['pending']  ?? 0, 'class' => 'role_assoc_pending'],
                        ['label' => 'Verified Associations', 'value' => $associationsCount['verified'] ?? 0, 'class' => 'role_assoc_verified'],
                        ['label' => 'Rejected Associations', 'value' => $associationsCount['rejected'] ?? 0, 'class' => 'role_assoc_rejected'],
                    ],
                ])
            </div>{{-- /NP Row --}}

               <h6 class="fw-bold mt-4">Fund Flow</h6>


              {{-- ── Fund Management Widgets (Visible if user has permissions) ── --}}
                @if (acl(config('permissions.allocation-view')) || acl(config('permissions.fund-distribution-view')))
                    @include('components.dashboard.fund-management-cards')
                @endif

            {{-- ── Charts (same layout as administrator.blade.php) ─────────── --}}
            <div class="row mb-4">
                <div class="col-lg-7 mb-3">
                    @include('dashboard.msme-category-pie-chart', [
                        'url' => url('admin-dashboard-msme-category-count-onboarded'),
                    ])
                </div>
                <div class="col-lg-5 mb-3">
                    @include('dashboard.top-performer-nps-chart', [
                        'url' => url('admin-dashboard-top-performer-nps'),
                    ])
                </div>
                <div class="col-lg-12 mb-3">
                    @include('dashboard.msme-gender-percentage-chart', [
                        'url' => url('admin-dashboard-msme-gender-percentage'),
                    ])
                </div>
                <div class="row mb-4">
                    <div class="col-lg-12 mb-3">
                        @include('dashboard.msme-state-wise-bar-chart', [
                            'url' => url('admin-dashboard-state-wise-msme-count'),
                        ])
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-12 mb-3">
                    @include('dashboard.registered-vs-onboarded-monthly-chart', [
                        'url' => url('admin-dashboard-registered-vs-onboarded-monthly'),
                    ])
                </div>
            </div>

        </div>{{-- /#role-dashboard --}}

        {{-- ── Claim Dashboard Tab ─────────────────────────────────────── --}}
        <div class="tab-pane fade" id="{{ $roleIdPrefix }}-claims"
             role="tabpanel" aria-labelledby="{{ $roleIdPrefix }}-claims-tab">

            <div class="row mt-3">
                <div class="col-lg-12">
                    <h1 class="page-title">Claim Management </h1>
                </div>
            </div>

            <div class="col-lg-12 mb-4 mt-3">
                <div id="claim-summary" class="stat-row"></div>
            </div>

              @include('dashboard.components.claims-dashboard-cards')

            <!-- @include('dashboard.claim-status-card') -->

        </div>{{-- /#role-claims --}}

    </div>{{-- /.tab-content --}}
</div>{{-- /.container-fluid --}}
