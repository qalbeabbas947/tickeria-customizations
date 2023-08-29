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

$tc_round_table_subject  = get_option('tc_round_table_subject');
if(empty($tc_round_table_subject)) {
	$tc_round_table_subject  = __('Round Table Purchase Email', 'cs_ld_addon');
}

$tc_round_table_body  = get_option('tc_round_table_body');
if(empty($tc_round_table_body)) {
	$tc_round_table_body  = __('<p>Dear [user_login],</p><p>Your have successfully purchase a round table. Please, open the <a href="[order_attendees_link]">order attendees</a> page to view and update the attendees detail. Click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', 'cs_ld_addon');
}

?>
<div style="padding:20px 0;">

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="save_tc_settings">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="tc_round_table_subject"><?php _e('Email Subject', 'cs_ld_addon')?></label></th>
                <td >
					<?php echo sprintf('<input type="text" value="%s" id="tc_round_table_subject" name="tc_round_table_subject" class="regular-text">', esc_html($tc_round_table_subject)); ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tc_round_table_body"><?php _e('Email Message', 'cs_ld_addon')?></label></th>
                <td >
					<?php wp_editor( wp_specialchars_decode($tc_round_table_body), "tc_round_table_body", $settings = array('textarea_rows' => 6) ); ?>
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
    <p><strong>[user_name]</strong>: <?php _e('Purchaser username,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><?php _e('john_doe', 'cs_ld_addon' ); ?></strong></em></p>
    <p><strong>[order_attendees_link]</strong>: <?php _e('Order attendees page link', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><?php _e('http://example.com/attendees-page/?token=****', 'cs_ld_addon' ); ?></strong></em></p>
    <p><strong>[round_table_orders_link]</strong>: <?php _e('Order listing page link,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><a href="#"><?php _e('http://example.com/attendees-page/?token=****', 'cs_ld_addon' ); ?></a></strong></em></p>
    <p><strong>[order_id]</strong>: <?php _e('Order ID,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong><?php _e('112', 'cs_ld_addon' ); ?></em></p>
    <p><strong>[order_date]</strong>: <?php _e('Order Date,', 'cs_ld_addon' ); ?> <em><?php _e('example', 'cs_ld_addon' ); ?> <strong> <?php echo date('F jS, Y g:i A', strtotime('2020-05-01')); ?></em></p>
    <p>&nbsp;</p>
</div>