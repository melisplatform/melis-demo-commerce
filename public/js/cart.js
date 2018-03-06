$(function(){
	body = $("body");
	
	$("#add-to-cart-form").keydown(function(event){
	    if(event.keyCode == 13) {
	    	event.preventDefault();
	    	return false;
	    }
	});
	
	$(".product-attribute-selection").change(function(e){
		$(".add-to-cart-zone .alert").addClass("hidden");
	});
	
	$("#add-to-cart").click(function(){
		
		$(".add-to-cart-zone .alert").addClass("hidden");
		
		var form = "add-to-cart-form";
		var dataString = $("form#"+form).serializeArray();
		
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComProduct/addToCart',
	        data        : dataString,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
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
				
				$.each(data.basket, function(e, val){
					var newItem = '', total = 0;
					var append = 0;
					newItem += '<div class="cart-single-wraper" data-variantid="' +val.var_id+ '">';
	                newItem += '<div class="cart-img">';
	                newItem += '<a href="#"><img src="' +val.image+ '" alt=""></a>';
	                newItem += '</div>';
	                newItem += '<div class="cart-content">';
	                newItem += '<div class="cart-name"> <a href="#">'+ val.name + '(' + val.var_sku +')</a> </div>';
	                newItem += '<div class="cart-price">'+ val.cur_symbol + val.price + '</div>';
	                newItem += '<div class="cart-qty"> Qty: <span>'+  val.quantity +'</span> </div>';
	                newItem += '</div>';
	                newItem += '<div class="remove cart-remove"> <a href="#"><i class="zmdi zmdi-close"></i></a> </div>';
	                newItem += '</div>';
	                var html = $.parseHTML(newItem);
	                
					// append the item in the menu cart
					if(var_cart.length > 1){
						var key = $.inArray(parseInt(val.var_id), var_cart);
						if(key == -1){							
			                $('.cart-subtotal').before(html);
			                append = 1;
						}else{
							// if item exist check quanity
							if(parseInt(val.quantity) > parseInt(variants[key]['qty'])){
								
								// update the existing cart if quantity is bigger
								body.find('.cart-single-wraper[data-variantid="'+variants[key]['var_id']+'"]').find('.cart-qty span').text(val.quantity+' ');
								var newTotal = 0;
								$.each(data.basket, function(a, b){
									newTotal = newTotal + ( parseInt(b.price,10) * parseInt(b.quantity,10) );
								});
								// update the new subtotal
								$('.cart-subtotal span').text(val.cur_symbol+newTotal);
							}
						}
					}else{
						$('.cart-message').addClass('hidden');
						$('.cart-subtotal').removeClass('hidden');
						$('.cart-check-btn').removeClass('hidden');
						$('.cart-subtotal').before(html);	
						append = 1;
					}
					
					// update quantity maxvalue field
					var varInput = body.find('.single-product-description[data-variantid="'+val.var_id+'"] #m_v_quantity');
					if(varInput.length){
						var newMaxValue = varInput.data("maxvalue") - varInput.val();
						varInput.data("maxvalue",newMaxValue);
					}
					
					if(append){
						var curr = $('.cart-subtotal span').text().replace ( /[^\d.]/g, '' );
						var price = val.price;
						var quant = val.quantity;
						total = parseFloat(curr, 10) + (parseFloat(price, 10) * parseFloat(quant, 10));
						$('.cart-subtotal span').text(val.cur_symbol + parseFloat(total).toFixed(2));
					}
					
				});
				
				// Reset quantity input
				$("#m_v_quantity").val(1);
				$("#cartCount").text(data.basket.length);
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
	});
	
	// Binding Variant quantity input to Numeric characters only
    $('#m_v_quantity').on("keydown", function (e) {
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
    
    $('#m_v_quantity').on("change", function (e) {
        if($(this).val() == '' || $(this).val() == 0){
        	$(this).val("1");
    	}
    });
    
});