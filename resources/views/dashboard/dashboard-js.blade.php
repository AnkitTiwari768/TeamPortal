<script>
    function loadClaimSummary(isDateSearch = false) {
        const params = getFilterParams(isDateSearch);
        $.ajax({
            url: "{{ url('get-claim-counts-by-status') }}",
            type: "GET",
            data: params,
            dataType: "json",
            beforeSend: function () {
                $("#ajax-loader").show();
            },
            success: function (response) {
                if (response.success) {
                    if (response.type_for == 1) {
                        // renderClaimCardForCA(response.data);
                        renderClaimCards(response.data);
                    } else {
                        renderClaimCards(response.data);
                        updateAggregatedClaims(response.all);
                    }

                } else {
                    $('#claim-summary').html('<p class="text-muted">No claim data available.</p>');
                }
            },
            error: function (xhr) {
                console.error("Error fetching claim summary:", xhr.responseText);
            },
            complete: function () {
                $("#ajax-loader").hide();
            }
        });
    }

    function updateAggregatedClaims(all) {
        $(".claimCounts_totalClaims").text(all.totalClaim);
        $(".claimCounts_approvedCount").text(all.approvedCount);
        $(".claimCounts_pendingCount").text(all.pendingCount);
        $(".claimCounts_rejectedCount").text(all.rejectedCount);
        $(".claimCounts_draftCount").text(all.totalDraft);
        $(".claimCounts_paymentCompletedCount").text(all.paymentCompletedCount);
    }
</script>