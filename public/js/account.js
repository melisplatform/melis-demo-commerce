$(function(){

	$("#save-profile, #save-delivery-address, #save-billing-address").click(function(){
		var form = $(this).data("form");
		var type = $(this).data("type");
		// Hidding all alerts
		$("#"+form+"-messages .alert").addClass("hidden");
		
		// convert the serialized form values into an array
		var datastring = $("form#"+form).serializeArray();
		if(datastring[0].name === "sel-delivery-address") {
            datastring.splice(0, 1);
		}
		// serialize the new array and send it to server
		datastring = $.param(datastring);
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComMyAccount/submit?form-type='+type,
	        data        : datastring,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				// Showing the Success result for submitting form
				$("#"+form+"-messages .alert-success").removeClass("hidden");
				
				
				if(type === 'profile'){
					// Reseting the password fields to empty after update
					$("#"+form+" input[type='password']").val('');
					// Updating the Header user name if profile updated
					updateUserHeaderName();
				}
				else {
					location.reload();
				}
			}else{
				// Showing the Error result for submitting form
				$("#"+form+"-messages .alert-danger").removeClass("hidden");
			}
			// Adding/removing Highlight the input fields that has an error using the custom helper
			forms.checkForm("#"+form, data.errors);
		});
		
	});
	
	function updateUserHeaderName()
	{
		$.ajax({
			type        : 'GET', 
	        url         : '/MelisDemoCommerce/ComMyAccount/getUserName',
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.personName != null){
				$("#person-name").text(data.personName);
			}
		});
	}

    $("select[data-selectaddress='select-address']").change(function() {
    	$(this).next("ul").remove();
        var id = $(this).val();
        var name = $(this).attr("name");
        var form = $(this).parents("form").attr("id");
        
        $("#"+form+" input:not([type='hidden']), #"+form+" fieldset select:not([name='"+name+"'])").val("");
        
		forms.clearDangerStatus(form);
		
        if(id === "new_address") {
            // $(form)[0].reset() is not working, trying alternatives below
            $("#"+form + " input[type='text'], #" + form + " input[name='cadd_id']").attr("value", "");
            // hide delete address button
			$("button.delete-address").hide();
        }
        else {
        	// show delete address button
            $("button.delete-address").show();
            // call an ajax event to render values
            id = parseInt(id);
            $.ajax({
                type : 'POST',
                url  : '/MelisDemoCommerce/ComMyAccount/getAddress',
                data : {id : id},
                dataType : 'json',
                encode : true
            }).success(function(data) {
                if(data.success) {
                    $("#" + form + " input[type='text'], #" + form + " input[name='cadd_id']").attr("value", "");
                    $.each(data.address, function(index, value){
    					if($("#"+form+" [name='"+index+"']").length){
    						$("#"+form+" [name='"+index+"']").val(value);
    					}
    				});
                }
            }).fail(function(error) {
				console.log(error);
            });
        }
    });

	$("button.delete-address").click(function() {
		var form = "form#" + $(this).parents("form").attr("id");
		var id   = $(form + " input[name='cadd_id']").val();
		if(id) {
            $(this).attr("disabled", "disabled");
            $.ajax({
                type: 'POST',
                url :  '/MelisDemoCommerce/ComMyAccount/deleteAddress',
                data: {id : id},
                dataType : 'json',
                encode: true
            }).success(function(data) {
                if(data.success) {
                    location.reload();
                }
                else {
                    // --> prompt error message
                }
            }).fail(function(error) {
                console.log(error);
            });
        }
	});

    setTimeout(function() {
        $("select#sel-delivery-address").trigger("change");
    },500);

});