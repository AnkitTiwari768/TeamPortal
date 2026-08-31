@extends('components.admin.content-layout')

@section('action-header')

	@php

				$addUserUrl = url('roles/create');

	@endphp

	@if (acl(config('permissions.user-create')))
		<div class="btn-group drop-btn">
				<button
					type="button"
					class="btn btn-primary"
					autocomplete="off"
				  onclick="window.location = '{{$addUserUrl}}'">
				  	<img src="{{asset('assets/ffo-admin/img/add.svg')}}" />
				  	{{ __('message.add_role') }}
				</button>
		</div>
	@endif

@endsection

@section('card-content')

		 <div class="card-body">
			<form id="search_form" autocomplete="off">
			    <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
					<div class="select-box">
						<label>{{ __('message.role_module') }}</label>
						{{ Form::select('role_id',role_list(),'', ['class' => 'form-select filter_btn', 'id' => 'role_id']) }}
					</div>

					<div class="select-box">
						<label>{{ __('message.status') }}</label>
						{{ Form::select('status', array(""=>__('message.select'))+status_list(),'', ['class' => 'form-select filter_btn', 'id' => 'status']) }}
					</div>
				   <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
			  </div>
			</form>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('message.role_name') }}</th>
							<th>{{ __('message.assigned_permissions') }}</th>
							<th>{{ __('message.status') }}</th>
							  @if (acl(config('permissions.role-update')) || acl(config('permissions.permission-button-view')) || acl(config('permissions.role-view')))
								<th class="actions">{{ __('message.action') }}</th>
							@endif
						</tr>
					</thead>
				</table>
			</div>
		</div>

@section('js');
<script>
$(document).ready(function () {

    // ========================================
    // DATATABLE EVENTS FOR LOADER
    // ========================================
    function showLoader() {
        $('#ajax-loader').show();
        $('#dataTable_wrapper').addClass('blur-background');
    }

    function hideLoader() {
        $('#ajax-loader').hide();
        $('#dataTable_wrapper').removeClass('blur-background');
    }

    // Show loader initially
    showLoader();

    $(document).on('preXhr.dt', '#dataTable', function() {
        showLoader();
    });

    $(document).on('draw.dt', '#dataTable', function() {
        hideLoader();
    });

    $(document).on('xhr.dt', '#dataTable', function(e, settings, json, xhr) {
        if (xhr && xhr.status !== 200) {
            hideLoader();
        }
    });

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    function getTable() {
        return $('#dataTable').DataTable();
    }

    $(document).on('init.dt', '#dataTable', function() {
        var oTable = getTable();
        var searchInput = $('.dataTables_filter input');

        // Remove existing event handlers
        searchInput.off('keyup input');

        // Apply debounced search
        searchInput.on('keyup input', debounce(function() {
            showLoader();
            oTable.search(this.value).draw();
        }, 500));
    });

    dataTableInit({
        id: "#dataTable",
        showExcelExport: true,
        order: {
            column: 1,
            direction: "asc"
        },
        url: "{{ url('roles/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function(data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.name;
                }
            },
            {
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span class="badge bg-info">' + row.permission_count + '</span>';
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return createActivationLabel(row.status);
                }
            },
            @if (acl(config('permissions.role-update')) || acl(config('permissions.permission-button-view')) || acl(config('permissions.role-view')))
            {
                "orderable": false,
                "render": function (data, type, row) {
                    var pview='';
                    @if (acl(config('permissions.role-view')))
                        pview=buttonView("{{ url('roles') }}", row.id);
                    @endif

                    var pedit='';
                    @if (acl(config('permissions.role-update')))
                        pedit=buttonEdit("{{ url('roles') }}", row.id);
                    @endif
                    var ppermission='';

                    @if (acl(config('permissions.permission-button-view')))
                        var ppermission= buttonRolePermission("{{ url('role-permission') }}", row.id);
                    @endif

                    var actions=createActionButtons([pview,pedit,ppermission]);
                    return actions;
                }
            }
            @endif
        ],
        filters: ["role_id","status"]
    });

    // Hide loader immediately if the table ends up not firing ajax for some reason, though it should.
    // We already handle this with draw.dt.

});
</script>

<style>
/* Blur effect on the table wrapper */
#dataTable_wrapper.blur-background {
    filter: blur(4px);
    pointer-events: none;
    user-select: none;
    transition: filter 0.3s ease;
}

#dataTable_wrapper {
    transition: filter 0.3s ease;
}

/* Hide default Datatables processing label */
.dataTables_processing {
    display: none !important;
}
</style>
@endsection
@endsection
