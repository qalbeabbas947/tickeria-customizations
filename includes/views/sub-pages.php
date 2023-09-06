<?php 
	global $wpdb;

	get_header(); 

	$token 			= isset( $_REQUEST['tctoken'] ) && ! empty( $_REQUEST['tctoken'] ) ? sanitize_text_field( $_REQUEST['tctoken'] ) : '';
	$order_id 		= isset( $_REQUEST['oid'] ) && ! empty( $_REQUEST['oid'] ) ? sanitize_text_field( $_REQUEST['oid'] ) : 0;

	$_access =  $wpdb->get_row( $wpdb->prepare('select * from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date > now() and token=%s', $token) );
	if( ! empty( $_access ) ) {
		
		$user_id 		= 	$_access->user_id;
		$issue_date 	= 	$_access->issue_date;
		$expiry_date 	= 	$_access->expiry_date;

		$args = array (
			'customer_id' 	=> $user_id,
			'limit' 		=> -1, // to retrieve _all_ orders by this user
		);

		$orders = wc_get_orders($args);
		
		$tc_roundtable_main_page 	= get_option( 'tc_roundtable_main_page' );
		$tc_roundtable_form_page 	= get_option( 'tc_roundtable_form_page' );

		$url = get_permalink( $tc_roundtable_main_page );
		$url = add_query_arg( 'tctoken', $token, $url );
	?>

    <div class="tc-front-container">
        <div class="tc-front-area">
            <h1><?php _e('Round Table Orders', TC_TEXT_DOMAIN);?></h1>
			<a href="<?php echo $url;?>"><?php _e('Back', TC_TEXT_DOMAIN);?></a>
			<div id="tc_order_attendee_message"></div>
			
			  <input type="hidden" name="tctoken" id="tctoken" value="<?php echo $token;?>" />
			  <input type="hidden" name="tc_order_id" id="tc_order_id" value="<?php echo $order_id;?>" />
			  <?php
			  	$order = wc_get_order($order_id);
				$item_index = 0;
			  	foreach( $order->get_items( ['line_item'] ) as $item_id => $item ) {
					$temp_url = get_permalink($tc_roundtable_form_page);
					$temp_url = add_query_arg('tctoken', $token, $temp_url);
					$temp_url = add_query_arg('oid', $order_id, $temp_url);
					$temp_url = add_query_arg('pid', $item->get_product_id(), $temp_url);
					$is_round_table = get_post_meta( $item->get_product_id(), '_is_round_table', true );
					if( intval($is_round_table) == 1 ) {

						$item_index++;
						if($item_index == 1) {
							echo '<div class="tc-product-row">';
						}
						?>	    	
						<div class="tc-product-column">
							<table>
								<tr><td><img src="<?php echo get_the_post_thumbnail_url( $item->get_product_id(), 'post-thumbnail' );?>" width="90%"/></td></tr>
								<tr><td><?php echo get_the_title($item->get_product_id());?></td></tr>
								<tr><td><a href="<?php echo $temp_url;?>"><?php _e('View / Edit Details', TC_TEXT_DOMAIN);?></a></td></tr>
							</table>
						</div>
							
						<?php
						if($item_index == 3) {
							echo '</div>';
							$item_index = 0;
						}

					}
				}
				?>
		</div>
    </div>


<?php 
	} else {
		echo '<div class="tc_order_attendee_failed">'.__('Invalid or expired token!', TC_TEXT_DOMAIN).'</div>';
	}

	get_footer();
?>