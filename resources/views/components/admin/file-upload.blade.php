<script>
    var docs = [];
    function asyncFileUpload(asyncFileUploadOptions) {
        const {
            url,
            selector,
            fileFieldName,
            formData,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
            isMultiple,
            showFileName
        } = asyncFileUploadOptions;

        $.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (element) { }, false);
                return xhr;
            },
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $(progressElementSelector).show();
                $(loaderElementSelector).html(
                    `<i class="fa fa-spinner fa-spin"></i> <span>${loadingContent}</span>`);

            },
            success: function (response) {
                console.log(response);
                if (response.status) {
                    var data = response.data;
                    if (isMultiple) {
                        var documents = $(hiddenInputSelector).val();

                        documents = JSON.parse(documents);
                        documents.push(data.file_system_name);
                        $(hiddenInputSelector).val(JSON.stringify(documents));
                        var children = '';
                        var input = $(selector).prop('files');
                        var downloadLink = `${BASE_URL}/download-file/` + response.data.id;
                        //console.log(downloadLink);
                        children += '<li id ="' + response.data.id + '" uploaded-doc-name =' + response.data.file_system_name + '>' + input[0]['name'] + '<a target = "_blank" href=' + downloadLink + '><img src="{{ asset('assets/img-new/download-ico-p.svg')}}"></a><a class="remove-doc"><img src="{{ asset('assets/img-new/delete-ico-p.svg')}}"></a></li>';
                        /*To make code generic for all module*/
                        $(selector).closest('table').next('ul#fileList').append(children);
                        /*$("#fileList").append(children);*/
                    } else {
                        var fileType = selector.replace("#", "");
                        hyperLink = `<a href="${BASE_URL}/${data?.file_path}" download="${data?.file_name}">${data?.file_name}<img src="{{ asset('assets/img-new/download-ico-p.svg')}}"></a>
                        <a class="remove-doc" onclick="event.stopPropagation(); deleteFile('${data?.file_system_name}','${fileType}')"><img src="{{ asset('assets/img-new/delete-ico-p.svg')}}"></a>`;
                        $(showFileName).html(hyperLink);
                        $(hiddenInputSelector).val(data?.file_system_name);
                    }

                    toastr.success(successMessage);
                    $(progressElementSelector).delay(1000).fadeOut('slow');

                } else {
                    $(progressElementSelector).delay(1000).fadeOut('slow');
                    /*changes start by Abhishek*/
                    var prevValue = $(hiddenInputSelector).val();
                    $(hiddenInputSelector).val(prevValue);
                    /*changes end by Abhishek*/
                    var errors = response.errors;
                    console.log(errors);
                    toastr.error(errors.file[0]);
                    $(selector).val('')
                    return;
                }
            },
            error: function (response) {
                $(progressElementSelector).delay(1000).fadeOut('slow');
                if (response.responseJSON && response.responseJSON.message) {

                    /*changes start by Abhishek*/
                    //$(hiddenInputSelector).val('');
                    var prevValue = $(hiddenInputSelector).val();
                    $(hiddenInputSelector).val(prevValue);
                    /*changes end by Abhishek*/
                    //toastr.error(errorMessage);
                    toastr.error(response.responseJSON.message);
                } else if (response.responseJSON && response.responseJSON.errors && Object.keys(response.responseJSON.errors).length > 0) {
                    var errors = response.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    toastr.error(errors[firstKey][0]);
                } else {
                    toastr.error('The file should match the given instructions.');
                }
                $(selector).val('')
                return;
            }
        });
    }

    function fileUploadWithLoader(fileUploadOptions) {
        const {
            url,
            selector,
            fileFieldName,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
            isMultiple,
            showFileName
        } = fileUploadOptions;
        //console.log(fileUploadOptions);
        const images = $(selector)[0].files;

        if (!images.length > 0) {
            throw new Error("Invalid file upload..");
        }

        const formData = new FormData();

        formData.append(fileFieldName, images[0]);
        formData.append('_token', '{{ csrf_token() }}');

        asyncFileUpload({
            url,
            selector,
            fileFieldName,
            formData,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
            isMultiple,
            showFileName
        });
    }


</script>