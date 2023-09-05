<?php
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


$args = array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1 );
$pages = new WP_Query( $args );

$tc_roundtable_main_page 	= get_option( 'tc_roundtable_main_page' );
$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );
$tc_roundtable_form_page	= get_option( 'tc_roundtable_form_page' );
?>
<div id="general_settings" class="cs_ld_tabs"> 
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <table class="setting-table-wrapper">
            <tbody>
                <tr> 
                    <td width="30%" align="left" valign="top">
						<strong><label align="left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Round Table Main Page', TC_TEXT_DOMAIN ); ?></label></strong>
					</td>
                    <td width="70%">
                       <select id="tc_roundtable_main_page" name="tc_roundtable_main_page">
							<option value=""><?php _e('Select Page', TC_TEXT_DOMAIN); ?></option>
							<?php 
                                if( $pages->have_posts() ) {
                                    
                                    foreach( $pages->posts as $page ) { ?>
                                        <?php if( $tc_roundtable_main_page == $page->ID ) { ?>
                                        <option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
                                        <?php } ?>
                                    <?php }	
                                } ?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __('The Selected page will list users purchase round tables.', TC_TEXT_DOMAIN ); ?>
                        </p>
                    </td>    
                </tr>    
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Round Table Sub Page', TC_TEXT_DOMAIN ); ?></label></strong>
					</td>
                    <td>
                        <select id="tc_roundtable_sub_page" name="tc_roundtable_sub_page">
							<option value=""><?php _e('Select Page', 'csld_general_settings_field'); ?></option>
							<?php 
                            
                                if( $pages->have_posts() ) {

                                    foreach( $pages->posts as $page ) { ?>
                                        <?php if( $tc_roundtable_sub_page == $page->ID ) { ?>
                                        <option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
                                        <?php } ?>
                                    <?php }	
                                } ?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __('The Selected page will show the attendees list.', TC_TEXT_DOMAIN ); ?>
                        </p>
                    </td>    
                </tr>   
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="tc_roundtable_form_page"><?php _e( 'Round Table Form Page', TC_TEXT_DOMAIN ); ?></label></strong>
					</td>
                    <td>
                        <select id="tc_roundtable_form_page" name="tc_roundtable_form_page">
							<option value=""><?php _e( 'Select Page', TC_TEXT_DOMAIN ); ?></option>
							<?php 
                            if( $pages->have_posts() ) {

                                foreach( $pages->posts as $page ) { ?>
                                    <?php if( $tc_roundtable_form_page == $page->ID ) { ?>
                                    <option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
                                    <?php } ?>
                                <?php }
                            } ?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __( 'The Selected page will show the attendees update form.', TC_TEXT_DOMAIN ); ?>
                        </p>
                    </td>    
                </tr>
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Token Generator Shortcode', TC_TEXT_DOMAIN ); ?></label></strong>
					</td>
                    <td>
                        [Ticket_Token_Generator]
                    </td>    
                </tr>            
            </tbody>
        </table>
        
        <div class="submit-button" style="padding-top:10px">
            <input type="hidden" value="general" name="tc_current_tab">
            <input type="hidden" name="action" value="save_tc_settings">
            <input type="submit" name="save_tc_settings" class="button-primary" value="<?php _e('Update Settings', 'cs_ld_addon' ); ?>">
        </div>
        <?php wp_nonce_field( 'save_tc_settings_nonce' ); ?>
    </form>
</div>