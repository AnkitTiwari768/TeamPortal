@extends('components.admin.content-layout')

@section('card-content')

    @if (isset($is_restricted) && $is_restricted == 1)
    @else
        @include('catalogue.claim_eligibilty_criteria')
    @endif
    <div class="align-items-end border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control filter_btn" name="from_date" id="from_date"
                        placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control filter_btn" name="to_date " id="to_date"
                        placeholder="To Date">
                </div>
                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>

        @if (hasRole('snp'))
            {{-- <div class="card-header d-flex">
                <div class="action-header ms-auto">
                    <div class="btn-group drop-btn">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#myCSVModal">
                            <img src="{{ asset('assets/img-new/add.svg') }}">
                            Add Bulk Claim
                        </button>
                    </div>
                </div>
            </div> --}}
        @endif

    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        {{-- <th><input type="checkbox" id="selectAll"></th> --}}
                        <th>{{ __('message.sn') }}</th>
                        <th>TEAMID</th>
                        <th>Udyam</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>Name of enterprise</th>
                        <th>Enterprise Type </th>
                        <th>Major Activity </th>
                        <th>Date of Registration</th>
                        {{-- <th class="actions">{{ __('message.action') }}</th> --}}
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <!-- Modal CSV -->
    <div class="modal fade" id="myCSVModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Bulk Claim</h5>
                    </div>

                    <div class="text-center flex-grow-1">
                        <a href="{{ url('storage/app/download_format/claim_bulk_import.zip') }}"
                            class="btn btn-sm btn-outline-primary">Download Sample Format</a>
                    </div>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="bulk-upload">
                    @csrf
                    <div class="modal-body">
                        <p>
                            <select class="form-select" name="claim_type_id" id="claim_type_id">
                                <option value="">Select Claim Type</option>
                                @foreach ($claim_types ?? [] as $claim_type_id => $claim_type_name)
                                    <option value="{{ $claim_type_id }}" @if (($claimTypeIdValue ?? null) == $claim_type_id) selected @endif>
                                        {{ $claim_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </p>
                        <p>Select File : <input type="file" name="file" id="file" accept=".zip"></p>
                        <span class="text-primary">Please ensure the file is in the correct format by downloading the sample
                            format.</span>

                        <div id="csv-errors" style="color: red; font-family: Arial; padding: 10px;"></div>


                    </div>
                    <div class="modal-footer">
                        <button type="sbumit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@section('js')
    ;
    <script>
        $('#myCSVModal').on('hidden.bs.modal', function() {
            $('#csv-errors').html(''); // clear the error messages
            $('#file').val('');
        });

        $(document).on('change', '#bulk-upload', function(e) {
            e.preventDefault();
            let fileName = e.target.files[0]?.name; // get selected file name
            if (fileName) {
                let ext = fileName.split('.').pop().toLowerCase();
                if (ext !== 'zip') {
                    alert("Only .zip files are allowed!");
                    $('#file').val('');
                }
            }
        });

        $(document).on('submit', '#bulk-upload', function(e) {
            e.preventDefault();

            document.getElementById('csv-errors').innerHTML = '';
            var claimTypeId = document.getElementById('claim_type_id').value;

            var form = document.getElementById('bulk-upload');
            var formData = new FormData(form);
            formData.append('claim_type_id', claimTypeId);
            if (claimTypeId == '') {
                toastr.error("Please select claim type.");
                return;
            }

            var fileInput = document.getElementById('file');
            if (!fileInput.files.length) {
                toastr.error("Please select a file to upload.");
                return;
            }

            $.ajax({
                url: "{{ url('claims/bulk-import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#ajax-loader").show();
                },
                success: function(res) {
                    toastr.success(res.message);
                    $("#csv-errors").html('');
                    setTimeout(function() {
                        window.location.href = "{{ url('claims') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        displayCsvErrors(xhr.responseJSON);
                    } else {
                        toastr.error("Something went wrong.");
                        document.getElementById('csv-errors').innerHTML = '';
                    }
                },
                complete: function() {
                    $("#ajax-loader").hide();
                }
            });
        });

        // Function to format and display errors
        function displayCsvErrors(data) {
            let errorHtml = `<strong>${data.message}</strong><ul>`;

            for (let cell in data.errors) {
                data.errors[cell].forEach(msg => {
                    errorHtml += `<li><strong>Cell ${cell}</strong>: ${msg}</li>`;
                });
            }

            errorHtml += `</ul>`;
            document.getElementById('csv-errors').innerHTML = errorHtml;
        }

        //   dataTableInit({
        //     id: "#dataTable",
        // 	showExcelExport: true,
        //     order: {
        //       column: 1,
        //       direction: "asc"
        //     },
        //     url: "{{ url('catalogue-created/datalist') }}",
        //     columns: [
        //       {
        //         "orderable": false,
        //         "render": function(data, type, full, meta) {
        //           return serialNumber("#dataTable", meta.row);
        //         }
        //       },
        //       {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.team_id;
        //         }
        //       },
        //       {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.udyam_no;
        //         }
        //       },

        //       {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.mobile;
        //         }
        //       },
        // 	  {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.email;
        //         }
        //       },
        // 	  {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.entrepreneur_name;
        //         }
        //       },
        // 	  {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.enterprise_name;
        //         }
        //       },

        // 	   {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.msme_classification;
        //         }
        //       },


        // 	  {
        //         "orderable": true,
        //         "render": function(data, type, row) {
        //             return row.created_at;
        //         }
        //       },

        // 	  { 
        //         "orderable": false,
        //         "render": function (data, type, row) {
        //           var catealog_creation = '<a href="{{ url('catalogue-creation') }}/' + row.id + '" rel="tooltip" title=" Claim For Catalogue Creation" class="btn  bg-primary btn-sm btn-circle m-1 view-action-btn" >  <img src="{{ asset('assets/img-new/Catalogue.svg') }}"></a>';
        //             var actions = createActionButtons([catealog_creation]);
        //             return actions;
        //         }
        //     }

        //     ],
        // 	filters: ["from_date","to_date"]
        //   });


        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('restricted-msme-list') }}",
            columns: [
                // {
                //     "orderable": false,
                //     "className": "noExport",
                //     "render": function(data, type, row) {
                //         var checked = selectedRows[row.id] ? 'checked' : '';
                //         return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' +
                //             checked + '>';
                //     }
                // },
                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.team_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.udyam_no;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.mobile;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.email;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.state_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.enterprise_name;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.msme_classification;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.major_activity;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                },

                // {
                //     "orderable": false,
                //     "render": function(data, type, row) {
                //         var catealog_creation = '<a href="{{ url('catalogue-creation') }}/' + row.id +
                //             '" rel="tooltip" title=" Claim For Catalogue Creation" class="btn  bg-primary btn-sm btn-circle m-1 view-action-btn" >  <img src="{{ asset('assets/img-new/Catalogue.svg') }}"></a>';
                //         var actions = createActionButtons([catealog_creation]);
                //         return actions;
                //     }
                // },

            ],
            filters: ["from_date", "to_date"]
        });

        $(document).on("change", ".row-checkbox", function() {
            var id = $(this).val();
            selectedRows[id] = $(this).prop("checked");

            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });

        $(document).on("change", "#selectAll", function() {
            var checked = $(this).prop("checked");
            $(".row-checkbox").each(function() {
                $(this).prop("checked", checked);
                selectedRows[$(this).val()] = checked;
            });
        });
        $('#dataTable').on('draw.dt', function() {
            $(".row-checkbox").each(function() {
                var id = $(this).val();
                $(this).prop("checked", selectedRows[id] ? true : false);
            });
            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });
    </script>
@endsection
@endsection
