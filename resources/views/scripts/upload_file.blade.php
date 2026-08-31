<script>
/**
 * Generic Ajax File Upload
 *
 * @param {Object} options
 */
function uploadFile(options) {

    const settings = $.extend({
        input: null,
        url: '',
        fieldName: 'file',
        multiple: false,
        loader: null,

        beforeUpload: function () {},
        success: function () {},
        error: function () {},
        complete: function () {}
    }, options);

    const files = settings.input[0].files;

    if (!files.length) {
        return;
    }

    const formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (settings.multiple) {
        [...files].forEach(file => formData.append(settings.fieldName + '[]', file));
    } else {
        formData.append(settings.fieldName, files[0]);
    }

    settings.beforeUpload();

    settings.loader?.removeClass('d-none');

    $.ajax({
        url: settings.url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        success(response) {

            if (response.status) {

                toastr.success(
                    response.message ||
                    "{{ __('message.file_uploaded_successfully') }}"
                );

                settings.success(response);

            } else {

                toastr.error(
                    response.message ||
                    "{{ __('message.something_went_wrong') }}"
                );

                settings.error(response);

            }

        },

        error(xhr) {

            settings.input.val('');

            if (xhr.status === 422) {

                $.each(xhr.responseJSON.errors, function (field, messages) {

                    messages.forEach(function (message) {
                        toastr.error(message);
                    });

                });

            } else {

                toastr.error("{{ __('message.something_went_wrong') }}");

            }

            settings.error(xhr);

        },

        complete() {

            settings.loader?.addClass('d-none');

            settings.complete();

        }

    });

}
</script>
