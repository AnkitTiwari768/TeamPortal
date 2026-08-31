@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">

                    <div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Industrial Associate Details</h6>

                    </div>
                    <div class="card-body snp_details">
                        <div class="row g-3">
                            @php
                                $basic_details = [
                                    'organization_name' => 'Organization Name',
                                    'entity_type' => 'Entity Type',
                                    'entity_email' => 'Entity Email',
                                    'number_of_members' => 'Number Of Members/Beneficiaries',
                                    'registration_number' => 'Registration Number',
                                    'website' => 'Website',
                                    'contact_number' => 'Contact Number',
                                    'pan_number' => 'PAN Number',
                                    'complete_address' => 'Complete Address',
                                    'district_name' => 'District Name',
                                    'state_name' => 'State Name',
                                    'statusWithLabel' => 'Status',
                                ];
                            @endphp

                            <h4>Basic Details</h4>
                            @foreach ($basic_details as $key => $label)
                                @if (!empty($data[$key]))
                                    <div class="col-md-4">
                                        <label><strong>{{ $label }}:</strong></label>

                                        @if ($key === 'statusWithLabel')
                                            {!! $data[$key] !!} {{-- Render HTML badge --}}
                                        @else
                                            {{ $data[$key] }}
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                            @if (!empty($data['authorization_document']))
                                <div class="col-md-4">
                                    <label><strong>Authorized Certificate:</strong></label><br>
                                    <a href="{{ url('storage/app/' . $data['authorization_document']) }}"
                                        download="authorized-certificate">
                                        View Authorized Certificate
                                    </a>
                                </div>
                            @endif

                            <h4>Contact Person Details</h4>

                            @php
                                $contact_person_fields = [
                                    'name' => 'Contact Person Name',
                                    'designation' => 'Designation',
                                    'phone' => 'Contact Person Phone',
                                    'email' => 'Contact Person Email',
                                ];

                                $contact = $data['contact_person'] ?? [];
                            @endphp

                            @foreach ($contact_person_fields as $key => $label)
                                @if (!empty($contact[$key]))
                                    <div class="col-md-4">
                                        <label><strong>{{ $label }}:</strong></label>
                                        {{ $contact[$key] }}
                                    </div>
                                @endif
                            @endforeach
                            @if (isset($data['status']) && $data['status'] == 1)
                                <div class="form-group">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success approve" data-bs-toggle="modal"
                                            data-bs-target="#myApproveModal">Approve</button>
                                        <button type="button" class="btn btn-danger reject" data-bs-toggle="modal"
                                            data-bs-target="#myRejectModal">Reject</button>
                                    </div>
                                @else
                                    <p><strong>Review Remarks </strong>: {{ $data['review_remarks'] ?? '' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div class="modal fade" id="myRejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Remarks
                    <textarea class="form-control" name="comments" id="comments" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger workflow" data-action="reject"
                        data-bs-dismiss="modal">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Approve -->
    <div class="modal fade" id="myApproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Remarks
                    <textarea class="form-control" name="comments" id="commentss" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning workflow" data-action="approve"
                        data-bs-dismiss="modal">Approve</button>
                </div>
            </div>
        </div>
    </div>



    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".workflow").on("click", function(e) {
            e.preventDefault();
            var action = $(this).data('action');
            var remarks = $("#commentss").val() || $("#comments").val();
            var url = action === "approve" ? "{{ url('approve-ia') }}" : "{{ url('reject-ia') }}";

            var id = "{{ $data['id'] }}";
            if (confirm("Are you sure you want to perform this action?")) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        ia_registration_id: id,
                        action: action,
                        remarks: remarks
                    },

                    success: function(res) {
                        toastr.success(res.message);
                        setTimeout(function() {
                            window.location.href = "{{ url('ia-verified-list') }}";
                        }, 2000);

                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON.message);
                    }
                });
            }

        });

        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('textarea').val('');
        });
    </script>
@endsection
