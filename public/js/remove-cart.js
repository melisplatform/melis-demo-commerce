$(document).ready(function() {
	body = $("body");
	
	body.on("click", ".cart-remove a" ,function(){
	    	
	    	var item = $(this);
	    	var dataString = [];
	    	var variantId = item.closest('.cart-single-wraper').data('variantid');
	    	var qty = item.closest('.cart-single-wraper').find('.cart-qty span').text().replace ( /[^\d.]/g, '' );;
	    	dataString.push({ name : 'm_v_id_remove' , value : variantId});
	    	
	    	$.ajax({
				type        : 'POST', 
		        url         : '/MelisDemoCommerce/ComProduct/removeItemFromCart',
		        data        : dataString,
		        dataType    : 'json',
		        encode		: true
			}).success(function(data){
				if(data.deleteSuccess){
					
					// replenish stock maxvalue
					var varContId = $('.single-product-description').data("variantid");
					if(varContId == variantId){
						var addToCart = $('.add-to-cart-quantity-zone');
						var varInput = $("#m_v_quantity");
						if(addToCart.hasClass("hidden")){
							addToCart.removeClass("hidden");
							$('.single-product-description .alert-danger').addClass("hidden");
						}
						
						var varInputMax = varInput.data("maxvalue");
						varInput.data("maxvalue", parseInt(varInputMax) + parseInt(qty));
						
					}
					
					item.closest('.cart-single-wraper').remove();
					if($('.cart-single-wraper').length == 1){
						$('.cart-message').removeClass('hidden');
						$('.cart-subtotal span').text('0');
						$('.cart-subtotal').addClass('hidden');
						$('.cart-check-btn').addClass('hidden');
					}else{
						$('.cart-subtotal span').text(data.checkOutCartSubTotal);
					}
					
					$("#cartCount").text(data.checkOutCart.length);
				}
			});
			
	});
})