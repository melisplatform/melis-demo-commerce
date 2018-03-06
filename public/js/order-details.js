$(function(){
	$("#add-to-cart-form").submit(function(ev){
		
		var form = $(this);
		
		form.find('input[name=message_is_submit]').val(1);
		
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCommerce/ComOrder/submitOrderMessage',
	        data        : form.serializeArray(),
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				var messagesId = [];				 
				$('.order-messages').show();
				$('.message-form .alert-danger').hide();
				$('.message-form .alert-success').fadeIn();
				
				// append new messages
				$('.order-messages li').each(function(){
					messagesId.push($(this).data("messageid"));
				});
				
				$.each(data.orderMessages, function(e, val){
					
					var key = $.inArray(parseInt(val.omsg_id), messagesId);
					// If message does not exist, append new message
					if(key == -1){
						var first = !val.firstName ? '' : val.firstName;
						var middle = !val.middleName ? '' : val.middleName;
						var last = !val.lastName ? '' : val.lastName;		
						var newMessage = '';

						newMessage += '<li class="single-comments" data-messageid="' + val.omsg_id + '">';
						newMessage += 	'<div class="commets-text">';
						newMessage += 		'<h5>' + first + ' ' + middle + ' ' + last + '</h5>';
						newMessage += 		$.basicDate(val.omsg_date_creation);
						newMessage += 		'<p>' + val.omsg_message + '</p>';
						newMessage += 	'</div>';
						newMessage += '</li>';  
						
						var html = $.parseHTML(newMessage);						
						$('.order-messages ul').append(html);
					}
					
				});
				
				form[0].reset();
			}else{
				$('.message-form .alert-success').hide();
				$('.message-form .alert-danger').fadeIn();
				melisSiteHelper.melisSiteShowFormResult(data.errors, "#add-to-cart-form");
			}
		});
		
		ev.preventDefault();
	});
	
	$.basicDate = function(dateObject) {
		
		var monthNames = ["January", "February", "March", "April", "May", "June",
		                  "July", "August", "September", "October", "November", "December"
		                ];
		
	    var d = new Date(dateObject);
	    var day = d.getDate();
	    var month = d.getMonth() + 1;
	    var year = d.getFullYear();

	    var hour = d.getHours();
	    var min = d.getMinutes();
	    var sec = d.getSeconds();
	    
	    var ps = '';
	    var month = monthNames[parseInt(month)];
	    
	    if (day < 10) {
	        day = "0" + day;
	    }
	    if (month < 10) {
	        month = "0" + month;
	    }
	    
	    if (hour >= 12){
	    	ps = 'pm';
	    }else{
	    	ps = 'am';
	    }
	    
	    var date = "<div class=\"comments-date\"><span>" + month + ' ' + day + ', ' + year + "</span>" + "<span>, at "+ hour + ':' + min + ' ' + ps +"</span></div>";
	    return date;
	};
});