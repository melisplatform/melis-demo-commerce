$(function() {
	$("#submit-fake-paypal-style-form-btn").click(function() {
		$(".fake-paypal-style-form-alert").addClass("hidden");

		var dataString = new Array();
		dataString = $("#fake-paypal-style-form").serializeArray();

		// validating th paypal form
		$.ajax({
			type: "POST",
			url: "/MelisDemoCommerce/FakeServerPayment/validateFakePaypalForm",
			data: dataString,
			dataType: "json",
			encode: true,
		}).success(function(data) {
			// Server to server "IPN" call
			if (data.success) {
				$.ajax({
					type: "POST",
					url: "/MelisDemoCommerce/ComCheckout/confirmPayment",
					data: data.result,
					dataType: "json",
					encode: true,
				}).success(function() {
					// Redirecting to confirm page url provided
					window.location = data.result["checkout-confirm-url"];
				});
			} else {
				console.log("shitss");
				// Showing the Error result for submitting form
				$(".fake-paypal-style-form-alert").removeClass("hidden");
			}
		});
	});
});
