 <script>
	function applyYearRestriction(setDefaultDates = true) {
		const year = $('#year').val();

		if (!year) {
			$('#from_date_new, #to_date_new').datepicker('option', {
				minDate: null,
				maxDate: null,
				yearRange: 'c-10:c+10'
			});

			$('#from_date_new, #to_date_new').val('');
			return;
		}

		const minDate = new Date(year, 0, 1); // 01-01-YYYY
		const maxDate = new Date(year, 11, 31); // 31-12-YYYY

		$('#from_date_new, #to_date_new').datepicker('option', {
			minDate: minDate,
			maxDate: maxDate,
			yearRange: year + ':' + year
		});

		// Set default From & To dates
		if (setDefaultDates) {
			$('#from_date_new').datepicker('setDate', minDate);
			$('#to_date_new').datepicker('setDate', maxDate);
		}
	}

	$(document).ready(function() {
		const defaultYear = '{{ $selectedYear }}';

		// Initialize datepickers
		$("#from_date_new, #to_date_new").datepicker({
			dateFormat: "dd-mm-yy",
			changeYear: true,
			changeMonth: true,
			beforeShow: function() {
				const year = $('#year').val();
				if (!year) return;

				// Open calendar in selected year
				$(this).datepicker('option', 'defaultDate', new Date(year, 0, 1));
			}
		});

		// Set default year on load
		if (defaultYear) {
			$('#year').val(defaultYear);
			applyYearRestriction(true);
		}

		// Apply restriction on year change
		$('#year').on('change', function() {
			applyYearRestriction(true);
		});
	});
</script>