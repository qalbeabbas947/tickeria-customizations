(function ($) {
    'use strict';
	
    $(document).ready(function () {
		
        var MWC_FRONTEND = {
            init: function () {
                
				$('#btn_tc_cancel_attendees').on('click', function () {
					var link = $(this).data('link');
					document.location=link;
                });
				
				$('#tc_email_for_token_form').on('submit', function (e) {
					e.preventDefault();
					var email_for_token 	= $('#tc_email_for_token').val();
					
					$( '#tc_order_attendee_message' ).css('display', 'none').html('');
                    // AJAX Request to apply coupon code to the cart
                    var data = {
                        email_for_token: email_for_token,
                        action: 'tc_customization_token_generator'
                    };

                    $.ajax({
                        type: 'post',
                        url: TC_Customization.ajaxURL,
                        data: data,
						
				        dataType: "json",
                        beforeSend: function (response) {
                            $('#tc-attendee-listin').css('opacity', '0.3');
                        },
                        complete: function (response) {
                            $('#tc-attendee-listin').css('opacity', '1');
                        },
                        success: function (response) {
							$( '#tc_order_attendee_message' ).css('display', 'block').addClass('tc_order_attendee_'+response.status).html( response.message );
						}

                    });
				});
                $('#tc-product-attendees-form').on('submit', function (e) {
																	 
                    e.preventDefault();
					var form = $(this).serialize();
					var tc_reload 		= $('#tc_reload').val();
					
                    /*var tc_order_id 	= $('#tc_order_id').val();
					var tc_attendee_id 	= $('#tc_attendee_id').val();
					var tc_first_name 	= $('#tc_first_name').val();
					var tc_last_name 	= $('#tc_last_name').val();
					var tc_owner_email 	= $('#tc_owner_email').val();
					var tctoken 		= $('#tctoken').val();*/
					$( '#tc_order_attendee_message' ).css('display', 'none').html('');
                    // AJAX Request to apply coupon code to the cart
                    /*var data = {
                        tctoken: tctoken,
						tc_order_id: tc_order_id,
                        tc_attendee_id: tc_attendee_id,
                        tc_first_name: tc_first_name,
                        tc_last_name: tc_last_name,
                        tc_owner_email: tc_owner_email,
                        action: 'tc_customization_attendee_update'
                    };*/

                    $.ajax({
                        type: 'post',
                        url: TC_Customization.ajaxURL,
                        data: form,
						
				        dataType: "json",
                        beforeSend: function (response) {
                            $('#tc-attendee-listin').css('opacity', '0.3');
                        },
                        complete: function (response) {
                            $('#tc-attendee-listin').css('opacity', '1');
                        },
                        success: function (response) {
							$( '#tc_order_attendee_message' ).css('display', 'block').addClass('tc_order_attendee_'+response.status).html( response.message );
							document.location.reload();
                        }

                    });
                });
            },
        };
        MWC_FRONTEND.init();
    });
})(jQuery);