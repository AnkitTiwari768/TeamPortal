$(document).ready(function(){

    $('.capitalize').css('textTransform', 'capitalize');


	$(".checkTextArea").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^([a-z0-9A-Z~`:;'+=!@#$%&*_\s\.\,\/\\-\\() ]+)$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	$(".checkEditor").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^([a-z0-9A-Z~`:;'+=!@#$%&*_\s\<\>\.\,\/\\-\\() ]+)$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	
	$(".txtMix").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z.\\-\\() ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	
	$(".txtOnly").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	$(".txtHashOnly").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z#]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});

	$(".txtCommaOnly").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z, ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	

	$(".txtDash").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z\\-\\ ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	//$(".alphaNumeric").on("keypress keyup blur input",function (e) {
	$(document).on('keypress',  '.alphaNumeric', function(e) {        
		  var regex = new RegExp("^[a-zA-Z0-9]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	$(".alphaNumericSpace").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z0-9 ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});

	$(".txtnumericMix").on("keypress keyup blur input",function (e) {
		var regex = new RegExp("^[a-zA-Z0-9.\\-\\() ]+$");
		var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		  if (!regex.test(key)) {
			 e.preventDefault();
			 return false;
		  }
	  return true;
  	});
		

	$(".integer").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[0-9]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});

	$(".txtnumerichypenapercend").on("keypress keyup blur input", function (e) {
		var regex = new RegExp("^[a-zA-Z0-9\-& ]+$"); // added space before closing ]
		var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
	
		if (!regex.test(key)) {
			e.preventDefault();
			return false;
		}
		return true;
	});

	$(document).ready(function () {
		$(".txtnumerichypenSlashUnederscoreComma").on("input", function () {
			// Allow only: letters, numbers, hyphen, slash, underscore, comma (NO spaces)
			var clean = $(this).val().replace(/[^a-zA-Z0-9\-\/_,]/g, '');
			$(this).val(clean);
		});
	});
	
	
	$(".numeric").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("[0-9.]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	
	$(document).on('keyup','.szna',function(){ //starting_zero_not_allow
		if($(this).val() < 1)
		{
			$(this).val('');
		}else{
			
		}
	});


	$(document).on('keydown keyup change','.sza',function(e){ //starting_zero_allow
		if (this.value.length == 2 )
		{
			if($(this).val() < 1)
			{
				$(this).val('');
			}
		}
	});
	
	
	$( document ).on( 'focus', ':input', function(){
		$( this ).attr( 'autocomplete', 'off' );
	});

	$(".notzero").on("input", function() {
		if (/^0/.test(this.value)) {
		  this.value = this.value.replace(/^0/, "")
		}
	  })
	  
	  $(".udyamNumber").on("keypress keyup blur input",function (e) {
		var regex = new RegExp("^[A-Z0-9-]+$");
		var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		  if (!regex.test(key)) {
			 e.preventDefault();
			 return false;
		  }
	  return true;
  	});

	

	  $(".numeric2decimal").on("input", function () {
		var val = $(this).val();
	
		// 1. Remove invalid chars (only digits and . allowed)
		val = val.replace(/[^0-9.]/g, '');
	
		// 2. Allow only one decimal point
		val = val.replace(/(\..*?)\..*/g, '$1');
	
		// 3. Limit to 2 decimal places
		if (val.indexOf('.') >= 0) {
			val = val.substring(0, val.indexOf('.') + 3);
		}
	
		// 4. Limit total length (15 incl. decimals)
		if (val.length > 15) {
			val = val.substring(0, 15);
		}
	
		$(this).val(val);
	});

	 

	
	

	
});
