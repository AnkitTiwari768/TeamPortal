<script>
    function formatComments(text, id) {
        if (text.length > 30) {
            let truncatedText = text.substring(0, 30) + '...';
            return `
                <span id="commentText_${id}" data-full="${text}" data-truncated="${truncatedText}">
                    ${truncatedText}
                </span>
                <a href="#" id="btnText_${id}" class="btn btn-link btn-sm btnText">Read More</a>
            `;
        } else {
            return text;
        }
    }


    function formatStatus(status) {
        if (status === 'Approved' || status === 'approved') {
            return '<span class="badge bg-success">Approved</span>';
        }
        if (status === 'Reverted' || status === 'reverted') {
            return '<span class="badge bg-warning">Reverted</span>';
        }
        if (status === 'Rejected' || status === 'rejected') {
            return '<span class="badge bg-danger">Rejected</span>';
        }
        if (status === 'Submitted' || status === 'submitted') {
            return '<span class="badge bg-primary">Submitted</span>';
        }
        if (status === 'forwarded' || status === 'forwarded') {
            return '<span class="badge bg-info">Forwarded</span>';
        }
        if (status === 'resend' || status === 'Resend') {
            return '<span class="badge bg-primary">Resend</span>';
        }
    }

    const TimelineUtils = {

        truncate(text, limit = 30) {
            if (!text) return null;
            return text.length > limit ? {
                full: text.replace(/\n/g, '<br>'),
                short: text.substring(0, limit) + '...'
            } : {
                full: text.replace(/\n/g, '<br>'),
                short: null
            };
        },

        remarkHtml(index, remark) {
            if (!remark) return '';

            const {
                full,
                short
            } = remark;

            if (!short) {
                return `<p><b>Remark:</b> ${full}</p>`;
            }

            return `
                <p>
                    <b>Remark:</b>
                    <span id="commentText_${index}" data-full="${full}" data-short="${short}">
                        ${short}
                    </span>
                    <a href="#" class="btn btn-sm btn-primary btnText" data-id="${index}">
                        Read More
                    </a>
                </p>`;
        }
    };

    $(document).on("click", ".btnText", function(e) {
        e.preventDefault();

        const id = $(this).data("id");
        const $text = $(`#commentText_${id}`);

        if ($(this).text() === "Read More") {
            $text.html($text.data("full"));
            $(this).text("Read Less");
        } else {
            $text.html($text.data("short"));
            $(this).text("Read More");
        }
    });

    $(document).on("click", ".timeline-history", function() {

        const batchId = $(this).data("id");
        $('#timelineModal #text').empty();
        $("#timelineModalLabel").text('View Timeline');

        $.get(`${BASE_URL}/get-timeline/${batchId}`, function(response) {

            let html = '';

            response.data.forEach((item, index) => {

                const remark = TimelineUtils.truncate(item.comment);

                html += `
                <li class="timeline-item ${item.status}" data-date="${item.updated_at}">
                    <h4>${item.subject}</h4>
                    ${TimelineUtils.remarkHtml(index, remark)}
                </li>`;
            });

            $('#timelineModal #text').html(html);
            $("#timelineModal").modal('show');
        });
    });


    $(document).on("click", ".timeline-request", function() {

        const batchId = $(this).data("id");
        $('#timelineModal #text').empty();
        $("#timelineModalLabel").text('Batch Claim Timeline');

        $.get(`${BASE_URL}/get-timeline/${batchId}`, function(response) {

            let html = '';

            response.data.forEach(item => {
                html += `
                <li class="timeline-item ${item.status}" data-date="${item.created_at}">
                    <h4>${item.subject}</h4>
                    <a href="javascript:void(0);"
                       class="badge bg-primary timeline-view-detail"
                       data-id="${item.id}">
                       View Details
                    </a>
                    <div id="claim-details-${item.id}" class="mt-2"></div>
                </li>`;
            });

            $('#timelineModal #text').html(html);
            $("#timelineModal").modal('show');
        });
    });


    $(document).on("click", ".timeline-view-detail", function() {

        const $btn = $(this);
        const timelineId = $btn.data("id");
        const $container = $(`#claim-details-${timelineId}`);

        if ($btn.text() === 'Hide Details') {
            $btn.text('View Details');
            $container.slideUp().empty();
            return;
        }

        $btn.text('Hide Details');

        $.get(`${BASE_URL}/get-timeline-details/${timelineId}`, function(response) {

            if (!response.data.length) {
                $container.html("No claim details found.").slideDown();
                return;
            }

            let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Claim No</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>`;

            response.data.forEach((claim, index) => {
              
                const remark = TimelineUtils.truncate(claim.comments);
                html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${claim.claim_number}</td>
                    <td>${claim.status}</td>
                    <td>${TimelineUtils.remarkHtml(index, remark)}</td>
                </tr>`;
            });

            html += `</tbody></table></div>`;

            $container.html(html).slideDown();
        });
    });
</script>
