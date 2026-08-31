@include('dashboard.Role-swtich-tab')

{{-- Mobile-only toggle bar: shows the active filter selection and expands/collapses the form below --}}
<div class="dashboard-filter-toggle d-md-none" id="dashboardFilterToggle"
	role="button" aria-expanded="false" aria-controls="dashboardFilterCollapse">
	<span class="dashboard-filter-toggle-label"><i class="fa fa-filter"></i> Filters</span>
	<span class="dashboard-filter-selection" id="dashboardFilterSelection">All Time</span>
	<i class="fa fa-chevron-down dashboard-filter-toggle-icon"></i>
</div>

<div class="collapse d-md-block" id="dashboardFilterCollapse">
	<div class="row mb-3">
		<div class="col-md-3">
			<label>Year</label>
			{!! Form::select('year', year_list(), null, [
				'class' => 'form-select',
				'id' => 'year',
				'onchange' => 'reloadAllCharts(false)',
			]) !!}
		</div>

		<div class="col-md-3">
			<label>From Date</label>
			<input type="text" id="from_date_new" class="form-control" placeholder="dd-mm-yyyy" readonly>
		</div>

		<div class="col-md-3">
			<label>To Date</label>
			<input type="text" id="to_date_new" class="form-control" placeholder="dd-mm-yyyy" readonly>
		</div>

		<div class="col-md-3 align-self-end d-flex gap-2">
			<button id="filterSearch" class="btn btn-primary w-50">Search</button>
			<button id="filterReset" class="btn btn-secondary w-50">Reset</button>
		</div>
	</div>
</div>

<script>
	(function () {
		function currentSelectionText() {
			var year = $('#year').val();
			var from = $('#from_date_new').val();
			var to = $('#to_date_new').val();
			if (from && to) return from + ' - ' + to;
			if (year) return 'Year: ' + year;
			return 'All Time';
		}

		function updateFilterSelectionBadge() {
			$('#dashboardFilterSelection').text(currentSelectionText());
		}

		function getFilterCollapseInstance() {
			var el = document.getElementById('dashboardFilterCollapse');
			return bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
		}

		$(document).ready(function () {
			updateFilterSelectionBadge();

			$('#dashboardFilterToggle').on('click', function () {
				getFilterCollapseInstance().toggle();
			});

			$('#filterSearch').on('click', function () {
				updateFilterSelectionBadge();
				if (window.innerWidth < 768) {
					getFilterCollapseInstance().hide();
				}
			});

			$('#filterReset').on('click', function () {
				setTimeout(updateFilterSelectionBadge, 0);
			});

			$('#year').on('change', updateFilterSelectionBadge);

			$('#dashboardFilterCollapse')
				.on('show.bs.collapse', function () {
					$('.dashboard-filter-toggle').addClass('open').attr('aria-expanded', 'true');
				})
				.on('hide.bs.collapse', function () {
					$('.dashboard-filter-toggle').removeClass('open').attr('aria-expanded', 'false');
				});
		});
	})();
</script>