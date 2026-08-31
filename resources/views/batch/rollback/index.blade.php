@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header border-bottom card-body d-flex justify-content-between mb-3">
                    <div class="heading">
                        <h1>{{ __('Rollback Batch') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('Rollback Batch') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body pt-1">
                    <form id="rollbackForm">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="mb-4">
                                    <label class="required form-label fw-bold text-secondary mb-2">{{ __('Select Batch Number') }}</label>
                                    <select class="form-select select2" name="batch_id" id="batch_id" style="width: 100%;">
                                        <option value="">{{ __('Loading batches...') }}</option>
                                    </select>
                                    <span class="text-danger form-error mt-1 d-block" id="batch_id_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-action mt-3 mb-3">
                            <button type="submit" class="btn btn-danger px-4 py-2" id="submitBtn">
                                <i class="fas fa-undo-alt me-2"></i> {{ __('Rollback Batch') }}
                            </button>
                            <button type="button" class="btn btn-warning px-4 py-2 ms-2 text-white" id="rollbackClaimsBtn">
                                <i class="fas fa-undo-alt me-2"></i> {{ __('Rollback Batch & its claims') }}
                            </button>
                            <button type="button" class="btn btn-dark px-4 py-2 ms-2" id="deleteBatchClaimsPermanentlyBtn">
                                <i class="fas fa-trash-alt me-2"></i> {{ __('Remove Batch & Claims Permanently') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(document).ready(function() {
        // Initialize Select2 for batch searchable dropdown
        $('#batch_id').select2({
            placeholder: 'Search and select a Batch Number',
            allowClear: true
        });

        // Load batches dynamically from get-rollback-batches endpoint
        function loadBatches() {
            $.ajax({
                url: "{{ url('get-rollback-batches') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status && response.data) {
                        var options = '<option value="">{{ __("Select Batch Number") }}</option>';
                        response.data.forEach(function(batch) {
                            options += '<option value="' + batch.id + '">' + batch.batch_number + '</option>';
                        });
                        $('#batch_id').html(options).trigger('change');
                    } else {
                        toastr.error('Failed to load batch numbers.');
                    }
                },
                error: function() {
                    toastr.error('Error occurred while fetching batches.');
                }
            });
        }

        loadBatches();

        // Form submit handler to hit the rollback batch endpoint
        $('#rollbackForm').on('submit', function(e) {
            e.preventDefault();
            
            var batchId = $('#batch_id').val();
            if (!batchId) {
                toastr.error('Please select a batch number.');
                return;
            }

            if (confirm('Are you sure you want to rollback this batch? This action is irreversible.')) {
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing Rollback...');
                
                $.ajax({
                    url: "{{ url('rollback-batch') }}",
                    type: "POST",
                    data: {
                        batch_id: batchId,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-undo-alt me-2"></i> {{ __("Rollback Batch") }}');
                        if (response.status) {
                            toastr.success(response.message || 'Batch rollback completed successfully.');
                            // Remove the rolled back batch option from the dropdown and reset
                            $('#batch_id').find('option[value="' + batchId + '"]').remove();
                            $('#batch_id').val('').trigger('change');
                        } else {
                            toastr.error(response.message || 'Validation or execution failed.');
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-undo-alt me-2"></i> {{ __("Rollback Batch") }}');
                        var errorMessage = 'An error occurred during rollback.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    }
                });
            }
        });

        // Click handler for rollback batch and its claims
        $('#rollbackClaimsBtn').on('click', function(e) {
            e.preventDefault();
            
            var batchId = $('#batch_id').val();
            if (!batchId) {
                toastr.error('Please select a batch number.');
                return;
            }

            if (confirm('Are you sure you want to rollback this batch and its claims? This action is irreversible.')) {
                $('#rollbackClaimsBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing Rollback...');
                
                $.ajax({
                    url: "{{ url('rollback-batch-with-claims') }}",
                    type: "POST",
                    data: {
                        batch_id: batchId,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#rollbackClaimsBtn').prop('disabled', false).html('<i class="fas fa-undo-alt me-2"></i> {{ __("Rollback Batch & its claims") }}');
                        if (response.status) {
                            toastr.success(response.message || 'Batch and claims rollback completed successfully.');
                            // Remove the rolled back batch option from the dropdown and reset
                            $('#batch_id').find('option[value="' + batchId + '"]').remove();
                            $('#batch_id').val('').trigger('change');
                        } else {
                            toastr.error(response.message || 'Validation or execution failed.');
                        }
                    },
                    error: function(xhr) {
                        $('#rollbackClaimsBtn').prop('disabled', false).html('<i class="fas fa-undo-alt me-2"></i> {{ __("Rollback Batch & its claims") }}');
                        var errorMessage = 'An error occurred during rollback.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    }
                });
            }
        });

        // Click handler for permanently removing batch and claims
        $('#deleteBatchClaimsPermanentlyBtn').on('click', function(e) {
            e.preventDefault();
            
            var batchId = $('#batch_id').val();
            if (!batchId) {
                toastr.error('Please select a batch number.');
                return;
            }

            if (confirm('Are you sure you want to permanently delete this batch and all its associated claims? This action will completely remove them from the system and is irreversible.')) {
                $('#deleteBatchClaimsPermanentlyBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('remove-batch-and-claims-permanently') }}",
                    type: "POST",
                    data: {
                        batch_id: batchId,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#deleteBatchClaimsPermanentlyBtn').prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i> {{ __("Remove Batch & Claims Permanently") }}');
                        if (response.status) {
                            toastr.success(response.message || 'Batch and claims permanently deleted successfully.');
                            // Remove the deleted batch option from the dropdown and reset
                            $('#batch_id').find('option[value="' + batchId + '"]').remove();
                            $('#batch_id').val('').trigger('change');
                        } else {
                            toastr.error(response.message || 'Validation or execution failed.');
                        }
                    },
                    error: function(xhr) {
                        $('#deleteBatchClaimsPermanentlyBtn').prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i> {{ __("Remove Batch & Claims Permanently") }}');
                        var errorMessage = 'An error occurred during deletion.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    }
                });
            }
        });
    });
</script>
@endsection
@endsection
