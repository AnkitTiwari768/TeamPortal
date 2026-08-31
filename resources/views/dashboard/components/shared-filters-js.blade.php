<script>
    // ── Filter helpers ─────────────────────────────────────────────────────
    /**
     * Build filter params for AJAX calls.
     */
    window.getFilterParams = function (isDateSearch = false, isState = false) {
        const year = $('#year').val();
        const from = $('#from_date_new').val();
        const to   = $('#to_date_new').val();

        // Default: all-time
        let params = { year: '', from_date_new: '', to_date_new: '', type: 1 };

        // Date range takes priority
        if (isDateSearch && from && to) {
            params.from_date_new = from;
            params.to_date_new   = to;
            params.type          = 2;  // not all-time
            return params;
        }

        // Specific year selected
        if (year) {
            params.type = 2;           // triggers year WHERE in service
            if (isState) params.syear = year;
            else         params.year  = year;
        }

        return params;
    };

    // ── Chart reload orchestration ─────────────────────────────────────────
    window.reloadAllCharts = function (isDateSearch = false) {
        if (typeof getMsmeCategoryCountOnboarded === 'function') getMsmeCategoryCountOnboarded(isDateSearch);
        if (typeof getMsmeStateWiseCount === 'function') getMsmeStateWiseCount(isDateSearch);
        if (typeof getMsmePercetageByGender === 'function') getMsmePercetageByGender(isDateSearch);
        if (typeof getTopPerformerNpsChart === 'function') getTopPerformerNpsChart(isDateSearch);
        if (typeof getRegisteredVsOnboardedMonthly === 'function') getRegisteredVsOnboardedMonthly(isDateSearch);
        
        reloadDashboardCards(isDateSearch);

        if ($('.fund-card').length) {
            reloadFundManagementMetrics(isDateSearch);
        }
    };

    // ── Card AJAX reload ───────────────────────────────────────────────────
    function reloadDashboardCards(isDateSearch = false) {
        var params = window.getFilterParams(isDateSearch);
        $.ajax({
            url:      "{{ url('/dashboard') }}",
            type:     'GET',
            dataType: 'json',
            data:     params,
            beforeSend: function () { $('#ajax-loader').show(); },
            success:  function (data) { updateDashboardCards(data); },
            error:    function (xhr)  { console.error('Card reload error:', xhr.responseText); },
            complete: function ()     { $('#ajax-loader').hide(); },
        });
    }

    function reloadFundManagementMetrics(isDateSearch = false) {
        var params = window.getFilterParams(isDateSearch);
        $.ajax({
            url:      "{{ url('/get-fund-management-metrics') }}",
            type:     'GET',
            dataType: 'json',
            data:     params,
            success:  function (data) {
                // Formatting helper for currency in Indian Rupees format (en-IN)
                const formatCurrency = (val) => {
                    return Number(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                };

                // 1. Update Totals
                $('#allocated_total').text(formatCurrency(data.allocated.current));
                $('#distributed_total').text(formatCurrency(data.distributed.current));
                $('#remaining_total').text(formatCurrency(data.remaining.current));

                // 2. Update Trends
                updateTrendBadge('#allocated_trend', data.allocated);
                updateTrendBadge('#distributed_trend', data.distributed);

                // 3. Update Utilization and Remaining Subtext
                $('#utilization_percent').text(data.utilization_percent + '% Utilized');

                // 4. Update Previous Period text
                $('#allocated_prev_period').text(data.prev_period_string);
                $('#distributed_prev_period').text(data.prev_period_string);
                $('#remaining_prev_period').text(data.prev_period_string);
            },
            error:    function (xhr)  { console.error('Fund metrics reload error:', xhr.responseText); }
        });
    }

    function updateTrendBadge(selector, metric) {
        const badge = $(selector);
        if (!badge.length) return;
        
        badge.removeClass('trend-up trend-down trend-neutral');
        
        let arrow = '';
        if (metric.direction === 'up') {
            badge.addClass('trend-up');
            arrow = '▲ ';
        } else if (metric.direction === 'down') {
            badge.addClass('trend-down');
            arrow = '▼ ';
        } else {
            badge.addClass('trend-neutral');
        }

        badge.find('span').text(arrow + metric.trend + '%');
    }

    function updateDashboardCards(data) {
        // ── MSE Stat Cards ──────────────────────────────────────────────────
        $('.registered_msme_total').text(data.registeredMsmeCounts?.total_msme || 0);
        $('.total_msme_open').text(data.msmeOpenCounts?.total_msme_open || 0);
        $('.choosen_msme_counts_totals').text(
            (data.msmechoosenCounts?.total && data.msmechoosenCounts.total.chossen) || 0
        );
        $('.msme_counts_total_onboarded').text(
            (data.msmeOnboardedCounts?.total && data.msmeOnboardedCounts.total.onboarded) || 0
        );

        populateUl('#ul_registered_counts_major_activities', data.registeredMsmeCounts?.major_activities, null);
        populateUl('#ul_msmeCounts_major_activities',         data.msmeOpenCounts?.major_activities,       null);
        populateUl('#ul_major_choosen_msme_counts_totals',    data.msmechoosenCounts?.major_activities,    'chossen');
        populateUl('#ul_major_msme_counts_total_onboarded',   data.msmeOnboardedCounts?.major_activities,  'onboarded');

        // ── NP Cards ────────────────────────────────────────────────────────
        if (data.snpCount) {
            $('.role_snp_total').text(data.snpCount.total || 0);
            $('.role_snp_pending').text(data.snpCount.pending || 0);
            $('.role_snp_verified').text(data.snpCount.verified || 0);
            $('.role_snp_rejected').text(data.snpCount.rejected || 0);
            $('.role_snp_reverted').text(data.snpCount.reverted || 0);
        }
        if (data.bnpCount) {
            $('.role_bnp_total').text(data.bnpCount.total || 0);
            $('.role_bnp_pending').text(data.bnpCount.pending || 0);
            $('.role_bnp_verified').text(data.bnpCount.verified || 0);
            $('.role_bnp_rejected').text(data.bnpCount.rejected || 0);
            $('.role_bnp_reverted').text(data.bnpCount.reverted || 0);
        }
        if (data.lspCount) {
            $('.role_lsp_total').text(data.lspCount.total || 0);
            $('.role_lsp_pending').text(data.lspCount.pending || 0);
            $('.role_lsp_verified').text(data.lspCount.verified || 0);
            $('.role_lsp_rejected').text(data.lspCount.rejected || 0);
            $('.role_lsp_reverted').text(data.lspCount.reverted || 0);
        }
        if (data.associationsCount) {
            $('.role_assoc_total').text(data.associationsCount.total || 0);
            $('.role_assoc_pending').text(data.associationsCount.pending || 0);
            $('.role_assoc_verified').text(data.associationsCount.verified || 0);
            $('.role_assoc_rejected').text(data.associationsCount.rejected || 0);
        }
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

    // ── Claim summary (Claim tab) ──────────────────────────────────────────
    function loadClaimSummary(isDateSearch = false) {
        var params = window.getFilterParams(isDateSearch);
        $.ajax({
            url:  "{{ url('get-claim-counts-by-status') }}",
            type: 'GET',
            data: params,
            success: function (response) {
                if (response.success) { renderClaimCards(response.data); }
            },
            error: function (xhr) {
                console.error('Claim summary error:', xhr.responseText);
            },
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

    // ── Bootstrap ─────────────────────────────────────────────────────────
    $(document).ready(function () {

        const tabPrefix = "{{ $tabPrefix ?? 'role' }}";

        $('#filterSearch').on('click', function () {
            window.reloadAllCharts(true);
            loadClaimSummary(true);
        });

        $('#filterReset').on('click', function () {
            $('#year').val('');
            $('#from_date_new').val('');
            $('#to_date_new').val('');
            window.reloadAllCharts(false);
            loadClaimSummary(false);
        });

        $('#year').on('change', function () {
            window.reloadAllCharts(false);
        });

        $(`#${tabPrefix}-claims-tab`).on('shown.bs.tab', function () {
            if (!$('#claim-summary').hasClass('loaded')) {
                loadClaimSummary(false);
                $('#claim-summary').addClass('loaded');
            }
        });

        $(`#${tabPrefix}-dashboard-tab`).on('shown.bs.tab', function () {
            window.reloadAllCharts(false);
        });

        // Initial load
        window.reloadAllCharts(false);
    });
</script>
