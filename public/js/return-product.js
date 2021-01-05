$(function(){
	$("form#returnProductForm").submit(function(e) {
		var _this = $(this);
        var datastring = $(this).serializeArray();

        $.ajax({
            type        : 'POST',
            url         : "/MelisDemoCommerce/ComOrder/returnProduct",
            data        : datastring,
            dataType    : 'json',
            encode		: true,
            beforeSend  : function(){
                $(".return-msg-status-success").addClass("hidden");
                $(".return-msg-status-failed").addClass("hidden");
            },
        }).success(function(data){
            if(data.success){
                //update the returned product value
                var returnProductsItem = data.returnProducts.items;
                $.each(returnProductsItem, function(i, v){
                    var tr = $("#variant-"+v.variant_id);
                    tr.find(".returned-product").text(v.returnedProduct);//updated the returned product quantity
                    //update the data attribute of button to sho the form
                    var btnShowForm = tr.find(".return-product-show-form");
                    btnShowForm.attr("data-rm-pr", v.remainingQtyToReturn);
                    //but we must hide the button if there is no more to return
                    if(v.remainingQtyToReturn <= 0){
                        btnShowForm.addClass("hidden");
                    }
                });

                $(".rp-msg-form-cont").addClass("hidden");
                $(".return-msg-status-success").removeClass("hidden");
                $(".return-msg-status-failed").addClass("hidden");

                //after success remove the quantity select that is added
                var qtySelect = _this.find("select[name*=m_rp_data]");
                $.each(qtySelect, function(){
                     $(this).closest("div.form-group").remove();
                });

                //clear form
                $('form#returnProductForm')[0].reset();
            }else{
                $(".return-msg-status-success").addClass("hidden");
                $(".return-msg-status-failed").removeClass("hidden");
            }
        });
		e.preventDefault();
	});
});