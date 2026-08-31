$(".form-control").focus(function () {
    $(this).parent('div').find('.form-error').text('');
});
function disableSubmit() {
    $('button[type="submit"]').attr("disabled", "disabled");
}

function enableSubmit() {
    $('button[type="submit"]').removeAttr("disabled");
}

function redirect(redirectTo) {
    setTimeout(function () {
        location.href = redirectTo;
    }, 800);
}

function failure(responseData) {
    toastr.error(responseData.message);
    setTimeout(function () {}, 1000); 
}

function applyValidationErrors(responseData) {
    for (var error in responseData.errors) {
        var errorMessage = responseData.errors[error][0];
        $(`#${error}_error`).text(errorMessage);
    }
}

function success(responseData) {
    toastr.success(responseData.message);
    setTimeout(function () {}, 1000); 
}


function sendRequest(requestData, redirectTo, isLogout = false) {
    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
		dataType: 'json',
		data:requestData.body, 
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function (responseData) {
            if (responseData.status) {
                success(responseData);
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }
                redirect(redirectTo);
            }
	    else if (!responseData.status && responseData.errors.length == 0) {
               failure(responseData);
	           enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                applyValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        }
    })
}