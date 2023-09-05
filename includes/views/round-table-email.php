<?php
/**
 * Purchaser Email UI/UX
 */

/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tc_round_table_subject  = get_option( 'tc_round_table_subject' );
$tc_round_table_subject = !empty( $tc_round_table_subject ) ? $tc_round_table_subject : __( 'Round Table Purchase Email', TC_TEXT_DOMAIN );
$tc_round_table_body  = get_option( 'tc_round_table_body' );
$tc_round_table_body_default  = __( '<p>Dear [user_login],</p><p>Your have successfully purchase a round table. Please, open the <a href="[order_attendees_link]">order attendees</a> page to view and update the attendees detail. Click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', TC_TEXT_DOMAIN );
$tc_round_table_body = !empty( $tc_round_table_body ) ? $tc_round_table_body : $tc_round_table_body_default;


?>
<div style="padding:20px 0;">

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="tc_round_table_subject"><?php _e( 'Email Subject', TC_TEXT_DOMAIN )?></label></th>
                <td >
					<?php echo sprintf('<input type="text" value="%s" id="tc_round_table_subject" name="tc_round_table_subject" class="regular-text">', esc_html( $tc_round_table_subject ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tc_round_table_body"><?php _e( 'Email Message', TC_TEXT_DOMAIN )?></label></th>
                <td >
					<?php wp_editor( wp_specialchars_decode( $tc_round_table_body ), "tc_round_table_body", $settings = array( 'textarea_rows' => 6 ) ); ?>
                </td>
            </tr>
            
            </tbody>
        </table>


        <div class="submit">
            <input type="hidden" value="round_table_email" name="tc_current_tab">
            <input type="hidden" name="action" value="save_tc_settings">
            <input type="submit" name="save_tc_settings" class="button-primary" value="<?php _e( 'Update Settings', TC_TEXT_DOMAIN ); ?>">
        </div>
		<?php wp_nonce_field( 'save_tc_settings_nonce' ); ?>
    </form>

    <p><?php _e( 'Email subject and message can be personalize by using following placeholders:', TC_TEXT_DOMAIN ); ?></p>
    <p><strong>[user_name]</strong>: <?php _e( 'Purchaser username,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><?php _e( 'john_doe', TC_TEXT_DOMAIN ); ?></strong></em></p>
    <p><strong>[order_attendees_link]</strong>: <?php _e( 'Order attendees page link', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><?php _e( 'http://example.com/attendees-page/?token=****', TC_TEXT_DOMAIN ); ?></strong></em></p>
    <p><strong>[round_table_orders_link]</strong>: <?php _e( 'Order listing page link,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><a href="#"><?php _e( 'http://example.com/attendees-page/?token=****', TC_TEXT_DOMAIN ); ?></a></strong></em></p>
    <p><strong>[order_id]</strong>: <?php _e( 'Order ID,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><?php _e( '112', TC_TEXT_DOMAIN ); ?></em></p>
    <p><strong>[order_date]</strong>: <?php _e( 'Order Date,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong> <?php echo date( 'F jS, Y g:i A', strtotime( '2020-05-01' ) ); ?></em></p>
    <p>&nbsp;</p>
</div>