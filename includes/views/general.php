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
    <form method="POST" >
        <table class="setting-table-wrapper">
            <tbody>
                <tr> 
                    <td width="30%" align="left" valign="top">
						<strong><label align="left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Round Table Main Page', 'cs_ld_addon' ); ?></label></strong>
					</td>
                    <td width="70%">
                       <select id="tc_roundtable_main_page" name="tc_roundtable_main_page">
							<option value=""><?php _e('Select Page', 'csld_general_settings_field'); ?></option>
							<?php foreach($pages->posts as $page){ ?>
								<?php if($tc_roundtable_main_page == $page->ID){ ?>
								<option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
								<?php } else{ ?>
								<option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
								<?php } ?>
							<?php }	?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __('The Selected page will list users purchase round tables.', 'cs_ld_addon' ); ?>
                        </p>
                    </td>    
                </tr>    
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Round Table Sub Page', 'cs_ld_addon' ); ?></label></strong>
					</td>
                    <td>
                        <select id="tc_roundtable_sub_page" name="tc_roundtable_sub_page">
							<option value=""><?php _e('Select Page', 'csld_general_settings_field'); ?></option>
							<?php foreach($pages->posts as $page){ ?>
								<?php if($tc_roundtable_sub_page == $page->ID){ ?>
								<option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
								<?php } else{ ?>
								<option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
								<?php } ?>
							<?php }	?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __('The Selected page will show the attendees list.', 'cs_ld_addon' ); ?>
                        </p>
                    </td>    
                </tr>   
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="tc_roundtable_form_page"><?php _e( 'Round Table Form Page', 'cs_ld_addon' ); ?></label></strong>
					</td>
                    <td>
                        <select id="tc_roundtable_form_page" name="tc_roundtable_form_page">
							<option value=""><?php _e('Select Page', 'csld_general_settings_field'); ?></option>
							<?php foreach($pages->posts as $page){ ?>
								<?php if($tc_roundtable_form_page == $page->ID){ ?>
								<option value="<?php echo $page->ID; ?>" selected><?php echo $page->post_title; ?></option>
								<?php } else{ ?>
								<option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
								<?php } ?>
							<?php }	?>
						</select>
                        <p class="description" style="font-weight: normal;">
                            <?php echo __('The Selected page will show the attendees update form.', 'cs_ld_addon' ); ?>
                        </p>
                    </td>    
                </tr>
				<tr> 
                    <td align="left" valign="top">
						<strong><label align = "left" for="ld-cms-schedule-excluded-roles"><?php _e( 'Token Generator Shortcode', 'cs_ld_addon' ); ?></label></strong>
					</td>
                    <td>
                        [Ticket_Token_Generator]
                    </td>    
                </tr>  
				<tr> 
                    <td align="left" valign="top"></td>
                    <td>
                        <div class="submit-button" style="padding-top:10px">
            				<input type="submit" id="save_csld_general_settings" name="save_csld_general_settings" class="button-primary cs-ld-addon-btn" value="<?php _e('Update Settings', 'cs_ld_addon'); ?>" style="float:left;display:block">
        				</div>
                    </td>    
                </tr>            
            </tbody>
        </table>
        
        <?php wp_nonce_field( 'csld_general_settings', 'csld_general_settings_field' ); ?>
    </form>
</div>