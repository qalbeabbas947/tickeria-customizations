(function ($) {
    'use strict';
    $(document).ready(function () {
        
        var TC_Admin = {

            init: function () {
                
				$('#tc_generate_token_order').on('click', function(e){
					e.preventDefault();
                    var aorder_id 	= $(this).data('order_id');
                    var auser_id 	= $(this).data('user_id');
                    // var lnk = $(this);

                    // lnk.attr('disabled', true).css('opacity', '0.5');
                    // AJAX Request to apply coupon code to the cart
                    var data = {
                        order_id: 	aorder_id,
                        user_id: 	auser_id,
                        action: 	'tc_customization_token_generator_admin',
                        tc_time:    Date.now()
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
                             alert( response.message );
                             document.location.reload();
                        }
                    });	
				});
            },
        };
        TC_Admin.init();
    });
})(jQuery);