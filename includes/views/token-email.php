<?php
/**
 * Token Generation Email UI/UX
 */

/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tc_token_generation_subject  = get_option( 'tc_token_generation_subject' );
$tc_token_generation_subject = !empty( $tc_token_generation_subject ) ? $tc_token_generation_subject : __( 'Round Table Token Generation Email', TC_TEXT_DOMAIN );
$tc_token_generation_body  = get_option( 'tc_token_generation_body' );
$tc_token_generation_body_default  = __( '<p>Dear [user_login],</p><p>Your token is generated successfully. Please, click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', TC_TEXT_DOMAIN );
$tc_token_generation_body = !empty( $tc_token_generation_body ) ? $tc_token_generation_body : $tc_token_generation_body_default;

?>
<div style="padding:20px 0;">
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="tc_token_generation_subject"><?php _e('Email Subject', TC_TEXT_DOMAIN )?></label></th>
                <td >
					<?php echo sprintf( '<input type="text" value="%s" id="tc_token_generation_subject" name="tc_token_generation_subject" class="regular-text">', esc_html( $tc_token_generation_subject ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tc_token_generation_body"><?php _e('Email Message', TC_TEXT_DOMAIN )?></label></th>
                <td >
					<?php wp_editor( wp_specialchars_decode( $tc_token_generation_body ), "tc_token_generation_body", $settings = array( 'textarea_rows' => 6 ) ); ?>
                </td>
            </tr>
            
            </tbody>
        </table>


        <div class="submit">
            <input type="hidden" value="token_email" name="tc_current_tab">
            <input type="hidden" name="action" value="save_tc_settings">
            <input type="submit" name="save_tc_settings" class="button-primary" value="<?php _e( 'Update Settings', TC_TEXT_DOMAIN ); ?>">
        </div>
		<?php wp_nonce_field( 'save_tc_settings_nonce' ); ?>
    </form>

    <p><?php _e( 'Email subject and message can be personalize by using following placeholders:', TC_TEXT_DOMAIN ); ?></p>
    <p><strong>[user_login]</strong>: <?php _e( 'Purchaser username,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><?php _e( 'john_doe', TC_TEXT_DOMAIN ); ?></strong></em></p>
    <p><strong>[round_table_orders_link]</strong>: <?php _e( 'Order listing page link,', TC_TEXT_DOMAIN ); ?> <em><?php _e( 'example', TC_TEXT_DOMAIN ); ?> <strong><a href="#"><?php _e( 'http://example.com/token-page/?token=****', TC_TEXT_DOMAIN ); ?></a></strong></em></p>
    <p>&nbsp;</p>
</div>