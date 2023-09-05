<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Tickera_Customization_Settings
 */
class Tickera_Customization_Settings {

	private $page_tab;
    
    /**
     * Constructor function
     */
    public function __construct() {

        $this->page_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        add_action( 'admin_menu', [ $this, 'setting_menu' ], 1001 );
        add_action( 'admin_post_save_tc_settings', [ $this, 'save_custom_settings' ] );
    }
    
    /**
     * Add new setting menu under WooCommerce menu
     */
    public function setting_menu() {
        
        /**
         * Add Setting Page
         */
        add_submenu_page(
            'woocommerce',
            __( 'Tickera Settings', 'TC' ),
            __( 'Tickera Settings', 'TC' ),
            'manage_options',
            'tc-customization-settngs',
            [ $this, 'load_setting_menu' ]
        );
    }
	

	/**
     * Save custom settings
     */
    public function save_custom_settings() {

        $url = admin_url('admin.php');
        $url = add_query_arg( 'page', 'tc-customization-settngs', $url );
        
        if( check_admin_referer('save_tc_settings_nonce') ) {

            $current_tab = isset( $_POST['tc_current_tab'] ) ? $_POST['tc_current_tab'] : '';
            
            if( $current_tab === 'round_table_email' ) {

                $tc_round_table_subject = isset( $_POST['tc_round_table_subject'] ) ? sanitize_textarea_field( stripslashes_deep( $_POST['tc_round_table_subject'] ) ) : '';
                $tc_round_table_body = isset( $_POST['tc_round_table_body'] ) ? wp_kses_post( stripslashes_deep( $_POST['tc_round_table_body'] ) ) : '';

                update_option( 'tc_round_table_subject', $tc_round_table_subject );
                update_option( 'tc_round_table_body', $tc_round_table_body );
                $url = add_query_arg( 'tab', 'round_table_email', $url );
            }

            if( $current_tab === 'token_email' ) {

                $tc_token_generation_subject = isset( $_POST['tc_token_generation_subject'] ) ? sanitize_textarea_field( stripslashes_deep( $_POST['tc_token_generation_subject'] ) ) : '';
                $tc_token_generation_body = isset( $_POST['tc_token_generation_body'] ) ? wp_kses_post( stripslashes_deep( $_POST['tc_token_generation_body'] ) ) : '';

                update_option( 'tc_token_generation_subject', $tc_token_generation_subject );
                update_option( 'tc_token_generation_body', $tc_token_generation_body );
                $url = add_query_arg( 'tab', 'token_email', $url );
            }

            if( $current_tab === 'general' ) {

                update_option( 'tc_roundtable_main_page', sanitize_text_field( $_POST['tc_roundtable_main_page'] ) );
			    update_option( 'tc_roundtable_sub_page', sanitize_text_field( $_POST['tc_roundtable_sub_page'] ) );
			    update_option( 'tc_roundtable_form_page', sanitize_text_field( $_POST['tc_roundtable_form_page'] ) );
                $url = add_query_arg( 'tab', 'general', $url );
            }

            $url = add_query_arg( 'updated', 1, $url );
        }

        wp_redirect( $url );
        exit;
    }

    public function load_setting_menu() {
        
		$settings_sections = array(
            'general' => array(
                'title' => __( 'General Settings', 'cs_ld_addon' ),
                'icon' => 'fa-cog',
            ),
            
            'round_table_email' => array(
                'title' => __( 'Purchaser Email', 'cs_ld_addon' ),
                'icon' => 'fa-info',
            ),
			 'token_email' => array(
                'title' => __( 'Token Generation Email', 'cs_ld_addon' ),
                'icon' => 'fa-info',
            ),
        );
		$settings_sections = apply_filters( 'tc_settings_sections', $settings_sections );
        
       
        ?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e( 'Tickera Customization Settings', 'cs_ld_addon' ); ?></h2>
		
			<div class="nav-tab-wrapper">
				<?php
					foreach( $settings_sections as $key => $section ) {
				?>
						<a href="?page=tc-customization-settngs&tab=<?php echo $key; ?>"
							class="nav-tab <?php echo $this->page_tab == $key ? 'nav-tab-active' : ''; ?>">
							<i class="fa <?php echo $section['icon']; ?>" aria-hidden="true"></i>
							<?php _e( $section['title'], 'cs_ld_addon' ); ?>
						</a>
						<?php
						}
						?>
			</div>
		
			<?php
					foreach( $settings_sections as $key => $section ) {
						if( $this->page_tab == $key ) {
							$key = str_replace( '_', '-', $key );
							include( 'views/' . $key . '.php' );
						}
					}
					?>
		</div>
        <?php
    }
}

new Tickera_Customization_Settings();