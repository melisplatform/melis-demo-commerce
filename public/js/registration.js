$(function() {
	$("#submit-registration-form-btn").on("click", function() {
		
		var btn = $(this);
		
		// Hidding all alerts
		$(".registration-form .alert").addClass("hidden");
		
		// convert the serialized form values into an array
		var datastring = $("form#registration").serializeArray();
		
		// Disable submit button
		btn.attr("disabled", true);
		
		// serialize the new array and send it to server
		datastring = $.param(datastring);

		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComLogin/submitRegistration',
	        data        : datastring,
	        dataType    : 'json',
	        encode		: true
		}).done(function(data){
			if(data.success){
				forms.clearDangerStatus(".registration-form");
				// Showing the Success result for submitting form
				$(".registration-form .alert-success").removeClass("hidden");
				// Reseting/make empty the Contact form
				$('form#registration')[0].reset();
				// redirect 
				if(data.redirect_link != ""){
					window.location = data.redirect_link;
				}
			}else{
				// Showing the Error result for submitting form
				$(".registration-form .alert-danger").removeClass("hidden");
				// Highlighting the input fields that has an error using the custom helper
                forms.checkForm(".registration-form", data.errors);
			}
			
			// Enable submit button
			btn.attr("disabled", false);
		});
	});
});