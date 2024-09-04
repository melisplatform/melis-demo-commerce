$(function(){
	// Menu bug fixed on Firefox
	$(".mega-menu-area a:not([href=''])").click(function(){
		$(".mega-menu-area").css("display", "none");
	});
	
	$("#cat-treeview").load(function(){
		var treeView = $(this);
		melisCommerceDemo.recursiveExpand(treeView);
	});
	
	$("#catalogueFilter").click(function(){
		melisCommerceDemo.submitCatalogueFilter()
	});
	
	$("#catalogueSearch").click(function(){
		melisCommerceDemo.submitCatalogueFilter()
	});
	
	$("#input-sort").change(function(){	
		melisCommerceDemo.submitCatalogueFilter()	
	});
	
	$("#input-limit").change(function(){	
		melisCommerceDemo.submitCatalogueFilter()	
	});
	
	$("#input-limit").click(function(){
		$(this).addClass('activated');
	});
	
	$("#input-sort").click(function(){
		$(this).addClass('activated');
	});

	$(".btn-def").click(function(){
		var body = $("html, body");
		body.stop().animate({scrollTop:220}, '500', 'swing');
	});
});

var melisCommerceDemo = (function(window) {
	
	function submitCatalogueFilter(){
		var dataString = [];
		var colorAttributes = $('.color-attributes');
		var sizeAttributes = $('.size-attributes');
		var attributeArray = [];
		var sortValue = $("#input-sort option:selected").val().split(" ");
		var urlVars = getUrlVars();
		// categories ID
		$('#cat-treeview .active').each(function(){
			dataString.push({ 
				name : 'm_box_category_tree_ids_selected[]', 
				value : $(this).closest('li').data('catid')
			});
		})
		
		var priceRange = $('#slider-range');
		if(priceRange.data('defaultmin') != priceRange.slider("values")[0] || priceRange.data('defaultmax') != priceRange.slider("values")[1]){
			// price min
			dataString.push({ 
				name : 'm_box_product_price_min',
				value : $("#slider-range").slider("values")[0]
			});
			
			// price max
			dataString.push({ 
				name : 'm_box_product_price_max',
				value : $("#slider-range").slider("values")[1]
			});
		}
		var colorAttr = [];
		// Selected attribute
        colorAttributes.each(function(){
			var attribute = $(this);
			attribute.find('.active').each(function(){
                colorAttr.push({
                    name: 'color[]',
                    value:$(this).closest('li').data('attributeid')
            	});
			});
		});

        var sizeAttr = [];
        // Selected attribute
        sizeAttributes.each(function(){
            var attribute = $(this);
            attribute.find('.active').each(function(){
                sizeAttr.push({
                    name: 'size[]',
                    value:$(this).closest('li').data('attributeid')
            	});
            });
        });

        dataString.push({
			name : 'm_box_product_attribute_values_ids_selected[]',
			//merge the two attributes to convert into a query string
			value : $.param($.merge(colorAttr, sizeAttr))
        });

		var sorting = urlVars['m_col_name'] || false;
		
		if($("#input-sort").hasClass('activated') || sorting ){
			dataString.push({ name : 'm_col_name', value : sortValue[0]});
			dataString.push({ name : 'm_order', value : sortValue[1]});
		}
		
		var limiting = urlVars['m_page_nb_per_page'] || false;
		
		if($("#input-limit").hasClass('activated') || limiting){
			dataString.push({ 
				name : 'm_page_nb_per_page',
				value : $("#input-limit option:selected").val()
			});
		}

        /**
		 * check if #catalogueSearchForm exist
		 * if not, we will create a new form
		 * to make the request submit
         */
        if($("#catalogueSearchForm").length <= 0){
			var form = $('<form>');
            $.each(dataString, function (key, e) {
                $('<input>').attr({
                    type: 'hidden',
                    name: e.name,
                    value: e.value,
                }).appendTo(form);
            });
            form.appendTo('body').submit();
        }else {
            $.each(dataString, function (key, e) {
                $('<input>').attr({
                    type: 'hidden',
                    name: e.name,
                    value: e.value,
                }).appendTo("#catalogueSearchForm");
            });
            $("#catalogueSearchForm").submit();
        }
	}
	
	function recursiveExpand(treeView){
		treeView.find('a').each(function(){
			var aCat = $(this);
			if(aCat.hasClass("active")){
				if(!aCat.closest('a').hasClass("active")){
					aCat.closest('li').addClass('collapsable ');
				}				
			}
		});
	}
	
	function getUrlVars() {
	    var vars = {};
	    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	        vars[key] = value;
	    });
	    return vars;
	}
	
	return{
		submitCatalogueFilter : submitCatalogueFilter,
		recursiveExpand : recursiveExpand,
		getUrlVars : getUrlVars
	}
})(window);

