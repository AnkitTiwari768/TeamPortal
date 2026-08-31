@include('components.admin.header')

@include('components.admin.top-nav')

<div id="layoutSidenav">

    {{-- <div id="loader" class="center"></div> --}}

    <div id="ajax-loader" style="display: none;">
        <div class="spinner"></div>
    </div>

    @include('components.admin.side-nav')

    <div id="layoutSidenav_content">

        <main>

            @yield('page-content')

        </main>

        @include('components.admin.footer')

    </div>

</div>

@include('components.admin.popup.logout')
@include('components.admin.popup.confirm-delete')
@include('components.admin.popup.sms-email')

@include('components.admin.file-upload')


<script src="{{ asset('assets/ffo-admin/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/ffo-admin/js/scripts.js') }}"></script>
<script src="{{ asset('assets/js/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/js/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/js/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('toastr/toastr.min.js') }}"></script>
<script>
    // ── Toastr Global Defaults ────────────────────────────────────────
    toastr.options = {
        closeButton:       true,
        progressBar:       true,
        positionClass:     'toast-top-right',
        timeOut:           4000,
        extendedTimeOut:   1000,
        preventDuplicates: true,
    };
</script>
<style>
    /* ── Toastr colour fix — prevents project CSS from washing them white ── */
    #toast-container > .toast-success { background-color: #51a351 !important; color: #fff !important; }
    #toast-container > .toast-error   { background-color: #bd362f !important; color: #fff !important; }
    #toast-container > .toast-warning { background-color: #f89406 !important; color: #fff !important; }
    #toast-container > .toast-info    { background-color: #2f96b4 !important; color: #fff !important; }
    #toast-container > div            { opacity: 1 !important; }
</style>
<script type="text/javascript" src="{{ asset('assets/js/jquery.bootstrap-duallistbox.min.js') }}"></script>
<script src="{{ asset('assets/js/crypto-js.min.js') }}"></script>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/verification.js') }}"></script>

<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/validations.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>

<script src="{{ asset('assets/js/ripple.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>

<!-- Feather icons -->
<script src="{{ asset('assets/js/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/mustache.min.js') }}"></script>

<script src="{{ asset('assets/js/highchart/code/highcharts.js') }}"></script>

<script>
    feather.replace();
    $('[data-toggle="tooltip"]').tooltip();
</script>

<script>
    $("#from_date").datepicker({
        dateFormat: "dd-mm-yy",
        changeYear: true,
        changeMonth: true,
        //minDate: new Date(),
        onSelect: function(selected) {
            $("#to_date").datepicker("option", "minDate", selected)
        },
        onClose: function(selected) {
            oTable.ajax.reload();
            $("#reset_btn").show();
        }
    });
    $("#to_date").datepicker({
        dateFormat: "dd-mm-yy",
        changeYear: true,
        changeMonth: true,
        minDate: new Date(),
        onSelect: function(selected) {
            $("#from_date").datepicker("option", "maxDate", selected);
        },
        onClose: function(selected) {
            oTable.ajax.reload();
            $("#reset_btn").show();
        }
    });



    $.ripple('.wave-effect', {
        opacity: 0.4,
        color: "auto",
        multi: false,
        duration: 0.7,
        rate: function(pxPerSecond) {
            return pxPerSecond; // animation speed
        },
        easing: 'linear'
    });

    $('ul li').each(function() {
        if ($(this).find('.child.active').length > 0) {
            $(".child.active").closest(".collapse").addClass("show");
        }

    });


    function makeCategoryDropdown(selectId, apiUrl, disable = false) {

        var $select = $('#' + selectId);

        if (!$select.length) {
            //console.log('Select element not found: ' + selectId);
            return;
        }

        $select.empty();

        $.ajax({
            url: apiUrl,
            type: 'GET',
            success: function(data) {

                // Normalize response
                if (!Array.isArray(data)) {
                    var flat = [];
                    for (var key in data) {
                        flat = flat.concat(data[key]);
                    }
                    data = flat;
                }

                var hasExisting =
                    Array.isArray(existingCategoryId) &&
                    existingCategoryId.length > 0;

                // Append options
                data.forEach(function(item) {

                    var isSelected =
                        hasExisting &&
                        existingCategoryId.includes(item.id);

                    var option = new Option(
                        item.name,
                        item.id,
                        false,
                        isSelected
                    );

                    $(option).attr('data-aov', item.aov_grouping_type);
                    $select.append(option);
                });


                // Init Select2 only once
                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        width: '100%',
                        closeOnSelect: false, // checkbox feel
                        placeholder: 'Select product categories'
                    });
                }

                // Ensure preselected values visible
                if (hasExisting) {
                    $select.val(existingCategoryId).trigger('change.select2');
                }

                if (disable === true) {
                    $select.prop('disabled', true);
                }
            },
            error: function() {
                alert('Something went wrong while loading categories.');
            }
        });
    }
</script>



@yield('js')
@stack('js')


</body>

</html>
