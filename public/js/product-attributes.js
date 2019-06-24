$(function(){
	var firstLoad = false;
	setTimeout(function(){
        firstLoad = true;
        $(".product-attribute-selection :first").trigger("change");
	}, 100);


	body = $("body");
	body.on("change", ".product-attribute-selection", function(e){

        enableDisableAttribute($(this));

		var dataString = new Array;

		//add page id to the request

		dataString.push({
			name : 'idpage',
			value : body.find(".single-product-description").data("pageid")
		});
		
		var attrIdAction = $(this).data("attributeid");
		dataString.push({
			name : 'action',
			value : attrIdAction
		});
		
		var productId = $(this).data("productid");
		dataString.push({
			name : 'productId',
			value : productId
		});
		
		$(".product-attribute-selection").each(function(){
			dataString.push({
				name : 'attrSelection['+$(this).data("attributeid")+']',
				value : $(this).val()
			});
		});

		setAttrSelectionsState();
		
		setAddTocartState();
		
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
					
					$("#prd-attr-"+key+" option:not([value='0'])").each(function(){
						$(this).removeClass("hidden");
						var option = $(this);

                        //option.val($("#prd-attr-"+key+" option:first").val());

						if($.inArray(parseInt(option.val()), attrOptions) == -1){
							$(this).addClass("hidden");
						}
					});
					//if the option value is 0, set the select value to 0
					if(!firstLoad) {
                        var opt = $("#prd-attr-" + key + " option");
                        if (opt.val() == 0) {
                            $("#prd-attr-" + key).val('0');
                        }
                    }
				}
			});

            firstLoad = false;
			
			/*$autoTrigger = false;
			if(variantAttrNum == $(".product-attribute-selection").length){
				if(typeof e.isTrigger == "undefined"){
					$(".product-attribute-selection :last").trigger("change");
					$autoTrigger = true;
				}
			}*/
			
			$(".product-sku").text("");
			$(".product-sku").attr("data-skuid", null);
			$(".new-price").text("");
			
			//if($autoTrigger == false){
				
				if(!$.isEmptyObject(data.variant)){
					var variant = data.variant;				
					$(".product-sku").text("("+variant.var_sku+")");
					$(".product-sku").attr('data-skuid', variant.var_id);
				}
				
				if(!$.isEmptyObject(data.variant_price)){
					var price = data.variant_price;	
					$(".new-price").text(price.cur_symbol+" "+price.price_net);
				}
				
				var inputField = $('input[name="qtybutton"]');
				
				$("form#add-to-cart-form input[name='m_variant_id']").val('');
				$("form#add-to-cart-form input[name='m_variant_quantity']").val(1);

				if(!$.isEmptyObject(data.variant_stock)){
					var varStock = parseInt(data.variant_stock.stock_quantity);
					
					if(varStock != 0){
						setAddTocartState(false);
						$(".add-to-cart-zone .alert-danger").addClass("hidden");
						$("form#add-to-cart-form input[name='m_variant_id']").val(data.variant.var_id);
						$("form#add-to-cart-form input[name='m_variant_country']").val(data.countryId);
						$("form#add-to-cart-form input[name='m_variant_quantity']").data("maxvalue", varStock);
					}else{
						$(".add-to-cart-zone .alert-danger").removeClass("hidden");
						$(".add-to-cart-zone .alert-danger").html("<p>Product is not available</p>");
					}
				}else{
					if(!$.isEmptyObject(data.variant) || data.variant.length === 0){
						$(".add-to-cart-zone .alert-danger").removeClass("hidden");
						$(".add-to-cart-zone .alert-danger").html("<p>Product is not available</p>");
					}
				}
			//}
		});
	});
	
	function setAttrSelectionsState(state){
        state = (state === undefined) ? "loading" : state;
		var opacity = ".5";
		var flag = true;
		
		if(state != "loading"){
			opacity = "1";
			flag = false;
		}
		
		//$(".product-attribute-selection").attr("disabled", flag);
		$(".product-attribute-selection").css("opacity", opacity);
	}

	function enableDisableAttribute(_this){
        var select_checker = _this.attr('data-select-checker');
		$('.product-type').each(function(){
			var select = $(this).find('.product-attribute-selection');
			var selectChecker = select.attr('data-select-checker');
            if(selectChecker <= Number(Number(select_checker) + 1)){
            	select.removeAttr('disabled');
            }else{
            	if(!firstLoad) {
                    select.attr('disabled', true);
                }
			}
		});
	}
});