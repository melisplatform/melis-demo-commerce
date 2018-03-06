var melisSiteHelper = (function(){
	
	// CACHE SELECTORS
	var $body = $("body");
	
	function hasValue(){
		if($(this).val().length){
			var element = $("#"+$(this).attr('id'));
			element.parent().removeClass("has-error");
			element.next().remove();
		}
	}
	
	function melisSiteShowFormResult(errors , form = 'form'){
		
		$("#"+form+" div").removeClass("has-error").find(".text-danger").remove();
		
		$.each( errors, function( key, error ) {
			var element = $('#'+key);
			
			// char counter in seo title
			element.on("keydown change", hasValue);
			
			element.next().remove();
			
			var errorTexts = '';
			
			// catch error level of object
			try {
				$.each( error, function( key, value ) {
					if(key !== 'label'){
						 errorTexts = value;
					}
				});
			} catch(Tryerror) {
				if(key !== 'label'){
					 errorTexts = error;
				} 
			}	
			
			element.parent().removeClass("has-success").addClass("has-error").after().append('<label for="'+key+'" class="text-danger">'+errorTexts+'</label>');
			
		});
	}
	
	return{
		melisSiteShowFormResult		:		melisSiteShowFormResult,
	}
})();

var forms = (function(window) {

    function addDangerOnInput(form, name) {
        $(form + "    input[name='" + name + "']").css("border-color", "#d14040");
        $(form + "   select[name='" + name + "']").css("border-color", "#d14040");
        $(form + " textarea[name='" + name + "']").css("border-color", "#d14040");
    }

    function clearDangerStatus(form) {
        $(form + " input").css("border-color", "#ccc").next("div.frm-err-prompt").html("");
        $(form + " select").css("border-color", "#ccc").next("div.frm-err-prompt").html("");
        $(form + " textarea").css("border-color", "#ccc").next("div.frm-err-prompt").html("");
    }

    function checkForm(form, errorData) {
        clearDangerStatus(form);
        $.each(errorData, function(i, key) {
            addDangerOnInput(form, i);

            $.each(key, function(idx, value) {
                // make sure to clear first the data before appending

                $(form + " input[name='" + i + "']").nextAll().remove();
                $(form + " input[name='" + i + "']").after('<div class="frm-err-prompt" style="color:#bb0000"><li class="error-details">'+value+'</li></div>');

                $(form + " select[name='" + i + "']").nextAll().remove();
                $(form + " select[name='" + i + "']").after('<div class="frm-err-prompt" style="color:#bb0000"><li class="error-details">'+value+'</li></div>');

                $(form + " textarea[name='" + i + "']").nextAll().remove();
                $(form + " textarea[name='" + i + "']").after('<div class="frm-err-prompt" style="color:#bb0000"><li class="error-details">'+value+'</li></div>');
            });
        })
    }

    function reset(form) {
        clearDangerStatus(form);
        $(form)[0].reset();
    }

    function suspendSubmit(form) {
        $(form + " button[type='submit']").attr('disabled', 'disabled');
    }

    function allowSubmit(form) {
        $(form + " button[type='submit']").removeAttr('disabled');
    }

    function disable(form) {
        var form = $(form + " input, button, textarea, select");
        $.each(form, function(idx, el) {
            $(el).attr("disabled", "disabled");
        });
    }

    function enable(form) {
        var form = $(form + " input, button, textarea, select");
        $.each(form, function(idx, el) {
            $(el).removeAttr("disabled");
        });
    }

    return{
        addDangerOnInput: addDangerOnInput,
        clearDangerStatus: clearDangerStatus,
        checkForm: checkForm,
        reset: reset,
        suspendSubmit: suspendSubmit,
        allowSubmit: allowSubmit,
        enable : enable,
        disable : disable
    }

})(window);

var alerts = (function(window){

    function showAlert(target, message, type, interval) {
        $(target).removeClass();
        $(target).css("display", "none");
        $(target).addClass("alert alert-" + type);
        $(target).html(message);
        $(target).fadeIn();
        setInterval(function() {
            $(target).fadeOut();
        }, interval);
    }

    function showDanger(target, message) {
        showAlert(target, message, "danger", 5000);
    };

    function showSuccess(target, message) {
        showAlert(target, message, "success", 5000);
    };

    function showInfo(target, message) {
        showAlert(target, message, "info", 5000);
    };

    function showWarning(target, message) {
        showAlert(target, message, "warning", 5000);
    };

    function hideAlert(target) {
        $(target).fadeOut();
    }

    return{
        showDanger: showDanger,
        showSuccess: showSuccess,
        showInfo: showInfo,
        showWarning: showWarning
    };

})(window);