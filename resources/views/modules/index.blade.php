@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid">
	<div class="card mb-4">
		 <div class="card-header d-flex justify-content-between">
			<h6 class="m-0 box-heading heading-1">{{ __('message.module_list') }} </h6>
      @if (acl(config('permissions.module-create'))) 
			  <a href="{{ url('modules/create') }}" class="btn btn-info wave-effect"> <span class="btn-label"> <?php echo __('message.plus'); ?> </span> {{ __('message.add_module') }}</a>
      @endif
		 </div>

	
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('message.module_name') }}</th>
							<th>{{ __('message.url') }}</th>
							<th>{{ __('message.icon') }}</th>
							<th>{{ __('message.status') }}</th>
							<th>{{ __('message.sort_order') }}</th>
							@if (acl(config('permissions.module-update')))
								<th class="actions">{{ __('message.action') }}</th>
							@endif  
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>

@section('js');
<script>
$(document).ready(function () {

    // ========================================
    // AJAX LOADER (initial load + every fetch/filter)
    // ========================================
    function showLoader() {
        $('#ajax-loader').show();
        $('#dataTable_wrapper').addClass('blur-background');
    }

    function hideLoader() {
        $('#ajax-loader').hide();
        $('#dataTable_wrapper').removeClass('blur-background');
    }

    showLoader();

    $(document).on('preXhr.dt', '#dataTable', function () {
        showLoader();
    });

    $(document).on('draw.dt', '#dataTable', function () {
        hideLoader();
    });

    $(document).on('xhr.dt', '#dataTable', function (e, settings, json, xhr) {
        if (xhr && xhr.status !== 200) {
            hideLoader();
        }
    });

    // ========================================
    // MODULE NAME FILTER DROPDOWN (loaded via AJAX)
    // ========================================
    function getTable() {
        return $('#dataTable').DataTable();
    }

    function reloadTable() {
        getTable().ajax.reload(null, false);
    }

    function debounce(func, wait) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                func.apply(context, args);
            }, wait);
        };
    }

    var $moduleSelect = $('#module_id');

    // Searchable, wider Module Name dropdown (search box is Select2's default behaviour
    // for a single select once initialised).
    $moduleSelect.select2({
        width: '320px',
        placeholder: '{{ __('message.select') }}'
    });

    $.ajax({
        url: "{{ url('modules/name-list') }}",
        method: 'GET',
        dataType: 'json'
    }).done(function (response) {
        var modules = (response && response.data) ? response.data : [];

        modules.forEach(function (module) {
            $moduleSelect.append($('<option>', { value: module.id, text: module.name }));
        });

        $moduleSelect.trigger('change');
    });

    var debouncedReload = debounce(function () {
        reloadTable();
        $('#reset_btn').toggle(!!$('#module_id').val());
    }, 300);

    $('#module_id').on('change', debouncedReload);

    $('#reset_btn').on('click', function () {
        $moduleSelect.val('').trigger('change.select2');
        $(this).hide();
        reloadTable();
    });

    // ========================================
    // DATATABLE INITIALIZATION (existing behaviour, unchanged)
    // ========================================
    dataTableInit({
    id: "#dataTable",
	showExcelExport: true,
    order: {
      column: 1,
      direction: "asc"
    },
    url: "{{ url('modules/datalist') }}",
    filters: ["module_id"],
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
        "orderable": true,
        "render": function(data, type, row) {
            return row.url;
        }
      },
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.icon;
        }
      },
	   
      {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.status);
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.sort_order;
        }
      },
	    @if (acl(config('permissions.module-update')) )
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';
          @if (acl(config('permissions.module-update'))) 
            pedit=buttonEdit("{{ url('modules') }}", row.id); 
          @endif
          var actions=createActionButtons([pedit]);

          return actions;
        }
      }
      @endif
    ]
  });

}); // END DOCUMENT READY
</script>

<style>
#dataTable_wrapper.blur-background {
    filter: blur(4px);
    pointer-events: none;
    user-select: none;
    transition: filter 0.3s ease;
}

#dataTable_wrapper {
    transition: filter 0.3s ease;
}

.dataTables_processing {
    display: none !important;
}

#module_id_box {
    min-width: 320px;
}

#module_id + .select2-container.select2-container--default {
    width: 320px !important;
    min-width: 320px !important;
    max-width: 100%;
}
</style>
@endsection
@endsection

