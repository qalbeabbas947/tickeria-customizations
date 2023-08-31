<?php 
	get_header(); 
	global $wpdb;
	$token = sanitize_text_field($_REQUEST['tctoken']);
	$_access =  $wpdb->get_row( $wpdb->prepare('select * from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date > now() and token=%s', $token) );
	
	if( !empty($_access) ) {
		$user_id = $_access->user_id;
		$user_email = $_access->user_email;
		$issue_date = $_access->issue_date;
		$expiry_date = $_access->expiry_date;
		if( intval($user_id) > 0 ) {
			$args = array(
				'customer_id' => $user_id,
				'limit' => -1, // to retrieve _all_ orders by this user
			);
			$orders = wc_get_orders($args);
		} else {
			$query = new WC_Order_Query();
			$query->set( 'customer', $user_email );
			$orders = $query->get_orders();
		}
		
	?>

    <div class="tc-front-container">
        <div class="tc-front-area">
            <h1><?php _e('Round Table Orders', 'TC');?></h1>
			<table id="tc-attendee-listing">
			  <tr>
				<th><?php _e('ID', 'TC');?></th>
				<th><?php _e('Title', 'TC');?></th>
				<th><?php _e('Total', 'TC');?></th>
				<th><?php _e('Attendees', 'TC');?></th>				
				<th><?php _e('Date', 'TC');?></th>
				<th><?php _e('View', 'TC');?></th>
			  </tr>
			  <?php
			  	foreach( $orders as $order ) {
					$order_id = $order->get_id();
					$order_attendees = TC_Orders::get_tickets_ids( $order_id );
					$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );

					$url = get_permalink($tc_roundtable_sub_page);
					$url = add_query_arg('tctoken', $token, $url);
					$url = add_query_arg('oid', $order_id, $url);
					
					if(is_array($order_attendees) && count($order_attendees) > 0) {
						?>
						  <tr>
							<td><?php echo $order_id;?></td>
							<td><?php echo get_the_title($order_id);?></td>
							<td><?php echo $order->get_total();?></td>
							
							<td><?php echo count($order_attendees);?></td>
						    <td><?php echo $order->get_date_created();?></td>
							<td><a href="<?php echo $url;?>"><?php _e('View', 'TC');?></a></td>
						</tr>
					<?php
					}
					
				}
			  ?>
			</table>
        </div>
    </div>


<?php 
	} else {
		echo '<div class="tc_order_attendee_failed">'.__('Invalid or expired token!', 'TC').'</div>';
	}
get_footer(); ?>
