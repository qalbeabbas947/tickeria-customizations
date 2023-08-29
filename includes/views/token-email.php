<?php
/**
 * Settings tab Quiz Completion
 */

/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tc_token_generation_subject  = get_option('tc_token_generation_subject');
if(empty($tc_token_generation_subject)) {
	$tc_token_generation_subject  = __('Round Table Token Generation', 'cs_ld_addon');
}

$tc_token_generation_body  = get_option('tc_token_generation_body');
if(empty($tc_token_generation_body)) {
	$tc_token_generation_body  = __('<p>Dear [user_login],</p><p>Your token is generated successfully. Please, click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', 'cs_ld_addon');
}

?>
<div style="padding:20px 0;">
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="save_tc_settings">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="tc_token_generation_subject"><?php _e('Email Subject', 'cs_ld_addon')?></label></th>
                <td >
					<?php echo sprintf('<input type="text" value="%s" id="tc_token_generation_subject" name="tc_token_generation_subject" class="regular-text">', esc_html($tc_token_generation_subject)); ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tc_token_generation_body"><?php _e('Email Message', 'cs_ld_addon')?></label></th>
                <td >
					<?php wp_editor( wp_specialchars_decode($tc_token_generation_body), "tc_token_generation_body", $settings = array('textarea_rows' => 6) ); ?>
                </td>
            </tr>
            
            </tbody>
        </table>


        <div class="submit">
            <input type="submit" name="save_tc_settings" class="button-primary" value="<?php _e('Update Settings', 'cs_ld_addon' ); ?>">
        </div>
		<?php wp_nonce_field( 'save_tc_settings_nonce' ); ?>
    </form>

    <p><?php _e('Email subject and message can be personalize by using following placeholders:', 'cs_ld_addon'); ?></p>
    <p><strong>[user_login]</strong>: <?php _e('Purchaser username,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><?php _e('john_doe', 'cs_ld_addon' ); ?></strong></em></p>
    <p><strong>[round_table_orders_link]</strong>: <?php _e('Order listing page link,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><a href="#"><?php _e('http://example.com/token-page/?token=****', 'cs_ld_addon' ); ?></a></strong></em></p>
    <p>&nbsp;</p>
</div>