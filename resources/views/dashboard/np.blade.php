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
    <div class="container-fluid p-4">

        @include('dashboard.search')
        <div class="row mt-3">
            <div class="col-lg-12">
                <h1 class="page-title">
                    @if(hasRole('lsp'))
                        Logistics And Transportation Claims
                    @else
                        Demand Generation Claims
                    @endif
                </h1>
            </div>
        </div>

        <div class="col-lg-12 mb-4 mt-3">
            <div id="claim-summary" class="stat-row"></div>
        </div>

        <div class="col-lg-12 mb-4">
            <div class="stat-row row">
                <div class="col-lg-3">
                    <div class="stat-card shadow-sm" style="background: #e9eaff;">
                        <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                        <h6 class="mt-2">Total Claims Submitted</h6>
                        <h4 class="fw-bold text-primary claimCounts_totalClaims">
                            0</h4>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                        <i class="bi bi-clock-history text-warning fs-3"></i>
                        <h6 class="mt-2">Total Claims Pending for Verification </h6>
                        <h4 class="fw-bold text-warning claimCounts_pendingCount">
                            0</h4>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="stat-card shadow-sm" style="background: #e0fbe5;">
                        <i class="bi bi-check2-circle text-success fs-3"></i>
                        <h6 class="mt-2">Total Approved Claims</h6>
                        <h4 class="fw-bold text-success claimCounts_approvedCount">
                            0</h4>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card shadow-sm " style="background:#fff2f2;">
                        <i class="bi bi-x-circle text-danger fs-3"></i>
                        <h6 class="mt-2">Total Rejected Claims</h6>
                        <h4 class="fw-bold text-danger claimCounts_rejectedCount">
                            0</h4>
                    </div>
                </div>



                <div class="col-lg-3">
                    <div class="stat-card shadow-sm" style="background:#f3fff6;">
                        <i class="bi bi-check2-circle text-success fs-3"></i>
                        <h6 class="mt-2">Total Payment Completed Claims</h6>
                        <h4 class="fw-bold text-success claimCounts_paymentCompletedCount">
                            0</h4>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card shadow-sm " style="background:#eef6ff;">
                        <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                        <h6 class="mt-2">Total Amount Claimed</h6>
                        <h4 class="fw-bold text-primary claimCounts_draftCount">
                            0</h4>
                    </div>
                </div>

            </div>
        </div>
    </div>



    @section('js')
        @include('dashboard.datepicker')
        <script>

            // ✅ ROLE PASS
            const userRole = "{{ hasRole('lsp') ? 'lsp' : (hasRole('bnp') ? 'bnp' : '') }}";


            // Bridge for search.blade.php year dropdown onchange="reloadAllCharts(false)"
            function reloadAllCharts(isDateSearch = false) {
                applyYearRestriction(true);
                LoadLogisticsClaims(isDateSearch);
            }

            $(document).ready(function () {
                LoadLogisticsClaims();

                $('#filterSearch').on('click', function () {
                    LoadLogisticsClaims(true);
                });
                // Handle filter reset button click
                $('#filterReset').on('click', function () {
                    resetFilters();
                    LoadLogisticsClaims(false);
                });
            });

            function resetFilters() {
                $('#year').val('');
                $('#from_date_new').val('');
                $('#to_date_new').val('');


            }

            function getFilterParams(isDateSearch = false) {
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
                        type: 1
                    };
                }

                if (isDateSearch && from && to) {
                    params.from_date_new = from;
                    params.to_date_new = to;
                    return params;
                }

                // YEAR MODE
                if (year) {
                    params.year = year;
                }

                return params;
            }

            function LoadLogisticsClaims(isDateSearch = false) {
                const params = getFilterParams(isDateSearch);

                $.ajax({
                    url: "{{url('get-claim-counts-by-status') }}",
                    type: "GET",
                    data: params,
                    dataType: "Json",
                    beforeSend: function () {
                        $("#ajax-loader").show();
                    },
                    success: function (response) {
                        if (response.success) {
                            const logisticData = findLogisticsClaims(response.data);

                            if (logisticData) {
                                updateStatCards(logisticData);
                                renderLogisticsCard(logisticData);
                            } else {
                                const emptyData = {
                                    totalClaims: 0,
                                    pendingCount: 0,
                                    approvedCount: 0,
                                    rejectedCount: 0,
                                    paymentCompletedCount: 0,
                                    totalAmount: 0
                                };
                                updateStatCards(emptyData);
                                renderLogisticsCard(emptyData);
                                $('#claim-summary').html('<p class="text-muted">No logistics claims data available.</p>');
                            }
                        } else {
                            $('#claim-summary').html('<p class="text-muted">No claim data available.</p>');
                        }
                    },

                    error: function (xhr) {
                        console.error("Error fetching claim summary:", xhr.responseText);
                        $('#claim-summary').html('<p class="text-muted">Error loading data.</p>');
                    },
                    complete: function () {
                        $("#ajax-loader").hide();
                    }
                });

            }
            // function findLogisticsClaims(data) {
            //     if (!data || !Array.isArray(data)) return null;

            //     // Find the claim type that matches logistics/transportation
            //     return data.find(item => 
            //         item.claimType === 'claim-for-transportation-and-logistic' || 
            //         item.claimType?.includes('transport') || 
            //         item.claimType?.includes('logistic')
            //     );
            // }

            function findLogisticsClaims(data) {
                if (!data || !Array.isArray(data)) return null;

                // ✅ LSP → Logistics
                if (userRole === 'lsp') {
                    return data.find(item =>
                        item.claimType === 'claim-for-transportation-and-logistic'
                    );
                }

                // ✅ BNP → Demand Generation
                if (userRole === 'bnp') {
                    return data.find(item =>
                        item.claimType === 'claim-for-demand-generation'
                    );
                }

                return null;
            }

            function updateStatCards(data) {
                const totalClaimsSubmitted = (data.totalClaims || data.totalClaim || 0) - (data.totalDraft || 0);
                $('.claimCounts_totalClaims').text(totalClaimsSubmitted);
                $('.claimCounts_pendingCount').text(data.pendingCount || 0);
                $('.claimCounts_approvedCount').text(data.approvedCount || 0);
                $('.claimCounts_rejectedCount').text(data.rejectedCount || 0);
                $('.claimCounts_paymentCompletedCount').text(data.paymentCompletedCount || 0);
                $('.claimCounts_draftCount').text('₹' + (data.totalAmount || 0).toLocaleString());
            }

            function renderLogisticsCard(data) {

                const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";

                //  let html = `
                //         <div class="row doc-cards">
                //             <div class="col-lg-12">
                //                 <div class="card w-100 logistics-claim-card">
                //                     <div class="card-body">
                //                         <div class="row">
                //                             <div class="col-md-12">
                //                                 <div class="top-header-card d-flex align-items-center justify-content-between mb-3">
                //                                     <div class="d-flex align-items-center gap-3">
                //                                         <div class="left align-items-center icon type-3">
                //                                             <img src="${iconSrc}" alt="Logistics Claims">
                //                                         </div>
                //                                         <h4 class="mb-0">Logistics & Transportation Claims</h4>
                //                                     </div>
                //                                 </div>

                //                                 <div class="row mt-4">
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #e9eaff; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Total Claims</h6>
                //                                             <h3 class="fw-bold text-primary mb-0">${data.totalClaims || 0}</h3>
                //                                         </div>
                //                                     </div>
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #fff7f0; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Pending</h6>
                //                                             <h3 class="fw-bold text-warning mb-0">${data.pendingCount || 0}</h3>
                //                                         </div>
                //                                     </div>
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #e0fbe5; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Approved</h6>
                //                                             <h3 class="fw-bold text-success mb-0">${data.approvedCount || 0}</h3>
                //                                         </div>
                //                                     </div>
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #fff2f2; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Rejected</h6>
                //                                             <h3 class="fw-bold text-danger mb-0">${data.rejectedCount || 0}</h3>
                //                                         </div>
                //                                     </div>
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #f3fff6; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Payment Completed</h6>
                //                                             <h3 class="fw-bold text-success mb-0">${data.paymentCompletedCount || 0}</h3>
                //                                         </div>
                //                                     </div>
                //                                     <div class="col-md-4 mb-3">
                //                                         <div class="stat-card-small p-3" style="background: #eef6ff; border-radius: 8px;">
                //                                             <h6 class="text-muted mb-2">Total Amount</h6>
                //                                             <h3 class="fw-bold text-primary mb-0">₹${(data.totalAmount || 0).toLocaleString()}</h3>
                //                                         </div>
                //                                     </div>
                //                                 </div>
                //                             </div>
                //                         </div>
                //                     </div>
                //                 </div>
                //             </div>
                //         </div>
                //     `;

                //     // Add some custom styles
                //     html += `
                //         <style>
                //             .logistics-claim-card {
                //                 border-radius: 12px;
                //                 box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                //                 transition: transform 0.2s;
                //             }
                //             .logistics-claim-card:hover {
                //                 transform: translateY(-2px);
                //                 box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                //             }
                //             .stat-card-small {
                //                 transition: all 0.2s;
                //             }
                //             .stat-card-small:hover {
                //                 transform: scale(1.02);
                //             }
                //         </style>
                //     `;

                //     $('#claim-summary').html(html);

            }
        </script>
    @endsection
@endsection