<footer class="py-2 bg-light mt-auto">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; {{ __('MSME Team Portal') }} {{ date('Y') }}</div>
            <div>
                <!-- <a href="#">Privacy Policy</a>
              &middot;
              <a href="#">Terms &amp; Conditions</a> -->
            </div>
        </div>
    </div>

</footer>

@include('components.admin.popup.timeline')
@include('components.admin.timeline')

{{-- <script>
    //Timeline

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

    $(document).on("click", ".timeline-history", function() {
        const batchId = $(this).data("id");
        $('#timelineModal #text').html('');
        $("#timelineModalLabel").text('View Timeline');

        let html = '';

        $.ajax({
            url: BASE_URL + '/get-timeline/' + batchId,
            method: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.data.length > 0) {
                    $.each(response.data, function(index, val) {
                        let timelineId = val.id;
                        let subject = val.subject;
                        let comment = val.comments?.trim(); // handles undefined/null/empty
                        let fullRemark = comment ? comment.replace(/\n/g, '<br>') : '';
                        let truncatedRemark = '';
                        let content = '';

                        // Start building HTML for this timeline item
                        html += '<li class="timeline-item ' + val.status + '" data-date="' +
                            val.created_at + '">';
                        html += '<h4>' + subject + '</h4>';

                        // Only add remark section if comment is not empty
                        if (comment) {
                            const remarkLength = comment.length;

                            if (remarkLength > 30) {
                                truncatedRemark = comment.substring(0, 30) + '...';
                                content =
                                    `<b class="remark-text">Remark:</b> ${truncatedRemark} <a href="#" onclick="btnDynamic(${index});" id="btnText_${index}" class="btn btn-theme btn-primary btnText">Read More</a>`;
                            } else {
                                content =
                                    `<b class="remark-text">Remark:</b> ${fullRemark}`;
                            }

                            html +=
                                `<p class="content_${index}" data-full="${fullRemark}" data-truncated="${truncatedRemark}">${content}</p>`;
                        }

                        html +=
                            `<a href="javascript:void(0);" data-id=${timelineId} class="badge bg-primary timeline-view-detail">View Details</a>`;

                        html +=
                            `<div id="claim-details-${timelineId}"></div>`;

                        html += '</li>';
                    });

                    $('#timelineModal #text').append(html);
                }

                $("#timelineModal").modal('show');

            },
            error: function(error) {
                console.log(error);
            }
        });
    });


    $(document).on("click", ".timeline-view-detail", function() {
        const $btn = $(this);
        const batchTimelineId = $btn.data("id");
        const $details = $(`#claim-details-${batchTimelineId}`);
        $details.html('');

        if ($btn.text().trim() === 'Hide details') {
            $btn.text('View details');
            $details.hide();
            return;
        } else {
            $btn.text('Hide details');
        }

        $.ajax({
            url: BASE_URL + '/get-timeline-details/' + batchTimelineId,
            method: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let html = '';
                if (response.data.length > 0) {
                    html += `<div class="table-responsive"><table class="table table-striped table-hover">
                    <tr>
                        <th>#</th>
                        <th>Claim Application No.</th>
                        <th>Status</th>
                        <th>Remarks</th>    
                    </tr>`;

                    $.each(response.data, function(index, val) {
                        html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${val.claim_number}</td>
                            <td>${formatStatus(val.status)}</td>
                            <td>${formatComments(val.comments, val.claim_number)}</td>
                        </tr>`;
                    });

                    html += '</table></div>';
                } else {
                    html = "No details found...";
                }

                $details.html(html).show();
            },
            error: function(error) {
                console.log(error);
            }
        });
    });

    $(document).on("click", ".btnText", function(e) {
        e.preventDefault();

        const id = $(this).attr("id").split("_")[1];
        const $textSpan = $(`#commentText_${id}`);
        const fullText = $textSpan.data("full");
        const truncatedText = $textSpan.data("truncated");

        if ($(this).text() === "Read More") {
            $textSpan.text(fullText);
            $(this).text("Read Less");
        } else {
            $textSpan.text(truncatedText);
            $(this).text("Read More");
        }
    });



    $(document).on("click", ".timeline-request", function() {
        var app_id = $(this).data('id');
        $('#timelineModal #text').html('');

        //var url = BASE_URL + '/np-timeline/'+app_id;
        $("#timelineModalLabel").text('View Application Approval Timeline');
        //To Get History
        var html = '';
        $.ajax({
            url: BASE_URL + '/get-timeline/' + app_id,
            method: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {

            },
            success: function(response) {

                if (response.data.length > 0) {

                    $.each(response.data, function(index, val) {
                        let subject = val.subject;
                        let comment = val.comment
                            ?.trim(); // handles undefined/null/empty
                        let fullRemark = comment ? comment.replace(/\n/g, '<br>') : '';
                        let truncatedRemark = '';
                        let content = '';

                        // Start building HTML for this timeline item
                        html += '<li class="timeline-item ' + val.status +
                            '" data-date="' +
                            val.preview_updated_at + '">';
                        html += '<h4>' + subject + '</h4>';

                        // Only add remark section if comment is not empty
                        if (comment) {
                            const remarkLength = comment.length;

                            if (remarkLength > 30) {
                                truncatedRemark = comment.substring(0, 30) + '...';
                                content =
                                    `<b class="remark-text">Remark:</b> ${truncatedRemark} <a href="#" onclick="btnDynamic(${index});" id="btnText_${index}" class="btn btn-theme btn-primary btnText">Read More</a>`;
                            } else {
                                content =
                                    `<b class="remark-text">Remark:</b> ${fullRemark}`;
                            }

                            html +=
                                `<p class="content_${index}" data-full="${fullRemark}" data-truncated="${truncatedRemark}">${content}</p>`;
                        }

                        html += '</li>';
                    });

                    $('#timelineModal #text').append(html);
                }
            }
        });

        $("#timelineModal").modal('show');

    });
</script> --}}

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    })

    if (window !== window.top) {
        document.getElementById('mainbody').innerHTML =
            "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
        document.getElementById("mainbody").style.backgroundColor = "#ccc";
    }
</script>
