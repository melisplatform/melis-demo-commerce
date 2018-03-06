$(function(){
	body = $("body");
	$(".product-attribute-selection").change(function(e){
		
		var dataString = new Array;
		
		dataString.push({
			name : 'm_attr_value',
			value : $(this).val()
		});
		
		
		var attrIdAction = $(this).data("attributeid");
		dataString.push({
			name : 'm_action',
			value : attrIdAction
		});
		
		var productId = $(this).data("productid");
		dataString.push({
			name : 'm_p_id',
			value : productId
		});
		
		$(".product-attribute-selection").each(function(){
			dataString.push({
				name : 'm_attrSelection['+$(this).data("attributeid")+']',
				value : $(this).val()
			});
		});
		
		setAttrSelectionsState();
		
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComProduct/getVariantCommonAttributes',
	        data        : dataString,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			
			setAttrSelectionsState("reset");
			
			if(!$.isEmptyObject(data.variant)){
				body.find(".single-product-description").data("variantid", parseInt(data.variant.var_id));
			}
				
			var attributes = data.variant_attr;
			var variantAttrNum = 0;
			$.each(attributes, function(key, value){
				var attrOptions = value.selections;
				
				
				if($.inArray(parseInt($("#prd-attr-"+key).val()), attrOptions) == -1){
					if(attrOptions.length == 1){
						$("#prd-attr-"+key).val(attrOptions[0]);
						variantAttrNum++;
					}else{
						$("#prd-attr-"+key).val("");
					}
				}else{
					
					variantAttrNum++;
				}
				
				if(parseInt(attrIdAction) < key){
					
					$("#prd-attr-"+key+" option:not([value=''])").each(function(){
						$(this).removeClass("hidden");
						var option = $(this);
						
						if($.inArray(parseInt(option.val()), attrOptions) == -1){
							$(this).addClass("hidden");
						}
					});
				}
			});
			
			$autoTrigger = false;
			if(variantAttrNum == $(".product-attribute-selection").length){
				if(typeof e.isTrigger == "undefined"){
					$(".product-attribute-selection :last").trigger("change");
					$autoTrigger = true;
				}
			}
			
			$(".product-sku").text("");
			$(".product-sku").attr("data-skuid", null);
			$(".new-price").text("");
			
			if($autoTrigger == false){
				
				if(!$.isEmptyObject(data.variant)){
					var variant = data.variant;				
					$(".product-sku").text(variant.var_sku);
					$(".product-sku").attr('data-skuid', variant.var_id);
				}
				
				if(!$.isEmptyObject(data.variant_price)){
					var price = data.variant_price;	
					$(".new-price").text(price.cur_symbol+price.price_net);
				}
				
				var inputField = $('input[name="qtybutton"]');
				
				$("form#add-to-cart-form input[name='m_v_id']").val('');
				$("form#add-to-cart-form input[name='m_v_quantity']").val(1);
				
				if(!$.isEmptyObject(data.variant_stock)){
					
					var varStock = parseInt(data.variant_stock.stock_quantity);
					
					if(varStock != 0){
						$(".add-to-cart-zone .alert-danger").addClass("hidden");
						$(".add-to-cart-quantity-zone").removeClass("hidden");
						
						$("form#add-to-cart-form input[name='m_v_id']").val(data.variant.var_id);
						$("form#add-to-cart-form input[name='m_v_quantity']").data("maxvalue", varStock);
					}else{
						$(".add-to-cart-quantity-zone").addClass("hidden");
						$(".add-to-cart-zone .alert-danger").removeClass("hidden");
						
						$(".add-to-cart-zone .alert-danger").html("<p>Product is not available</p>");
					}
				}else{
					
					$(".add-to-cart-quantity-zone").addClass("hidden");
					
					if(!$.isEmptyObject(data.variant)){
						$(".add-to-cart-zone .alert-danger").removeClass("hidden");
						$(".add-to-cart-zone .alert-danger").html("<p>Product is not available</p>");
					}
				}
			}
		});
	});
	
	function setAttrSelectionsState(state = "loading"){
		
		var opacity = ".5";
		var flag = true;
		
		if(state != "loading"){
			opacity = "1";
			flag = false;
		}
		
		$(".product-attribute-selection").attr("disable", flag);
		$(".product-attribute-selection").css("opacity", opacity);
	}
});