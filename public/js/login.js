$(function() {
	$("#submit-login-form-btn").on("click", function() {
		var btn = $(this);
		
			// Hidding all alerts
			$(".login-form .alert").addClass("hidden");
			
			// convert the serialized form values into an array
			var datastring = $("form#login").serializeArray();
			
			// serialize the new array and send it to server
			datastring = $.param(datastring);

			$.ajax({
				type        : 'POST', 
				url         : '/MelisDemoCommerce/ComLogin/submitLogin',
				data        : datastring,
				dataType    : 'json',
				encode		: true
			}).done(function(data) {
				console.log(`data.success: `, data.success);
				if ( data.success ) {
					// Showing the Success result for submitting form
					$(".contact-info .alert-success").removeClass("hidden");
					// Reseting/make empty the Contact form
					$('form#login')[0].reset();
					// Redirect after login authentication success
					window.location = data.redirect_link;
				}
				else {
					// Showing the Error result for submitting form
					$(".login-form .alert-danger").removeClass("hidden");
					// Highlighting the input fields that has an error using the custom helper
					forms.checkForm(".login-form", data.errors);
				}
			});
	});
});