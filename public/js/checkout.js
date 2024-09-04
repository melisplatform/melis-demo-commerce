$(function(){
	$(".update-checkout-cart").click(function(){
		$("#checkout-cart").submit();
	});
	
	$("#m_add_billing_phone_landline, #m_add_billing_phone_mobile, #m_add_delivery_phone_landline, #m_add_delivery_phone_mobile").on("keypress", function(event){
		var regex = /^([0-9\(\)\/\+\-]*)$/;
	    var _event = event || window.event;
	    var key = _event.keyCode || _event.which;
	    key = String.fromCharCode(key);
	    
	    if(!regex.test(key)) {
	        _event.returnValue = false;
	        if (_event.preventDefault)
	            _event.preventDefault();
	    }
	});
	
	// Binding Variant quantity input to Numeric characters only
    $('.cart-plus-minus-box').on("keydown", function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
	
	if($(".checkout-delivery-address").length){
		if($(".checkout-delivery-address  #m_add_delivery_id").val() == "new_address"){
			action("delivery", "block");
		}else{
			if($(".checkout-delivery-address  #m_add_delivery_id").val() !== ""){
				setDataForm("delivery", $(".checkout-delivery-address  #m_add_delivery_id").val());
			}else{
				action("delivery", "none");
			}
		}
		
		if($(".checkout-billing-address  #m_add_billing_id").val() == "new_address"){
			action("billing", "block");
		}else{
			if($(".checkout-billing-address  #m_add_billing_id").val() !== ""){
				setDataForm("billing", $(".checkout-billing-address  #m_add_billing_id").val());
			}else{
				action("billing", "none");
			}
		}
	}
	
	$(".checkout-delivery-address #m_add_delivery_id").on("change", function(){
		if($(this).val() !== ""){
			if($(this).val() !== "new_address"){
				setDataForm("delivery", $(this).val());
			}else{
				action("delivery", "block", "onChange");
			}
		}else{
			action("delivery", "none", "onChange");
		}
	});
	
	$(".checkout-billing-address #m_add_billing_id").on("change", function(){
		if($(this).val() !== ""){
			if($(this).val() !== "new_address"){
				setDataForm("billing", $(this).val());
			}else{
				action("billing", "block", "onChange");
			}
		}else{
			action("billing", "none", "onChange");
		}
	});
	
	$("#checkout-billing-address-same").click(function(){
		if($(this).is(":checked")){
			$(".checkout-billing-address-zone").addClass("hidden");
			$("#m_add_use_same_address").val(1);
		}else{
			$(".checkout-billing-address-zone").removeClass("hidden");
			$("#m_add_use_same_address").val(0);
		}
	});
	
	function setDataForm(type, typeId){
		
		var datastring = new Array;
		
		datastring.push({
			name : 'type',
			value : type
		});
		
		datastring.push({
			name : 'cadd_id',
			value : typeId
		});
		// serialize the new array and send it to server
		datastring = $.param(datastring);;
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComCheckout/getSelectedAddressDetails',
	        data        : datastring,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				action(type, "block", "onChange", true);
				$.each(data.address, function(index, value){
					if($(".checkout-"+type+"-address #"+index).length){
						$(".checkout-"+type+"-address #"+index).val(value);
					}
				});
			}
		});
	}
	
	function action(type, display, action = null, editable = true){
		
		if(action == "onChange"){
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select").parent(".input-box").find("ul").remove();
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select:not([name='m_add_"+type+"_id'])").val("");
		}
		
		if(editable == true){
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select:not([name='m_add_"+type+"_id'])").attr("disabled", false);
		}else{
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select:not([name='m_add_"+type+"_id'])").attr("disabled", true);
		}
		
		if(display == "none"){
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select:not([name='m_add_"+type+"_id'])").parent(".input-box").addClass("hidden");
		}else{
			$(".checkout-"+type+"-address fieldset input:not([type='hidden']), .checkout-"+type+"-address fieldset select:not([name='m_add_"+type+"_id'])").parent(".input-box").removeClass("hidden");
		}
	}
})