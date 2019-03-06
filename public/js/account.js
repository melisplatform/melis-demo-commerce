$(function(){
	
	/**
	 * User Profile
	 */
	$("#save-profile").click(function(){
		var form = "account-profile-form";
		// Hidding all alerts
		$("#"+form+"-messages .alert").addClass("hidden");
		
		// convert the serialized form values into an array
		var datastring = $("form#"+form).serializeArray();
		
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComMyAccount/saveProfile',
	        data        : datastring,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				// Showing the Success result for submitting form
				$("#"+form+"-messages .alert-success").removeClass("hidden");
				
				// Reseting the password fields to empty after update
				$("#"+form+" input[type='password']").val('');
				
				if(data.personName){
					$("#person-name").text(data.personName);
				}
			}else{
				// Showing the Error result for submitting form
				$("#"+form+"-messages .alert-danger").removeClass("hidden");
			}
			// Adding/removing Highlight the input fields that has an error using the custom helper
			forms.checkForm("#"+form, data.errors);
		});
	});
	
	/**
	 * User Addresses
	 */
	$("#select_delivery_addresses, #select_billing_addresses").on("change", function(e){
		
		var type = $(this).data("type");
        // Clearing all error messages of the form
		forms.clearDangerStatus("#"+type+"-address-form");
		// Hidding all alerts
		$("#"+type+"-address-form-messages .alert").addClass("hidden");
		
		// Clearing all fields of the form
		$("#"+type+"-address-form input:not([name='"+type+"_address_save_submit']), #"+type+"-address-form fieldset select").val("");
		
		if($(this).val() === "new_address"){
			// hidding Delete button
			$(".delete-address[data-type='"+type+"']").hide();
		}else{
			$("#select_"+type+"_address").trigger("submit");
		}
		
		e.preventDefault();
	});
	
	$("#select_delivery_address,#select_billing_address").on("submit", function(e) {
        var dataString = $(this).serializeArray();
        
        var type = $(this).data("type");
        forms.suspendSubmit("#"+type+"-address-form");
        dataString.push({
			name : "type",
			value : type
		});
        
        $.ajax({
            type: 'POST',
            url: '/MelisDemoCommerce/ComMyAccount/getAddress',
            data: dataString,
            dataType: 'json',
            encode: true,
        }).success(function(data) {
            $.each(data.formData, function(index, value){
				if($("form#"+type+"-address-form [name='"+index+"']").length){
					$("form#"+type+"-address-form [name='"+index+"']").val(value);
				}
			});
            
            // Showing Delete button
			$(".delete-address[data-type='"+type+"']").show();
        }).error(function(err) {
            console.log(err);
        });
        
	    e.preventDefault();
    });
	
	$(".save-address").on("click", function(){
		var type = $(this).data("type");
		$("form#"+type+"-address-form").trigger("submit");
	});
	
	$("form#delivery-address-form, form#billing-address-form").on("submit", function(e) {
		var dataString = $(this).serializeArray();
		
		var type = $(this).data("type");
		dataString.push({
			name : "type",
			value : type
		});
		
		// Hidding all alerts
		$("#"+type+"-address-form-messages .alert").addClass("hidden");
		
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComMyAccount/submitAddress',
	        data        : dataString,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				// Showing the Success result for submitting form
				$("#"+type+"-address-form-messages .alert-success").removeClass("hidden");
				$("#"+type+"-address-form-messages .alert-success span").text(data.message);
				// Refreshing select options
				$("#select_"+type+"_addresses option").remove();
				$.each(data.selectAddresses, function(index, value){
					$("#select_"+type+"_addresses").append($('<option>', { 
				        value: index,
				        text : value 
				    }));
				});
				// Selecting the new added address
				$("#select_"+type+"_addresses").val(data.selectedId);
				// Set value to address id on the form
				$("#"+type+"-address-form").find("input[name='cadd_id']").val(data.selectedId);
				
				// Showing Delete button
				$(".delete-address[data-type='"+type+"']").show();
			}else{
				// Showing the Error result for submitting form
				$("#"+type+"-address-form-messages .alert-danger").removeClass("hidden");
			}
			
			// Adding/removing Highlight the input fields that has an error using the custom helper
			forms.checkForm("#delivery-address-form", data.errors);
		});
		
		e.preventDefault();
	});
	
	$("button.delete-address").click(function() {
		
		var btn = $(this);
		
		var type = $(this).data("type");
		
		var dataString = $("#select_"+type+"_address").serializeArray();
		
		var cadd_id   = $("#"+type+"-address-form").find("input[name='cadd_id']").val();
		dataString.push({
			name : "cadd_id",
			value : cadd_id,
		});
		
		
		dataString.push({
			name : "type",
			value : type,
		});
		
		dataString.push({
			name : type+"_address_delete_submit",
			value : true,
		});
		
		// Hidding all alerts
		$("#"+type+"-address-form-messages .alert").addClass("hidden");
		
		if(cadd_id) {
			btn.attr("disabled", true);
            $.ajax({
                type: 'POST',
                url :  '/MelisDemoCommerce/ComMyAccount/submitAddress',
                data: dataString,
                dataType : 'json',
                encode: true
            }).success(function(data) {
                if(data.success) {
                	// Showing the Success result for submitting form
    				$("#"+type+"-address-form-messages .alert-success").removeClass("hidden");
    				$("#"+type+"-address-form-messages .alert-success span").text(data.message);
    				// Refreshing select options
    				$("#select_"+type+"_addresses option").remove();
    				$.each(data.selectAddresses, function(index, value){
    					$("#select_"+type+"_addresses").append($('<option>', { 
    				        value: index,
    				        text : value 
    				    }));
    				});
    				// Selecting the new added address
    				$("#select_"+type+"_addresses").val(data.selectedId);
    				// Set value to address id on the form
    				$("#"+type+"-address-form").find("input[name='cadd_id']").val(data.selectedId);
    				
    				// Clearing all fields of the form
    				$("#"+type+"-address-form input:not([name='"+type+"_address_save_submit']), #"+type+"-address-form fieldset select").val("");
    				
					$.each(data.formData, function(index, value){
    					if($("form#"+type+"-address-form [name='"+index+"']").length){
    						$("form#"+type+"-address-form [name='"+index+"']").val(value);
    					}
    				});
					
					if(data.showDeleteButton){
						btn.show();
					}else{
						btn.hide();
					}
                }
                btn.attr("disabled", false);
            }).fail(function(error) {
                console.log(error);
                btn.attr("disabled", false);
            });
        }
	});

	//order history plugin
    $('body').on('click','.order-history a', function(){
        if(!$(this).hasClass("disabled")){
            var obj = {};
            obj.order_history_current = $(this).attr('data-page-number');

            var dataString = $.param(obj);

            $.ajax({
                type        : 'POST',
                url         : '/MelisDemoCommerce/ComMyAccount/orderHistoryPaginationRenderer',
                data        : dataString,
                dataType    : 'json',
                encode		: true
            }).success(function(data){
                if(data.orderHistory !== ''){
                    // Reloading header cart with new content
                    $("#order-history").html(data.orderHistory);
                }
            });
        }
    });

    // open order
    $('body').on('click', '.orderhist-table-cell', function() {
    	$(this).closest('.table-row').find('a')[0].click();
    });

    // download invoice on order history
    $('body').on('click', '.orderhist-table-download-invoice', function() {
    	let $downloadInvoice = $(this);
    	let url = '/CommerceOrderInvoice/getInvoice';
    	let xhr = new XMLHttpRequest();
    	let invoiceId = $(this).val();
    	let params = 'invoiceId=' + invoiceId;
    	let refNum = $(this).closest('.table-cell').siblings('.ref-num').text();

		$downloadInvoice.closest('.p-checkarea').siblings('.invoice-alert').css('display', 'none');

    	if (invoiceId > 0) {
			xhr.open('POST', url);
			xhr.responseType = 'arraybuffer';
	        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	        xhr.send(params);
    	} else {
			$downloadInvoice.closest('.p-checkarea').siblings('.invoice-alert').find('strong').text('No available invoice for ' + refNum);
			$downloadInvoice.closest('.p-checkarea').siblings('.invoice-alert').css('display', '');
    	}

        xhr.onload = function(e) {
			let blob = new Blob([this.response], {type:'application/pdf'});
			let link = document.createElement('a');
			let downloadUrl = window.URL.createObjectURL(blob);

			link.href = downloadUrl;
			link.download = 'invoice.pdf';
			link.dispatchEvent(new MouseEvent(`click`, {bubbles: true, cancelable: true, view: window}));
        };
    });

    //cart plugin
    $('body').on('click','.cart-pagination a', function(){
        if(!$(this).hasClass("disabled")){
            var obj = {};
            obj.cart_current = $(this).attr('data-page-number');

            var dataString = $.param(obj);

            $.ajax({
                type        : 'POST',
                url         : '/MelisDemoCommerce/ComMyAccount/cartPaginationRenderer',
                data        : dataString,
                dataType    : 'json',
                encode		: true
            }).success(function(data){
                if(data.cart !== ''){
                    // Reloading header cart with new content
                    $("#my-cart").html(data.cart);
                }
            });
        }
    });
});