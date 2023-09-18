<?php
/**
 * Displays the round table orders.
 */	

/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

global $wpdb;

get_header(); 

$token = isset( $_REQUEST['tctoken'] ) && ! empty( $_REQUEST['tctoken'] ) ? sanitize_text_field( $_REQUEST['tctoken'] ) : '';
$_access =  $wpdb->get_row( $wpdb->prepare('select user_id,user_email from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date > now() and token=%s', $token ) );

if( !empty( $_access ) ) {
	
	$user_id = $_access->user_id;
	$user_email = $_access->user_email;
	if( !empty($user_email) && is_email($user_email) ) {

		$query = new WC_Order_Query();
		$query->set( 'customer', $user_email );
		$orders = $query->get_orders();
	} else {
		$args = array(
			'customer_id' => $user_id,
			'limit' => -1,
		);

		$orders = wc_get_orders( $args );
	}
	?>

	<div class="tc-front-container">
		<div class="tc-front-area">
			<h1><?php _e( 'Round Table Orders', TC_TEXT_DOMAIN );?></h1>
			<table id="tc-attendee-listing">
				<thead>	
					<tr>
						<th><?php _e( 'ID', TC_TEXT_DOMAIN );?></th>
						<th><?php _e( 'Title', TC_TEXT_DOMAIN );?></th>
						<th><?php _e( 'Total', TC_TEXT_DOMAIN );?></th>
						<th><?php _e( 'Attendees', TC_TEXT_DOMAIN );?></th>				
						<th><?php _e( 'Date', TC_TEXT_DOMAIN );?></th>
						<th><?php _e( 'View', TC_TEXT_DOMAIN );?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					
					foreach( $orders as $order ) {
						
						$order_id = $order->get_id();
						foreach( $order->get_items( ['line_item'] ) as $item_id => $item ) {
							
							$item_product_id = $item->get_product_id();
							$is_round_table = get_post_meta( $item_product_id, '_is_round_table', true );
							if( intval( $is_round_table ) == 1 ) {
								
								$order_attendees = TC_Orders::get_tickets_ids( $order_id );
								$tc_roundtable_sub_page = get_option( 'tc_roundtable_sub_page' );

								$url = get_permalink( $tc_roundtable_sub_page );
								$url = add_query_arg( 'tctoken', $token, $url );
								$url = add_query_arg( 'oid', $order_id, $url );
								
								if( is_array( $order_attendees ) && count( $order_attendees ) > 0 ) {
									?>
									<tr>
										<th style="display:none"><?php _e( 'ID', TC_TEXT_DOMAIN );?></th>
										<td><?php echo $order_id;?></td>
										<th style="display:none"><?php _e( 'Title', TC_TEXT_DOMAIN );?></th>
										<td><?php echo get_the_title( $order_id );?></td>
										<th style="display:none"><?php _e( 'Total', TC_TEXT_DOMAIN );?></th>
										<td><?php echo $order->get_total();?></td>
										<th style="display:none"><?php _e( 'Attendees', TC_TEXT_DOMAIN );?></th>	
										<td><?php echo count( $order_attendees );?></td>
										<th style="display:none"><?php _e( 'Date', TC_TEXT_DOMAIN );?></th>
										<td><?php echo $order->get_date_created();?></td>
										<th style="display:none"><?php _e( 'View', TC_TEXT_DOMAIN );?></th>
										<td><a href="<?php echo $url;?>"><?php _e( 'View', TC_TEXT_DOMAIN );?></a></td>
									</tr>
									<?php
								}
								break;
							}
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
} else {

	echo '<div class="tc_order_attendee_failed">'.__('Invalid or expired token!', TC_TEXT_DOMAIN).'</div>';
}

get_footer(); 
?>
