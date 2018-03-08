$(function(){
	body = $("body");
	
	$("#add-to-cart-form").keydown(function(event){
	    if(event.keyCode == 13) {
	    	event.preventDefault();
	    	return false;
	    }
	});
	
	$("#add-to-cart").click(function(){
		
		if(!$(this).hasClass("disable-btn-add-cart")){
			$(".add-to-cart-zone .alert").addClass("hidden");
			
			var form = "add-to-cart-form";
			var dataString = $("form#"+form).serializeArray();
			
			
			setAddTocartState();
			
			$.ajax({
				type        : 'POST', 
		        url         : '/MelisDemoCommerce/ComProduct/addToCart',
		        data        : dataString,
		        dataType    : 'json',
		        encode		: true
			}).success(function(data){
				
				setAddTocartState(false);
				
				if(data.success){
					$(".add-to-cart-zone .alert-success").removeClass("hidden");
					
					var variants = [];
					var var_cart = [];
					body.find('.cart-single-wraper').each(function(){
						var_cart.push(parseInt($(this).data('variantid')));
						var item = [];
						item['var_id'] = $(this).data('variantid');
						item['qty'] = $(this).find('.cart-qty span').text().replace ( /[^\d.]/g, '' );
						item['price'] = $(this).find('.cart-price').text().replace ( /[^\d.]/g, '' );
						variants.push(item);
					});
					
					// Reloading header cart with new content
					$(".header-cart").html(data.cartList);
					
					// Reset quantity input
					$("#m_variant_quantity").val(1);
					
				}else{
					var errMsg = "";
					$.each(data.errors, function(key, value){
						$.each(value, function(k, val){
							errMsg += "<p>"+val+"</p>";
						})
					});
					// Adding content of the alert message zone
					$(".add-to-cart-zone .alert-danger").html(errMsg);
					// Showing the Error result for submitting form
					$(".add-to-cart-zone .alert-danger").removeClass("hidden");
				}
			});
		}
	});
	
	// Binding Variant quantity input to Numeric characters only
    $('#m_variant_quantity').on("keydown", function (e) {
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
    
    $('#m_variant_quantity').on("change", function (e) {
        if($(this).val() == '' || $(this).val() == 0){
        	$(this).val("1");
    	}
    });
    
    /*----------------------------
    cart-plus-minus-button
   ------------------------------ */
   $(".cart-plus-minus")
   $(".qtybutton").on("click", function() {
	   console.log('test');
	   if(!$(this).hasClass("disable-qtybutton")){
		   var $button = $(this);
	       var oldValue = $button.parent().find("input").val();
	       if ($button.text() == "+") {
	           var newVal = parseFloat(oldValue) + 1;
	       } else {
	           // Don't allow decrementing below zero
	           if (oldValue > 1) {
	               var newVal = parseFloat(oldValue) - 1;
	           } else {
	               newVal = oldValue;
	           }
	       }
	       $button.parent().find("input").val(newVal);
	   }
   });
    
});

function setAddTocartState(disable = true){
	
	$(".add-to-cart-quantity-zone #add-to-cart-form input[name='m_variant_id']").val("");
	$(".add-to-cart-quantity-zone #add-to-cart-form input[name='m_variant_country']").val("");
	$(".add-to-cart-quantity-zone .cart-plus-minus-box").val("1");
	
	$(".add-to-cart-zone .alert").addClass("hidden");
	
	if(disable){
		$(".add-to-cart-quantity-zone .cart-plus-minus-box").attr("readonly", true);
		$(".add-to-cart-quantity-zone .cart-plus-minus-box").addClass("disable-cart-plus-minus-box");
		$(".add-to-cart-quantity-zone .qtybutton").addClass("disable-qtybutton");
		$(".add-to-cart-quantity-zone .btn-add-cart").addClass("disable-btn-add-cart");
	}else{
		$(".add-to-cart-quantity-zone .cart-plus-minus-box").attr("readonly", false);
		$(".add-to-cart-quantity-zone .cart-plus-minus-box").removeClass("disable-cart-plus-minus-box");
		$(".add-to-cart-quantity-zone .qtybutton").removeClass("disable-qtybutton");
		$(".add-to-cart-quantity-zone .btn-add-cart").removeClass("disable-btn-add-cart");
	}
}