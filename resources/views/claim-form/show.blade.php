@extends('components.admin.layout')
@section('page-content')
    @php
        function formatDateWithTime($value)
        {
            if (empty($value)) {
                return null;
            }

            try {
                $dateTime = \Carbon\Carbon::parse($value);
                $formattedDate = $dateTime->format('d-m-Y'); // dd-mm-yy format

                // Check if time component exists and is not midnight (00:00:00)
                if ($dateTime->format('H:i:s') !== '00:00:00') {
                    return $formattedDate . ' (' . $dateTime->format('H:i:s') . ')'; // Add time in parentheses
                }

                return $formattedDate;
            } catch (\Exception $e) {
                return $value; // Return original value if parsing fails
            }
        }
    @endphp
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow container-main-card">

                    <div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Claim Details</h6>
                        <div class="d-flex gap-2">
                            @if (
                                (hasRole('snp') || hasRole('bnp') || hasRole('lsp')) &&
                                    (!isset($claimDetails['status']) || $claimDetails['status'] === 0 || $claimDetails['status'] === '0'))
                                {{-- <a href="{{ url('claim-edit/' . $claimDetails['id'] . '/edit') }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit Claim
                                </a> --}}
                            @endif
                            @include('components.admin.back-button')
                        </div>
                    </div>
                    <div class="card-body data-show-type-1 pt-0">
                        <div class="row g-3">
                            @php

                                $snpIdLabel = '';

                                if ($claimSlug === 'claim-for-catalogue-creation') {
                                    $snpIdLabel = 'SNP Id';
                                } elseif ($claimSlug === 'claim-for-transportation-and-logistic') {
                                    $snpIdLabel = 'LSP Id';
                                } elseif ($claimSlug === 'claim-for-packaging') {
                                    $snpIdLabel = 'SNP Id';
                                } elseif ($claimSlug === 'claim-for-accounts-management') {
                                    $snpIdLabel = 'SNP Id';
                                } elseif ($claimSlug === 'claim-for-demand-generation') {
                                    $snpIdLabel = 'BNP Id';
                                }

                                $basic_details = [
                                    'snp_id' => $snpIdLabel,
                                    'msme_name' => 'MSME Name',
                                    'team_registration_id' => 'Team Registration Id',
                                    'msme_udyam_number' => 'Udyam Number',
                                    'msme_category' => 'MSME Category',
                                    'msme_classification' => 'MSME Classification',
                                    'catalogue_type' => 'Catalogue Type',
                                    'onboarding_date' => 'Onboarding Date',
                                    'number_of_skus' => 'Number of SKUs',
                                    'amount' => 'Claimed Amount(Rs)',
                                    'bpp_id' => 'BppId /Provider ID',
                                    'created_at' => 'Created At',
                                    'updated_at' => 'Updated At',
                                    'claim_period_start_date' => 'Claim Period Start Date',
                                    'claim_period_end_date' => 'Claim Period End Date',
                                    'organisation_id_seller_np' => 'Organisation ID of Seller NP',
                                    'organisation_id_lsp' => 'Organisation ID of LSP',
                                    'seller_np_configuration' => 'Seller NP Configuration',
                                    'configuration' => 'Configuration',
                                    'ondc_seller_network_id' => 'ONDC Seller Network ID',
                                    'seller_credential_report' => 'Seller Credential Report',
                                    'catalogue_score_report' => 'Catalogue Score Id',
                                    'date_of_onboarding' => 'Date of onboarding of MSE on ONDC',
                                ];

                            @endphp


                            @foreach ($basic_details as $key => $label)
                                @php
                                    $value = $claimDetails[$key] ?? null;
                                    // Apply date formatting to date fields
                                    $dateFields = [
                                        'onboarding_date',
                                        'created_at',
                                        'updated_at',
                                        'date_of_onboarding',
                                        'claim_period_start_date',
                                        'claim_period_end_date',
                                    ];
                                    if (in_array($key, $dateFields)) {
                                        $value = formatDateWithTime($value);
                                    }
                                @endphp

                                @if (!empty($value))
                                    @if (in_array($key, ['onboarding_date', 'number_of_skus']) && hasRole('ondc-admin'))
                                        <div class="form-group col-md-3 text-success">
                                            <label><strong>{{ $label }}:</strong></label>
                                            {{ $value }}
                                        </div>
                                    @else
                                        <div class="form-group col-md-3">
                                            <label><strong>{{ $label }}:</strong></label>
                                            {{ $value }}
                                        </div>
                                    @endif
                                @endif
                            @endforeach

                            @if ($invoiceDocument)
                                <div class="form-group col-md-3">
                                    <label><strong>Invoice Document:</strong></label>
                                    <a href="{{ $invoiceDocument }}" download>View Invoice Document</a>
                                </div>
                            @endif

                            @if (count($claimOrders) > 0)
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-end mb-2">
                                        <a href="{{ url('claim-orders-export/' . $claimDetails['id']) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fa fa-file-excel-o"></i> Download Excel
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle"
                                            id="claimOrdersTable">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>MSE TEAM ID</th>
                                                    @if (in_array($claimSlug, ['claim-for-catalogue-creation', 'claim-for-accounts-management', 'claim-for-packaging']))
                                                        <th>Catalogue Score ID</th>
                                                        <th>Credential Score ID</th>
                                                        <th>Provider ID</th>
                                                    @endif
                                                    <th>Domain</th>
                                                    <th>Item Consolidated Category</th>
                                                    @if ($claimSlug != 'claim-for-transportation-and-logistic' && $claimSlug != 'claim-for-packaging')
                                                        <th>AOV Grouping Type</th>
                                                    @endif
                                                    @if (!in_array($claimSlug, []))
                                                        {{-- Show Buyer NP Name
                                                        for all except packaging --}}
                                                        <th>Buyer NP Name</th>
                                                    @endif

                                                    @if (!in_array($claimSlug, ['claim-for-catalogue-creation']))
                                                        <th>Seller NP Name</th>
                                                    @endif
                                                    <th>Transaction Network Order ID</th>
                                                    <th>Invoice Number</th>
                                                    <th>Invoice Date</th>
                                                    <th>Network Transaction ID </th>
                                                    <th>Order Created</th>
                                                    <th>Order Completed</th>
                                                    <th>Cart Level Item Price</th>
                                                    <th>Delivery Fee</th>
                                                    <th>Total Fee</th>
                                                    <!-- <th>Transaction Date</th> -->
                                                    <th>Status</th>
                                                    <!-- <th>Order Invoice Number</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            <?php /* download="{{ $claimDocument->file_system_name }}"*/ ?>

                            @foreach ($claimDocuments as $key => $claimDocument)
                                <div class="form-group col-md-3">
                                    <label><strong>{{ $claimDocument->document_category_name }}</strong></label>
                                    <a href="{{ url('storage/app/uploads/claim-documents/' . $claimDocument->file_system_name) }}"
                                        target="_blank" class="text-decoration-none">
                                        {{ $claimDocument->file_name }}
                                    </a>
                                </div>
                            @endforeach

                            @if (
                                !(hasRole('snp') || hasRole('bnp') || hasRole('lsp')) &&
                                    !(
                                        $claimDetails['review_status'] == \App\Web\Claim\ClaimReviewStatus::APPROVED->value ||
                                        $claimDetails['review_status'] == \App\Web\Claim\ClaimReviewStatus::REJECTED->value ||
                                        $claimDetails['review_status'] == \App\Web\Claim\ClaimReviewStatus::REVERTED->value
                                    ))
                                {{-- @if (!$isForwarded) --}}
                                @php $logedinstatus = ''; @endphp
                                @if (hasRole('ondc-admin'))
                                    @php $logedinstatus = $claimDetails['ondc_review_status']; @endphp
                                @elseif(hasRole('nsic'))
                                    @php $logedinstatus = $claimDetails['nsic_review_status']; @endphp
                                @elseif(hasRole('nsic-finance'))
                                    @php $logedinstatus = $claimDetails['nsicfinance_review_status']; @endphp
                                @endif
                                @if ($logedinstatus == \App\Web\Claim\ClaimReviewStatus::PENDING->value)
                                    <div class="form-group">
                                        <div class="d-flex gap-2">

                                            @if (acl('claim-view') && acl('claim-forward'))
                                                <?php            /*<button type="button" class="btn btn-success workflow"  action="2" >Approve</button>*/ ?>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#myForwardedModal">Approve</button>
                                            @endif

                                            @if (acl('claim-view') && acl('claim-approve'))
                                                <button type="button" class="btn btn-success workflow"
                                                    action="3">Approve</button>
                                            @endif

                                            @if (acl('claim-view') && acl('claim-reject'))
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#myRejectModal">Reject</button>
                                            @endif

                                            @if (acl('claim-view') && acl('claim-revert'))
                                                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#myRevertModal">Revert</button>
                                            @endif

                                        </div>
                                @endif
                                {{-- @endif --}}
                            @endif
                            @if (hasRole('ondc-admin'))
                                {{-- <span><strong>Note : The highlighted ones in green color will be verified by ONDC. --}}
                                {{-- </strong></span> --}}
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal forwarded -->
    <div class="modal fade" id="myForwardedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Remarks
                    <textarea class="form-control" name="comments" id="comments" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success workflow" data-bs-dismiss="modal"
                        action="2">Approve</button>
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
                    <button type="submit" class="btn btn-danger workflow" data-bs-dismiss="modal"
                        action="4">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div class="modal fade" id="myRevertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Revert to:
                    <select name="revert_to" id="revert_to" class="form-control">
                        <option value="">Select</option>
                        @foreach ($roles as $role_id => $role_name)
                            <option value="{{ $role_id }}">{{ $role_name }}</option>
                        @endforeach
                    </select>
                    Remarks
                    <textarea class="form-control" name="comments" id="commentss" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning workflow" data-bs-dismiss="modal"
                        action="5">Revert</button>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script>
        // function initClaimOrdersTable() {
        //     if ($.fn.DataTable.isDataTable('#claimOrdersTable')) return;
        //     var claimOrdersTable = 

        //     return claimOrdersTable;
        // }

        $(document).ready(function() {
            dataTableInit({
                id: "#claimOrdersTable",
                showExcelExport: false,
                order: {
                    column: 1,
                    direction: "asc"
                },
                url: "{{ url('claim-orders-list/' . $claimDetails['id']) }}",
                columns: [{
                        orderable: false,
                        className: "noExport",
                        render: function(data, type, full, meta) {
                            return serialNumber("#claimOrdersTable", meta.row);
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.team_id ?? '';
                        }
                    },
                    @if (in_array($claimSlug, ['claim-for-catalogue-creation', 'claim-for-accounts-management', 'claim-for-packaging']))
                        {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.catalogue_score_id ?? '';
                            }
                        }, {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.credential_score_id ?? '';
                            }
                        }, {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.provider_id ?? '';
                            }
                        },
                    @endif {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.domain ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.item_consolidated_category ?? '';
                        }
                    },
                    @if ($claimSlug != 'claim-for-transportation-and-logistic' && $claimSlug != 'claim-for-packaging')
                        {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.aov_grouping_type ?? '';
                            }
                        },
                    @endif
                    @if (!in_array($claimSlug, []))
                        {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.buyer_np_name ?? '';
                            }
                        },
                    @endif
                    @if (!in_array($claimSlug, ['claim-for-catalogue-creation']))
                        {
                            orderable: true,
                            render: function(data, type, row) {
                                return row.seller_np_name ?? '';
                            }
                        },
                    @endif {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.ondc_order_id ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.invoice_number ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.invoice_date ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.network_transaction_id ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.order_creation_timestamp ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.order_completed_timestamp ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.cart_level_item_price ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.delivery_fee ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.total_fee ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function(data, type, row) {
                            return row.order_status ?? '';
                        }
                    }
                ],
                createdRow: true
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $(".workflow").on("click", function(e) {
            e.preventDefault();
            var action = $(this).attr('action');
            var revert_to = $("#revert_to").val();
            var comments = $("#commentss").val();


            if (revert_to != '' && comments == '' && (action == 2 || action == 4 || action == 5)) {

                if (action == 2) {

                    var modal = new bootstrap.Modal(document.getElementById('myForwardedModal'));
                    modal.show();
                }

                if (action == 4) {

                    var modal = new bootstrap.Modal(document.getElementById('myRejectModal'));
                    modal.show();
                }

                if (action == 5) {

                    var modal = new bootstrap.Modal(document.getElementById('myRevertModal'));
                    modal.show();
                }


                toastr.error("Please enter comments");
                return false;
            }


            var claimId = "{{ $claimDetails['id'] }}";
            if (confirm("Are you sure you want to perform this action?")) {
                $.ajax({
                    url: "{{ url('application-action') }}",
                    type: 'POST',
                    data: {
                        application_id: claimId,
                        action: action,
                        comments: comments,
                        revert_to: revert_to
                    },

                    success: function(res) {
                        toastr.success(res.message);
                        setTimeout(function() {
                            window.location.href = "{{ url('claims') }}";
                        }, 2000);

                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON.message);
                    }
                });
            }

        });

        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('textarea').val(''); // clear all textareas
            $(this).find('select').prop('selectedIndex', 0); // reset dropdown
        });
    </script>
@endsection
@endsection
