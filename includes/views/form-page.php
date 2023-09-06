<?php 
	get_header(); 
	global $wpdb;
	$token 		= sanitize_text_field($_REQUEST['tctoken']);
	$order_id 	= sanitize_text_field($_REQUEST['oid']);
	$pid 		= sanitize_text_field($_REQUEST['pid']);
	
	$_access =  $wpdb->get_row( $wpdb->prepare('select * from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date > now() and token=%s', $token) );
	if( !empty($_access) ) {
	
		$user_id = $_access->user_id;
		$issue_date = $_access->issue_date;
		$expiry_date = $_access->expiry_date;
		$args = array(
			'customer_id' => $user_id,
			'limit' => -1, // to retrieve _all_ orders by this user
		);
		$orders = wc_get_orders($args);
		
		$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );

		$url = get_permalink($tc_roundtable_sub_page);
		$url = add_query_arg('tctoken', $token, $url);
		$url = add_query_arg('oid', $order_id, $url);


		$tc_roundtable_form_page = get_option( 'tc_roundtable_form_page' );
		$reload = get_permalink($tc_roundtable_form_page);
		$reload = add_query_arg('tctoken', $token, $reload);
		$reload = add_query_arg('oid', $order_id, $reload);
		$reload = add_query_arg('pid', $pid, $reload);
	?>

    <div class="tc-front-container">
        <div class="tc-front-area">
            <h1><?php _e('Round Table Orders', TC_TEXT_DOMAIN);?></h1>
			<a href="<?php echo $url;?>"><?php _e('Back', TC_TEXT_DOMAIN);?></a>
			<div id="tc_order_attendee_message"></div>
			<form id="tc-product-attendees-form">
				<table id="tc-attendee-listing">
				  <tr>
					<th><?php _e('Attendee ID', TC_TEXT_DOMAIN);?></th>
					<th><?php _e('Event ID', TC_TEXT_DOMAIN);?></th>
					<th><?php _e('Ticket Code', TC_TEXT_DOMAIN);?></th>				
					<th><?php _e('Type ID', TC_TEXT_DOMAIN);?></th>
					<th><?php _e('First Name', TC_TEXT_DOMAIN);?></th>
					<th><?php _e('Last Name', TC_TEXT_DOMAIN);?></th>
					<th><?php _e('Email', TC_TEXT_DOMAIN);?></th>
				  </tr>
				  <input type="hidden" name="tctoken" id="tctoken" value="<?php echo $token;?>" />
				  <input type="hidden" name="tc_order_id" id="tc_order_id" value="<?php echo $order_id;?>" />
				  <input type="hidden" name="tc_pro_id" id="tc_pro_id" value="<?php echo $pid;?>" />
				  <input type="hidden" name="action" id="action" value="tc_customization_attendee_update" />
				  <input type="hidden" name="tc_reload" id="tc_reload" value="<?php echo $reload;?>" />
				  
				  <?php
					$order_attendees = TC_Orders::get_tickets_ids( $order_id );
					$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );
					
					$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );
					$sub_page = get_permalink($tc_roundtable_sub_page);
					
					if(is_array($order_attendees) && count($order_attendees) > 0) {
						$editable = 0;
						foreach($order_attendees as $attendee_id) {
							$ticket_code = get_post_meta($attendee_id, 'ticket_code', true);		
							$event_id = get_post_meta($attendee_id, 'event_id', true);
							$item_id = get_post_meta($attendee_id, 'item_id', true);
							$ticket_type_id = get_post_meta($attendee_id, 'ticket_type_id', true);
							$first_name = get_post_meta($attendee_id, 'first_name', true);
							$last_name = get_post_meta($attendee_id, 'last_name', true);
							$owner_email = get_post_meta($attendee_id, 'owner_email', true);	
							$round_table_expiry = get_post_meta( $ticket_type_id, '_round_table_expiry', true ); 	
							if( $pid == $ticket_type_id  ) {

							
								?>
									<tr>
										<td><?php echo $attendee_id;?></td>
										<td><?php echo get_the_title($event_id);?></td>
										<td><?php echo $ticket_code;?></td>
										<td><?php echo get_the_title($ticket_type_id);?></td>
										<?php if( strtotime($round_table_expiry) > time() && $user_id==get_current_user_id() ) { $editable++; ?>
											<input type="hidden" name="tc_attendee_id[]" id="tc_attendee_id" value="<?php echo $attendee_id;?>" />
											<td><input type="text" name="tc_first_name[]" id="tc_first_name" value="<?php echo $first_name;?>" /></td>

											<td><input type="text" name="tc_last_name[]" id="tc_last_name" value="<?php echo $last_name;?>" /></td>
											<td><input type="text" name="tc_owner_email[]" id="tc_owner_email" value="<?php echo $owner_email;?>" /></td>
										<?php } else { ?>
											<td><?php echo $first_name;?></td>
											<td><?php echo $last_name;?></td>
											<td><?php echo $owner_email;?></td>
										<?php } ?>
									</tr>
								<?php
							}
						}
						if( $editable > 0 ) {
							?>
								<tr>
									<td colspan="7" align="center">
										<button type="submit" class="btn btn-primary tc-attendee-button" id="btn_tc_update_attendees"><?php _e('Update', TC_TEXT_DOMAIN);?></button>
									</td>
								</tr>
							<?php
						}
					}
				  ?>
				</table>
			</form>
        </div>
    </div>


<?php 
	} else {
		echo '<div class="tc_order_attendee_failed">'.__('Invalid or expired token!', TC_TEXT_DOMAIN).'</div>';
	}
	get_footer();
?>