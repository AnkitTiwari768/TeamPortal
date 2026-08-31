<script>
    /*
     * Read More / Read Less for long DataTable cell values.
     *
     * Uses the same markup and class names as the existing implementation in
     * claim-form/index.blade.php (.toggle-text / .short-text / .full-text) so the
     * look and behaviour stay consistent across the project.
     *
     * The click handler is delegated on `document` and attached only once, so it
     * keeps working after every DataTable redraw — paging, sorting, filtering and
     * AJAX reloads all replace the <td> contents, which would detach a handler
     * bound directly to the anchor.
     */

    function readMoreEscapeHtml(value) {
        return $('<div>').text(value).html();
    }

    /**
     * Build a truncated cell with a Read More toggle.
     *
     * @param {string} value  raw cell value
     * @param {number} limit  characters to show collapsed (default 40)
     */
    function readMoreCell(value, limit) {
        limit = limit || 40;

        if (value === null || value === undefined || value === '') {
            return '-';
        }

        var text = String(value);

        if (text.length <= limit) {
            return readMoreEscapeHtml(text);
        }

        return '<span class="short-text">' + readMoreEscapeHtml(text.substring(0, limit)) + '...</span>' +
            '<span class="full-text d-none">' + readMoreEscapeHtml(text) + '</span>' +
            ' <a href="#" class="toggle-text d-block mt-1" style="text-decoration:none;">Read More</a>';
    }

    function initReadMoreToggle() {
        if (window.readMoreHandlerAttached) return;

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-text');
            if (!btn) return;

            e.preventDefault();

            let cell = btn.closest('td');
            if (!cell) return;

            let shortText = cell.querySelector('.short-text');
            let fullText = cell.querySelector('.full-text');

            if (!shortText || !fullText) return;

            shortText.classList.toggle('d-none');
            fullText.classList.toggle('d-none');

            btn.textContent =
                btn.textContent.trim() === 'Read More' ?
                'Read Less' :
                'Read More';
        });

        window.readMoreHandlerAttached = true;
    }

    initReadMoreToggle();
</script>
