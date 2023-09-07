(function ($) {
    'use strict';
	
    $(document).ready(function () {
		
        var MWC_FRONTEND = {
            init: function () {

                MWC_FRONTEND.generateToken();
                MWC_FRONTEND.saveAttendees();
            },
				
            /**
             *  Generate toekn when form is submitted from front-end.
             */
            generateToken: function() {

                $( '#tc_email_for_token_form' ).on( 'submit', function(e) {
                    e.preventDefault();
					var email_for_token = $( '#tc_email_for_token' ).val();
					$( '#tc_order_attendee_message' ).css( 'display', 'none' ).html( '' );
                    $( '#tc-attendee-listing' ).css( 'opacity', '0.3' );
                    var data = {
                        email_for_token: email_for_token,
                        action: 'tc_customization_token_generator' 
                    };

                    jQuery.post( TC_Customization.ajaxURL, data, function( response ) {
                        var obj = jQuery.parseJSON( response );
                        $('#tc-attendee-listing').css( 'opacity', '1' );
                        $( '#tc_order_attendee_message' ).css( 'display', 'block' ).addClass( 'tc_order_attendee_'+obj.status ).html( obj.message );
                    });
				});
            },

            /**
             * Save the attendees
             */
            saveAttendees: function(){

                $('#tc-product-attendees-form').on('submit', function (e) {
																	 
                    e.preventDefault();
					var form = $(this).serialize();
					var tc_reload 		= $('#tc_reload').val();
		
					$( '#tc_order_attendee_message' ).css('display', 'none').html('');

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
            }
            
        };
        MWC_FRONTEND.init();
    });
})(jQuery);