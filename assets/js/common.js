$(function () {
   //$('[data-toggle="tooltip"]').tooltip()
}) 

$(".form-control, .form-select, .input-text").on("focus", function () {
	let fieldId = $(this).attr('id');
	if(fieldId=='state_ids'){
		$('#state_id' +'_error').text('');
	}
	$('#' + fieldId + '_error').text('');
});

$(document).on("select2:open", ".select2", function () {
	let fieldId = $(this).attr('id');
	if (fieldId) {
		$('#' + fieldId + '_error').text('');
	}
});

//$('.numericonly').keypress(function (e) {
$(document).on('keypress',  '.numericonly', function(e) {        	 
	var charCode = (e.which) ? e.which : event.keyCode  
	if (String.fromCharCode(charCode).match(/[^0-9]/g))  
		return false;                        
});    

$( document ).ready(function() {

	var today = new Date();
	$(".datepicker").datepicker({
        dateFormat: "dd-mm-yy",
        changeYear: true,
        changeMonth: true,
        //minDate: 0  
    });
 
	function showLoader() {
	    document.getElementById('loader').style.display = 'block';
	}

	// Function to hide loader
	function hideLoader() {
	    document.getElementById('loader').style.display = 'none';
	}
	
	$('.modal').on('hidden.bs.modal', function () {
		$(".form-error").html('');
		
	});

	 
	//Use in form
	// $('.duration').daterangepicker({
	// 	timePicker: true,
	// 	minDate: new Date(),
	// 	placeholder: "dd-mm-yyyy",
	// 	locale: {
	// 		format: 'DD-M-YYYY hh:mm A',
	// 	},
	// 	drops:"up"
	// });

	// //Use in search form
	// $('.sduration').daterangepicker({
  //   locale: {
  //     format: 'DD/MM/YYYY'
  //   }
  // });
  
  $('.sduration').val('');
  $('.sduration').attr("placeholder","Select Duration");
  
	
	

     $('.toggle-password').click(function () {
      const passwordInput = $('#password');
      const passwordFieldType = passwordInput.attr('type');

      if (passwordFieldType === 'password') {
        passwordInput.attr('type', 'text');
        $(this).html('<i class="fa fa-eye-slash" aria-hidden="true"></i>');
      } else {
        passwordInput.attr('type', 'password');
        $(this).html('<i class="fa fa-eye" aria-hidden="true"></i>');
      }
    });

    var initial_state_text = $("#state_id option:selected").text().trim().toLowerCase();
    if (initial_state_text === 'national') {
        $("#district_id").val('').prop('disabled', true);
    }
});

var countryId = $("#country_id").val();
if(countryId !=''){
	//getState(countryId);
}


$("#country_id").on('change',function(){
	var countryId = $(this).val();
	getState(countryId);
	//getCountryName(countryId);
});

var row_stateId=$("#state_id").attr("state-id");

function getState(countryId){
	var method = 'GET';
	var url = BASE_URL+'/getState/'+ countryId;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if (responseData) {
					var district_html = "<option value=''>Select</option>";
					$.each(responseData.data, function( index, value ) {
						district_html += "<option value="+value.id+">"+value.name+"</option>";
					}); 
					$("#state_id").html(district_html);
				}
				
				if(row_stateId !=''){
					$("#state_id").val(row_stateId);
				}
			  
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
}



if(row_stateId !=''){
	//getDistrict(row_stateId);
}
	

$("#state_id").on('change',function(){
	var stateId = $(this).val();
	var selectedText = $(this).find('option:selected').text().trim().toLowerCase();
	
	if (selectedText === 'national') {
		$("#district_id").val('').prop('disabled', true);
	} else {
		$("#district_id").prop('disabled', false);
		if (!stateId) {
			$("#district_id").html("<option value=''>Select</option>");
		} else {
	getDistrict(stateId);
		}
	}
});

var row_districtId=$("#district_id").attr("district-id");


function getDistrict(stateId){
	if (!stateId) {
		$("#district_id").html("<option value=''>Select</option>");
		return;
	}
	var method = 'GET';
	var url = BASE_URL+'/getDistrict/'+ stateId;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if (responseData) {
					var district_html = "<option value=''>Select</option>";
					$.each(responseData.data, function( index, value ) {
						district_html += "<option value="+value.id+">"+value.name+"</option>";
					}); 
					$("#district_id").html(district_html);
				}

				if(row_districtId !=''){
					$("#district_id").val(row_districtId);
				}
			  
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
}

function getCountryName(countryId){
	var method = 'GET';
	var url = BASE_URL+'/getCountryName/'+ countryId;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if(responseData.data[0].slug == 'india')
				{
					$('.district_label').text('');
					$('.district_label').text('District Name');
				}else{
					$('.district_label').text('');
					$('.district_label').text('City Name');
				}
					
				
			  
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
}




var categoryId = $("#category_id").val();
if(categoryId !=''){
	//getRole(categoryId);
}


$("#category_id").on('change',function(){
	var categoryId = $(this).val();
	//getRole(categoryId);
});

var row_roleId=$("#role_id").attr("role-id");
 
 function getRole(categoryId){
	var method = 'GET';
	var url = BASE_URL+'/getRole/'+ categoryId;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if (responseData) {
					var role_html = "<option value=''>Select</option>";
					$.each(responseData, function( index, value ) {
						role_html += "<option value="+value.id+">"+value.name+"</option>";
					}); 
					
					$("#role_id").html(role_html);
				}
				
				if(row_roleId !=''){
					$("#role_id").val(row_roleId);
				}
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
} 





if(row_roleId !=''){
	//getUsers(row_roleId);
}


$("#role_id").on('change',function(){
	var roleId = $(this).val();
	//getUsers(roleId);
});
	
	
var row_userId=$("#user_id").attr("user-id");

 function getUsers(roleId){
	var method = 'GET';
	var url = BASE_URL+'/getUsers/'+ roleId;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(resp) {
				if (resp) {
					var hdata = "<option value=''>Select</option>";
					$.each(resp, function( index, value ) {
				      hdata += "<option value="+value.id+">"+value.full_name+" ("+value.username+")"+"</option>";;
					}); 
					$("#user_id").html(hdata);
				}
				
				if(row_userId !=''){
					$("#user_id").val(row_userId);
				} 
			  
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
} 


function enableDeleteButton(deleteButtonClass, deleteRowPrefix, componentType) {
    var row_count = $("." + deleteButtonClass).length;
    $('.' + componentType + '_button').show();  
    if (row_count !== 0) { 
        if (row_count === 1) {

            $('.' + componentType + '_button:first').hide();      
        } else {     	      
            $("." + deleteButtonClass + '_addMore').not(':first').hide();
            $("." + deleteButtonClass + '_text').not(':first').html('Remove');
            $('.' + componentType + '_button:first').hide();  //delete
            $('.' + componentType + '_button').not(':first').show(); //delete
        }
    }
}

function deleteComponentMore(randomId, deleteRowPrefix, componentType,deleteButtonClass) { 
    $("#" + "remove_" + deleteRowPrefix + randomId).click(function () {
        var row_count = $("." + deleteButtonClass).length; 
        console.log(row_count);
        if (row_count > 1) {
            $("#" + deleteButtonClass + randomId).remove();
            enableDeleteButton(componentType + '_button', deleteRowPrefix, componentType);
        }
    });
} 


/*function getComponent(majorComponentID){
	var method = 'GET';
	var url = BASE_URL+'/getComponent/'+ majorComponentID;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if (responseData) {
					var component_html = "<option value=''>Select</option>";
					$.each(responseData, function( index, value ) {
						component_html += "<option value="+value.id+">"+value.name+"</option>";
					}); 
					$("#component_id").html(component_html);
				}		
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
}*/
/*function getComponent(majorComponentID, randomId = null) {
    var url = BASE_URL + '/getComponent/' + majorComponentID;

    $.ajax({
        url: url,
        method: 'GET',
        success: function(responseData) {
            if (responseData) {
                var component_html = "<option value=''>Select</option>";
                $.each(responseData, function(index, value) {
                    component_html += "<option value='" + value.id + "'>" + value.name + "</option>";
                });
                if (randomId !== null && randomId !== undefined) {
                    // dynamic row case → update specific component select
                    $("#component_id" + randomId).html(component_html);
                } else {
                    // normal page case → update default component select
                    $("#component_id").html(component_html);
                }
            }
        },
        error: function() {
            $("#spinner").html('<span class="text-danger">Something went wrong..</span>');
        }
    });
}*/

/*function getSubComponent(componentID){
	var method = 'GET';
	var url = BASE_URL+'/getSubComponent/'+ componentID;
		$.ajax({
			url: url,
			method: method,
			data: {},
			contentType: false,
			cache: false,
			processData: false,
			success: function(responseData) {
				if (responseData) {
					var component_html = "<option value=''>Select</option>";
					$.each(responseData, function( index, value ) {
						component_html += "<option value="+value.id+">"+value.name+"</option>";
					}); 
					$("#sub_component_id").html(component_html);
				}		
			},
			error: function() {
				$("#spinner").html('<span class="text-danger">Something went wrong..</span>');
			}
		});
		
}*/

/*function getSubComponent(componentID, randomId = null) {
    var url = BASE_URL + '/getSubComponent/' + componentID;

    $.ajax({
        url: url,
        method: 'GET',
        success: function(responseData) {
            if (responseData) {
                var component_html = "<option value=''>Select</option>";
                $.each(responseData, function(index, value) {
                    component_html += "<option value='" + value.id + "'>" + value.name + "</option>";
                });

                if (randomId !== null && randomId !== undefined) {
                    // dynamic row case → update specific component select
                    $("#sub_component_id" + randomId).html(component_html);
                } else {
                    // normal page case → update default component select
                    $("#sub_component_id").html(component_html);
                }
            }
        },
        error: function() {
            $("#spinner").html('<span class="text-danger">Something went wrong..</span>');
        }
    });
}*/

function getComponent(majorComponentID, randomId = null, selectedId = null, callback = null) {

    var url = BASE_URL + '/getComponent/' + majorComponentID;

    $.ajax({
        url: url,
        method: 'GET',
        success: function(responseData) {

            var component_html = "<option value=''>Select</option>";

            $.each(responseData, function(index, value) {
                component_html += "<option value='" + value.id + "'>" + value.name + "</option>";
            });

            let $component = (randomId !== null)
                ? $("#component_id" + randomId)
                : $("#component_id");

            $component.html(component_html);

            if (selectedId) {
                $component.val(selectedId).trigger('change');
            }

            if (callback) callback();
        }
    });
}

function getSubComponent(componentID, randomId = null, selectedId = null, callback = null) {

    var url = BASE_URL + '/getSubComponent/' + componentID;

    $.ajax({
        url: url,
        method: 'GET',
        success: function(responseData) {

            var html = "<option value=''>Select</option>";

            $.each(responseData, function(index, value) {
                html += "<option value='" + value.id + "'>" + value.name + "</option>";
            });

            let $sub = (randomId !== null)
                ? $("#sub_component_id" + randomId)
                : $("#sub_component_id");

            $sub.html(html);

            if (selectedId) {
                $sub.val(selectedId).trigger('change');
            }

            if (callback) callback();
        }
    });
}
