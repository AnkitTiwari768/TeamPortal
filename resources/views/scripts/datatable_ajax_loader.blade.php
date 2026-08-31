<script>
    /*
     * AJAX loader for server-side DataTables lists.
     *
     * Reuses the existing project loader — the #ajax-loader / .spinner element
     * already rendered by components/admin/layout.blade.php — and the same
     * preXhr.dt / xhr.dt / draw.dt wiring used by the fund_flow and
     * executed-workshops lists. No new loader markup or styling is introduced.
     *
     * Call bindDataTableLoader('#dataTable') BEFORE dataTableInit() so the very
     * first request (fired during initialisation) is captured too.
     *
     * Covers every request that refreshes the list: initial load, date filters,
     * SNP / IA / status filter changes, search, paging and sorting — they all go
     * through the same DataTables AJAX cycle.
     */
    function bindDataTableLoader(tableId) {
        var $loader = $('#ajax-loader');

        // The first request is issued by dataTableInit(), so start off visible.
        $loader.show();

        $(tableId).on('preXhr.dt', function(e, settings) {
            /*
             * Cancel a request that is still in flight so rapid successive
             * filter / search / paging changes cannot overlap or land out of
             * order. DataTables 1.13 only reports an "Ajax error" when
             * readyState === 4, so an aborted request stays silent.
             */
            if (settings.jqXHR && settings.jqXHR.readyState > 0 && settings.jqXHR.readyState < 4) {
                settings.jqXHR.abort();
            }

            // Shown after the abort above, whose error path hides the loader.
            $loader.show();
        });

        /*
         * xhr.dt fires on success AND on failure, so the loader always clears.
         */
        $(tableId).on('xhr.dt', function(e, settings, json, xhr) {
            $loader.hide();

            // status 0 means the request was aborted by the handler above — not
            // a real failure, so stay quiet and let the newer request finish.
            if (!xhr || !xhr.status || xhr.status < 400) {
                return;
            }

            var message = 'Unable to load the list. Please try again.';
            var payload = (xhr && xhr.responseJSON) ? xhr.responseJSON : json;

            if (payload && payload.message) {
                message = payload.message;
            }

            if (window.toastr) {
                toastr.error(message);
            }

            // Returning true tells DataTables the error has been handled, which
            // suppresses its own built-in "Ajax error" alert popup.
            return true;
        });

        // Safety net for redraws that do not involve a request.
        $(tableId).on('draw.dt', function() {
            $loader.hide();
        });
    }
</script>
