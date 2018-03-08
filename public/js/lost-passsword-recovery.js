$(function(){
	$("form#lost-password-form").submit(function(e) {
        var data = $(this).serializeArray();
        var form = "form#lost-password-form";
        forms.suspendSubmit(form);
        $.ajax({
            type: 'POST',
            url: '/MelisDemoCommerce/ComLostPassword/submitLostPassword',
            data: data,
            dataType: 'json',
            encode: true,
        }).success(function(data) {
            if(data.success) {
                $("div.alert-danger").addClass("hidden");
                $("div.alert-success").removeClass("hidden");
            }
            else {
                $("div.alert-success").addClass("hidden");
                $("div.alert-danger").removeClass("hidden");
                $("div.alert-danger").html('<strong>Error!</strong> ' + data.message);
            }
            
            forms.checkForm(form, data.errors);
            
            forms.allowSubmit(form);
        }).error(function() {
            forms.allowSubmit(form);
        });
	    e.preventDefault();
    })

    $("form#lost-password-reset-form").submit(function(e) {
        var data = $(this).serializeArray();
        var form = "form#lost-password-reset-form";
        forms.suspendSubmit(form);
        $.ajax({
            type: 'POST',
            url: '/MelisDemoCommerce/ComLostPassword/submitResetPassword',
            data: data,
            dataType: 'json',
            encode: true,
        }).success(function(data) {
            if(data.success) {
                $("div.alert-danger").addClass("hidden");
                $("div.alert-success").removeClass("hidden");
                forms.clearDangerStatus(form);
                forms.reset(form);
                forms.disable(form);
                if(data.redirectUrl){
                	window.location = data.redirectUrl;
                }
            }
            else {
                $("div.alert-success").addClass("hidden");
                $("div.alert-danger").removeClass("hidden");
                
                forms.allowSubmit(form);
            }
            
            forms.checkForm(form, data.errors);
        }).error(function() {
            forms.allowSubmit(form);
        });
        e.preventDefault();
    })


});