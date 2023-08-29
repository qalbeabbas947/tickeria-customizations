<?php 
if (!is_user_logged_in() ) {
	wp_redirect ( site_url() );
	exit;
}
else
{
	get_header(); 
	global $wpdb;
	$token 		= sanitize_text_field($_REQUEST['tctoken']);
	$order_id 	= sanitize_text_field($_REQUEST['oid']);
	
	$aid = 0;
	if(isset($_REQUEST['aid']))
		$aid = sanitize_text_field($_REQUEST['aid']);
		
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
		
		$tc_roundtable_main_page 	= get_option( 'tc_roundtable_main_page' );
		$tc_roundtable_form_page 	= get_option( 'tc_roundtable_form_page' );

		$url = get_permalink($tc_roundtable_main_page);
		$url = add_query_arg('tctoken', $token, $url);
	?>
<style>
/* Create two equal columns that floats next to each other */
.tc-product-row {
	display: flex;
	width: 100%;
	flex-wrap: wrap;
}
.tc-product-column {
	padding: 6px;
	margin: 10px;
	border-radius:5px;
	border: 1px solid #ccc;
	width: 29%;
}
.tc-front-area a:hover {
	text-decoration: underline;
}
@media screen and (max-width: 1000px) {
  .tc-product-column {
    width: 44%;
  }
}
@media screen and (max-width: 600px) {
  .tc-product-column {
    width: 100%;
  }
}
</style>


    <div class="tc-front-container">
        <div class="tc-front-area">
            <h1><?php _e('Round Table Orders', 'TC');?></h1>
			<a href="<?php echo $url;?>"><?php _e('Back', 'TC');?></a>
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
					
					$item_index++;
					if($item_index == 1) {
						echo '<div class="tc-product-row">';
					}
					?>	    	
				   <div class="tc-product-column">
						<table>
							<tr><td><img src="<?php echo get_the_post_thumbnail_url( $item->get_product_id(), 'post-thumbnail' );?>" width="90%"/></td></tr>
							<tr><td><?php echo get_the_title($item->get_product_id());?></td></tr>
							<tr><td><a href="<?php echo $temp_url;?>"><?php _e('Get Details', 'TC');?></a></td></tr>
						</table>
				   </div>
						
					<?php
					if($item_index == 3) {
						echo '</div>';
						$item_index = 0;
					}

					
				}
				?>
		</div>
    </div>


<?php 
	} else {
		echo '<div class="tc_order_attendee_failed">'.__('Invalid or expired token!', 'TC').'</div>';
	}
	get_footer(); ?>
<?php } ?>