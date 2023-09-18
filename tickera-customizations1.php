<?php
/**
 * Plugin Name: Tickera Customization
 * Version: 1.0
 * Description:
 * Author: LDninjas
 * Author URI: ldninjas.com
 * Plugin URI: ldninjas.com
 * Text Domain: TC
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Runs on plugin activation
 */
function tc_activation() {

	global $wpdb;

	$table_name = $wpdb->prefix . 'tc_attendee_tokens';
	if( $wpdb->get_var("show tables like '$table_name'" ) != $table_name ) {

		$sql = "CREATE TABLE " . $table_name . " (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NULL,
			`user_email` varchar(255) NULL,
			`token` varchar(50) NOT NULL,
			`issue_date` datetime  NULL,
			`expiry_date` datetime  NULL,
			PRIMARY KEY (`id`)
		);";
		
		$wpdb->query($sql);
	}
}
register_activation_hook( __FILE__, 'tc_activation' );

/**
 * Class Tickera_Customization
 */
class Tickera_Customization {

    const VERSION = '1.0';

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof Tickera_Customization ) ) {
            self::$instance = new self;

            self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * includes plugin files
     */
    private function includes() {
        
        if( file_exists( TC_INCLUDES_DIR.'settings.php' ) ) {
            require_once( TC_INCLUDES_DIR . 'settings.php' );
        }   

        if( file_exists( TC_INCLUDES_DIR.'template-pages.php' ) ) {
            require_once( TC_INCLUDES_DIR . 'template-pages.php' );
        }       
    }

    /**
     * defining constants for plugin
     */
    private function setup_constants() {

        /**
         * Directory
         */
        define( 'TC_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'TC_DIR_FILE', TC_DIR . basename ( __FILE__ ) );
        define( 'TC_INCLUDES_DIR', trailingslashit ( TC_DIR . 'includes' ) );
        define( 'TC_BASE_DIR', plugin_basename(__FILE__));

        /**
         * URLs
         */
        define( 'TC_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'TC_ASSETS_URL', trailingslashit ( TC_URL . 'assets/' ) );

        /**
         * Text Domain
         */
        define( 'TC_TEXT_DOMAIN', 'TC' );
    }

	/**
     * Plugin Hooks
     */
    private function hooks() {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL);
		/**
		 * Enqueue scripts
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'tc_add_front_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'tc_add_admin_scripts' ] );

        add_action( 'wp_ajax_nopriv_tc_customization_attendee_update', [ $this, 'tc_customization_attendee_update' ] );
		add_action( 'wp_ajax_tc_customization_attendee_update', [ $this, 'tc_customization_attendee_update' ] );

		add_action( 'wp_ajax_nopriv_tc_customization_token_generator', [ $this, 'tc_customization_token_generator' ] );
		add_action( 'wp_ajax_tc_customization_token_generator', [ $this, 'tc_customization_token_generator' ] );

		add_action( 'wp_ajax_tc_customization_token_generator_admin', [ $this, 'tc_customization_token_generator_admin' ] );
		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'add_link_back_to_order' ], 10, 1 );
        
		/**
		 * Cron email testing hook
		 */
		
		// if(isset($_REQUEST['tcrun']) && $_REQUEST['tcrun'] == 'now') {
		// 	add_action('init', array( $this, 'process_attendees_email' ), 10, 0);
		// }

       /**
		* To display and save tickeria custom fields on product edit page.
	    */
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'tc_add_custom_settings' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'tc_custom_settings_fields_save' ], 10, 1 );
		
		/**
		 * To send round table email when status is set to processing.
		 */
		add_action('woocommerce_order_status_processing', [ $this, 'woocommerce_order_status_completed_callback' ], 10, 1 );
		//add_filter('cron_schedules', [ $this, 'my_cron_schedules' ], 9999, 1);
		add_action( 'tc_process_attendees_emails', [ $this, 'process_attendees_email' ] );
		if ( ! wp_next_scheduled( 'tc_process_attendees_emails' ) ) {
			wp_schedule_event( time(), 'hourly', 'tc_process_attendees_emails' );
		}
		
		add_shortcode( 'Ticket_Token_Generator', [ $this,'tc_token_generator' ] );
		add_filter( 'tc_order_is_paid', [ $this,'tc_order_is_paid_callback' ], 9999, 2 );
	}

	/**
	 * Cron test schedules of 5mins.
	 */
	function my_cron_schedules($schedules){
		
		if(!isset($schedules["5min"])){
			$schedules["5min"] = array(
				'interval' => 5*60,
				'display' => __('Once every 5 minutes'));
		}
		
		return $schedules;
	}
	

	/**
	 * Add link to generate token id from order edit page.
	 */
	public function add_link_back_to_order( $order ) {
		
		$link = '<p class="tc_generate_token_order_link" data-order_id="'.$order->get_id().'" data-user_id="'.$order->get_user_id().'">';
		$link .= '<a href="javascript:;" id="tc_generate_token_order" data-order_id="'.$order->get_id().'" data-user_id="'.$order->get_user_id().'">';
		$link .= __( 'Click here to generate new token for your round table orders.', TC_TEXT_DOMAIN );
		$link .= '</a>';
		$link .= '</p>';
		
		echo $link;
	}

	/**
	 * Return orders of an email
	 */
	public function get_orders_by_billing_email( $user_email ) {
		
		$query = new WC_Order_Query();
		$query->set( 'customer', $user_email );
		return $orders = $query->get_orders();
	}

	/**
     * generate token and send email from front-end.
     */
	public function tc_customization_token_generator() {
		
		global $wpdb;
		
		$email_for_token 	= sanitize_email( $_REQUEST['email_for_token'] );
		if( !is_email( $email_for_token ) ) {

			echo json_encode( [ 'status'=>'failed', 'message'=>__( 'Entered email is not correct.', TC_TEXT_DOMAIN ) ] );
			exit;
		}
		
		$orders = $this->get_orders_by_billing_email( $email_for_token );
		if( count( $orders ) < 1 ) {

			echo json_encode( [ 'status'=>'failed', 'message'=>__( 'Order not found.', TC_TEXT_DOMAIN ) ] );
			exit;
		}
		
		$user_email = $email_for_token;
		$user_name = $orders[0]->get_billing_first_name().' '.$orders[0]->get_billing_last_name();
		
		$_access = $wpdb->get_results( $wpdb->prepare( 'select id,expiry_date,token from '.$wpdb->prefix.'tc_attendee_tokens where user_email=%s', $user_email ) );
		if( empty( $_access ) || count( $_access ) < 1 ) {
			
			$token = md5( time() );
			$sql_query = "insert into ".$wpdb->prefix."tc_attendee_tokens(user_id,user_email,token,issue_date,expiry_date) values('0','".$user_email."','".$token."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s', strtotime('+1 Day'))."')";
			$wpdb->query( $sql_query );
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status'=>'success', 'message'=>__( 'We have successfully sent a new login link to your email address.', TC_TEXT_DOMAIN ) ] );
			exit;
		}
		
		if( time() >= strtotime( $_access[0]->expiry_date ) ) {
			$token = md5( time() );
			$wpdb->update( $wpdb->prefix.'tc_attendee_tokens',
				[
					'expiry_date' => date( 'Y-m-d H:i:s', strtotime( '+1 Day' ) ),
					'token' => $token
				], 
				[ 'id'=> $_access[0]->id ]
			);	
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status'=>'success', 'message'=>__( 'Your updated login link is sent to your Email address.', TC_TEXT_DOMAIN ) ] );
			exit;

		} else {

			$token = $_access[0]->token;
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status'=>'success', 'message'=>__( 'Your login link is already active. An Email with your current login link was sent to your email address.', TC_TEXT_DOMAIN ) ] );
			exit;
		}
	}

	/**
	 * Send token generation email.
	 */
	public function tc_send_token_gen_email($user_name, $user_email, $token) {

		$subject = get_option( 'tc_token_generation_subject' );
		if( empty( $subject ) ) {
			$subject  = __('Round Table Token Generation', TC_TEXT_DOMAIN );
		}
		
		$message = get_option( 'tc_token_generation_body' );
		if(empty($message)) {
			$message  = __( '<p>Dear [user_login],</p><p>Your token is generated successfully. Please, click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', TC_TEXT_DOMAIN );
		}
		
		$tc_roundtable_main_page = get_option( 'tc_roundtable_main_page' );
		$round_table_orders_link = get_permalink( $tc_roundtable_main_page );
		$round_table_orders_link = add_query_arg( 'tctoken', $token, $round_table_orders_link );
		
		$subject = str_replace( array(
			'[user_login]',
			'[round_table_orders_link]',
		), array(
			$user_name,
			$round_table_orders_link,
		), $subject);

		$message = str_replace(array(
			'[user_login]',
			'[round_table_orders_link]',
		), array(
			$user_name,
			$round_table_orders_link,
		), $message);

		$message_template_header = dirname(__FILE__) . '/includes/views/message_header.php';
		$message_template_footer = dirname(__FILE__) . '/includes/views/message_footer.php';

		ob_start();
		include_once $message_template_header;
		echo wpautop( $message );
		include_once $message_template_footer;
		$message = ob_get_clean();

		$site_name = get_option( 'blogname' );
		$admin_email = get_option( 'admin_email' );
		
		$headers = [];
		$headers[] = "From: {$site_name} <{$admin_email}>";
		$headers[] = "Content-Type: text/html; charset=UTF-8"; 
		
		return wp_mail( $user_email, $subject, $message, $headers );
	}

	/**
     * Update attendees
     */
	public function tc_customization_attendee_update() {
		
		global $wpdb;
		
		$tc_order_id = sanitize_text_field( $_REQUEST['tc_order_id'] );
		$order = wc_get_order( $tc_order_id );
		$tc_attendee_id = $_REQUEST['tc_attendee_id'];
		$tc_first_name 	= $_REQUEST['tc_first_name'];
		$tc_last_name 	= $_REQUEST['tc_last_name'];
		$tc_email 	= $_REQUEST['tc_owner_email'];
		$tctoken 		= $_REQUEST['tctoken'];

		if( empty( $tctoken ) || 
			( !is_array( $tc_first_name ) || count( $tc_first_name ) < 1 ) || 
			( !is_array( $tc_first_name ) || count( $tc_first_name ) < 1 ) || 
			( !is_array( $tc_email ) || count( $tc_email ) < 1 ) ) {

			echo json_encode( ['status'=>'failed', 'message' => 'Fields can not be empty'] );
			exit;
		}
		
		$_access =  $wpdb->get_results( $wpdb->prepare('select id from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date>now() and token=%s', $tctoken ) );
		if( empty( $_access ) || count( $_access ) < 1 ) {

			echo json_encode( ['status'=>'failed', 'message'=>'Invalid or expired token!'] );
			exit;
		}

		if( isset( $tc_attendee_id ) && is_array( $tc_attendee_id ) && count( $tc_attendee_id ) > 0 ) {
			
			foreach( $tc_attendee_id as $key => $aid ) {

				if( !empty( $tc_first_name[$key] ) && !empty( $tc_last_name[$key] ) && is_email( $tc_email[$key] ) ) {

					update_post_meta( $tc_attendee_id[$key], 'first_name', sanitize_text_field( $tc_first_name[$key] ) );
					update_post_meta( $tc_attendee_id[$key], 'last_name', sanitize_text_field( $tc_last_name[$key] ) );
					update_post_meta( $tc_attendee_id[$key], 'owner_email', sanitize_email( $tc_email[$key] ) );
				}
			}
			echo json_encode( ['status'=>'success', 'message'=>'Attendees data is updated successfully!'] );
			exit;
		}

		echo json_encode( ['status'=>'failed', 'message'=>'Invalid attempt'] );
		exit;
	}
	
    /**
	 * Restrict tickeria to send round table emails.
	 */
	public function tc_order_is_paid_callback( $order_is_paid, $order_id ) {
		
		$order = wc_get_order( $order_id );
		
		$is_round_table = 0;
		foreach( $order->get_items( ['line_item'] ) as $item_id => $item ) {
			$item_product_id = $item->get_product_id();
			$is_round_table = get_post_meta( $item_product_id, '_is_round_table', true );
			if( intval( $is_round_table ) == 1 ) {
				$is_round_table = 1;
			}
		}

		if( $is_round_table == 1 ) {
			return false;
		}
		
		return $order_is_paid;
	}

	/**
	 * Generate token from order edit page
	 */
	public function tc_customization_token_generator_admin() {
		
		global $wpdb;
		
		$order_id = sanitize_text_field( $_REQUEST['order_id'] );
		$order = wc_get_order( $order_id );
		
		if( ! $order ) {
			
			echo json_encode( ['status'=>'success', 'message' => 'There is some glitch in this order.'] );
			exit;
		}

		$user_email = $order->get_billing_email();
		$user_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();

		$token = '';
		$_access = $wpdb->get_results( $wpdb->prepare( 'select id,expiry_date,token from '.$wpdb->prefix.'tc_attendee_tokens where user_email=%s', $user_email ) );
		if( empty( $_access ) && count( $_access ) < 1 ) {
			
			$token = md5( time() );
			$sql_query = "insert into ".$wpdb->prefix."tc_attendee_tokens(user_id,user_email,token,issue_date,expiry_date) values('0','".$user_email."','".$token."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s', strtotime('+1 Day'))."')";
			$wpdb->query( $sql_query );
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status' => 'success', 'message' => __( 'We have successfully sent a new login link to your email address.', TC_TEXT_DOMAIN ) ] );
			exit;
		}

		if( time() >= strtotime( $_access[0]->expiry_date ) ) {
			
			$token = md5( time() );
			$wpdb->update( $wpdb->prefix.'tc_attendee_tokens',
				[
					'expiry_date' => date( 'Y-m-d H:i:s', strtotime( '+1 Day' ) ),
					'token' => $token
				], 
				[ 'id'=> $_access[0]->id ]
			);	
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status' => 'success', 'message' => __( 'Your updated login link is sent to your Email address.', TC_TEXT_DOMAIN ) ] );
			exit;

		} else {

			$token = $_access[0]->token;
			$this->tc_send_token_gen_email( $user_name, $user_email, $token );
			echo json_encode( [ 'status' => 'success', 'message' => __( 'Your login link is already active. An Email with your current login link was sent to your email address.', TC_TEXT_DOMAIN ) ] );
			exit;
		}
	}
	
	/**
	 * Shortcode to display token generator form at front-end.
	 */
	public function tc_token_generator( $atts ) {

		ob_start();
		$page_template = dirname( __FILE__ ) . '/includes/views/token-generator.php'; 
		include_once( $page_template );
		$content = ob_get_contents();
		ob_get_clean();
	
		return $content;
	}
	
	/**
	 * send email upon processing status of order for round table tickets.
	 */
	public function woocommerce_order_status_completed_callback( $order_id ) {
		
		global $wpdb;

		$tc_round_table_email_sent = get_post_meta( $order_id, 'tc_round_table_email_sent', true );
		if( $tc_round_table_email_sent == 'Yes' ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		$is_round_table = 0;
		foreach( $order->get_items( ['line_item'] ) as $item_id => $item ) {
			$item_product_id = $item->get_product_id();
			$is_round_table = get_post_meta( $item_product_id, '_is_round_table', true );
			if( intval( $is_round_table ) == 1 ) {
				$is_round_table = 1;
			}
		}

		if( $is_round_table == 0 ) {
			return false;
		}
		
		$_access =  $wpdb->get_results( $wpdb->prepare('select * from '.$wpdb->prefix.'tc_attendee_tokens where expiry_date>now() and (user_id=%d and user_email=%s)', $order->get_user_id(), $order->get_billing_email() ) );
		if( !empty( $_access ) && count( $_access ) > 0 ) {
			$token = $_access[0]->token;
		} else {
			$token = md5( time() . $order->get_user_id() );
			$sql_query = "insert into ".$wpdb->prefix."tc_attendee_tokens(user_id,user_email,token,issue_date,expiry_date) values('".$order->get_user_id()."','".$order->get_billing_email()."','".$token."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s', strtotime('+1 Day'))."')";
			$wpdb->query( $sql_query );
		}

		$tc_round_table_subject  = get_option( 'tc_round_table_subject' );
		$subject = !empty( $tc_round_table_subject ) ? $tc_round_table_subject : __( 'Round Table Purchase Email', TC_TEXT_DOMAIN );
		
		$tc_round_table_body  = get_option( 'tc_round_table_body' );
		$tc_round_table_body_default  = __( '<p>Dear [user_login],</p><p>Your have successfully purchase a round table. Please, open the <a href="[order_attendees_link]">order attendees</a> page to view and update the attendees detail. Click <a href="[round_table_orders_link]">here</a> to view the available round table orders.</p><p>Thank You</p>', TC_TEXT_DOMAIN );
		$message = !empty( $tc_round_table_body ) ? $tc_round_table_body : $tc_round_table_body_default;
		
		
		$order_date = $order->get_date_created();
		
		$tc_roundtable_main_page 	= get_option( 'tc_roundtable_main_page' );
		$round_table_orders_link = get_permalink( $tc_roundtable_main_page );
		$round_table_orders_link = add_query_arg( 'tctoken', $token, $round_table_orders_link );

		$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );
		$order_attendees_link = get_permalink( $tc_roundtable_sub_page );
		$order_attendees_link = add_query_arg( 'tctoken', $token, $order_attendees_link );
		$order_attendees_link = add_query_arg( 'oid', $order_id, $order_attendees_link );

		if( intval( $order->get_user_id() ) > 0 ) {
			$user = get_userdata( $order->get_user_id() );
			$user_name = $user->user_login;
			$user_email = $user->user_email;
		} else {
			$user_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
			$user_email = $order->get_billing_email();
		}

		$subject = str_replace( array(
			'[user_name]',
			'[order_attendees_link]',
			'[round_table_orders_link]',
			'[order_id]',
			'[order_date]'
		), array(
			$user_name,
			$order_attendees_link,
			$round_table_orders_link,
			$order_id,
			$order_date
		), $subject );

		$message = str_replace( array(
			'[user_name]',
			'[order_attendees_link]',
			'[round_table_orders_link]',
			'[order_id]',
			'[order_date]'
		), array(
			$user_name,
			$order_attendees_link,
			$round_table_orders_link,
			$order_id,
			$order_date
		), $message );

		$message_template_header = dirname(__FILE__) . '/includes/views/message_header.php';
		$message_template_footer = dirname(__FILE__) . '/includes/views/message_footer.php';

		ob_start();
		include_once $message_template_header;
		echo wpautop($message);
		include_once $message_template_footer;
		$message = ob_get_clean();

		$site_name = get_option( 'blogname' );
		$admin_email = get_option( 'admin_email' );
		
		$headers = [];
		$headers[] = "From: {$site_name} <{$admin_email}>";
		$headers[] = "Content-Type: text/html; charset=UTF-8"; 
		update_post_meta( $order_id, 'tc_round_table_email_sent', 'Yes' );
		return wp_mail( $user_email, $subject, $message, $headers );
	}

    
    /**
	 * Save Tickera custom fields located on the Woo product page
	 *
	 * @param int $post_id
	 */
	public function tc_custom_settings_fields_save( $post_id ) {
		
		$post_id = (int) $post_id;

		// Check if product is a ticket
		$_is_round_table = ( isset( $_POST['_is_round_table'] ) ? 'yes' : 'no' );
		
		if ( 'yes' == $_is_round_table ) {
			
			// Save related event
			$_is_round_table = ( empty($_POST['_is_round_table']) ? '' : (int) $_POST['_is_round_table'] );
			update_post_meta( $post_id, '_is_round_table', (int) $_is_round_table );
			
			// Save choosen ticket template
			$_round_table_expiry = ( empty($_POST['_round_table_expiry']) ? '' : $_POST['_round_table_expiry'] );
			update_post_meta( $post_id, '_round_table_expiry', $_round_table_expiry );
			
		
		} else {
			delete_post_meta( $post_id, '_is_round_table' );
			delete_post_meta( $post_id, '_round_table_expiry' );
		}
	}
	
    /**
	 * Add custom Tickera fields to the admin product screen
	 *
	 * @global mixed $woocommerce
	 * @global object $post
	 */
	public function tc_add_custom_settings() {

		global  $post ;

		$is_round_table 	= get_post_meta( $post->ID, '_is_round_table', true );
		$round_table_expiry = get_post_meta( $post->ID, '_round_table_expiry', true );
		
		// Currently use as separator.
		echo  '<div class="options_group"></div>' ;
		
		if ( method_exists( 'TC_Ticket', 'is_sales_available' ) ) {
			// Allow Ticket Checkout
			echo  '<div class="options_group">' ;
			woocommerce_wp_checkbox( [
					'id'          => '_is_round_table',
					'label'       => __( 'Is Round Table?', TC_TEXT_DOMAIN ),
					'desc_tip'    => 'true',
					'cbvalue'    => '1',
					'value'		  => $is_round_table,
					'description' => __( 'If enabled, the round table functionality will be available on the checkout.', TC_TEXT_DOMAIN ),
				] );
			echo  '</div>' ;
			
			
			// During selected date range
			echo  '<div id="_ticket_checkin_availability_dates">' ;
			woocommerce_wp_text_input( [
				'id'    => '_round_table_expiry',
				'class' => 'tc_date_field',
				'value'	=> $round_table_expiry,
				'label' => __( 'Round Table Deadline Date', TC_TEXT_DOMAIN ),
			] );
		   
		}
		
		echo  '</div>' ;
	}

    /**
     * Adds frontend scripts
     */
    public function tc_add_admin_scripts() {
        
        wp_enqueue_style( 'tc-admin-css', TC_ASSETS_URL . 'css/admin.css', [], time(), null );
        wp_enqueue_script( 'tc-admin-js', TC_ASSETS_URL . 'js/admin.js?tctime='.time(), [ 'jquery' ], time(), true );

        wp_localize_script( 'tc-admin-js', 'TC_Customization', [
            'ajaxURL'       => admin_url( 'admin-ajax.php' ),
            'save_label' => __( 'Save', TC_TEXT_DOMAIN ),
           
        ] );
    }

    /**
     * Adds frontend scripts
     */
    public function tc_add_front_scripts() {

        wp_enqueue_style( 'tc-frontend-css', TC_ASSETS_URL . 'css/frontend.css?'.time(), [], time(), null );
        wp_enqueue_script( 'tc-frontend-js', TC_ASSETS_URL . 'js/frontend.js?'.time(), [ 'jquery' ], time(), true );

        wp_localize_script( 'tc-frontend-js', 'TC_Customization', [
            'ajaxURL'       => admin_url( 'admin-ajax.php' )
        ] );
    }
	
	/**
	 * Return order ids by product id
	 */
	public function get_orders_id_from_product_id( $product_id, $args = array() ) {
		
		$query = new WC_Order_Query( $args );
		$order_ids = $query->get_orders();
		
		$filtered_order_ids = array();
		foreach ($order_ids as $order_id) {
			$order = wc_get_order( $order_id );
			$order_items = $order->get_items();
			foreach ($order_items as $item) {
				if( $item->get_product_id() == $product_id ) {
				 
					array_push( $filtered_order_ids, $order_id );
					break;
				}
			}
		}

		return $filtered_order_ids;
	}

	/**
	 * Send email on paid orders
	 *
	 * @param $order_id
	 */
	public function tc_order_paid_attendee_email( $order_id, $product_id = 0 ) {
		
		global $tc;
		
		$tc_email_sent = get_post_meta( $order_id, 'tc_email_sent', true );
		if( $tc_email_sent != 'Yes' ) {

			$tc_email_settings = get_option( 'tc_email_setting', false );
			$email_send_type = isset( $tc_email_settings[ 'email_send_type' ] ) ? $tc_email_settings[ 'email_send_type' ] : 'wp_mail';
		
			add_filter( 'wp_mail_content_type', 'set_content_type' );
			if ( ! function_exists( 'set_content_type' ) ) {
	
				function set_content_type( $content_type ) {
					return 'text/html';
				}
			}
	
			add_filter( 'wp_mail_from', 'attendee_email_from_email', 999 );
			add_filter( 'wp_mail_from_name', 'attendee_email_from_name', 999 );
	
			$subject = isset( $tc_email_settings[ 'attendee_order_subject' ] ) ? $tc_email_settings[ 'attendee_order_subject' ] : __( 'Your Ticket is here!', TC_TEXT_DOMAIN );
			$default_message = 'Hello, <br /><br />You can download ticket for EVENT_NAME here DOWNLOAD_URL';
			$order = new TC_Order( $order_id );
	
			$tc_attendee_order_message = isset( $tc_email_settings[ 'attendee_order_message' ] ) ? $tc_email_settings[ 'attendee_order_message' ] : '';
			$tc_attendee_order_message = apply_filters( 'tc_attendee_order_message', $tc_attendee_order_message, $order );
	
			$attendee_headers = '';
			$order_attendees = TC_Orders::get_tickets_ids( $order->details->ID );
			$done = 0;
			foreach ( $order_attendees as $order_attendee_id ) {
				
				$ticket_meta = get_post_meta( $order_attendee_id );
				
				$ticket_type_id = isset( $ticket_meta[ 'ticket_type_id' ] ) ? reset( $ticket_meta[ 'ticket_type_id' ] ) : '';
				$email_sent = get_post_meta( $order_attendee_id, 'tc_email_sent', true );	
				
				if($product_id == $ticket_type_id && $email_sent!='Yes' ) {
					
					$ticket_type_name = get_the_title( $ticket_type_id );
	
					$ticket_code = isset( $ticket_meta[ 'ticket_code' ] ) ? reset( $ticket_meta[ 'ticket_code' ] ) : '';
					$ticket_code = strtoupper( $ticket_code );
		
					$event_id = isset( $ticket_meta[ 'event_id' ] ) ? reset( $ticket_meta[ 'event_id' ] ) : '';
					$event_id = (int) $event_id;
		
					$first_name = isset( $ticket_meta[ 'first_name' ] ) ? reset( $ticket_meta[ 'first_name' ] ) : '';
					$last_name = isset( $ticket_meta[ 'last_name' ] ) ? reset( $ticket_meta[ 'last_name' ] ) : '';
					$owner_email = isset( $ticket_meta[ 'owner_email' ] ) ? reset( $ticket_meta[ 'owner_email' ] ) : '';
		
					$event = new TC_Event( $event_id );
					$event_location = get_post_meta( $event_id, 'event_location', true );
		
					$message = isset( $tc_attendee_order_message ) ? $tc_attendee_order_message : $default_message;
					$placeholders = array( 'EVENT_NAME', 'DOWNLOAD_URL', 'TICKET_TYPE', 'TICKET_CODE','FIRST_NAME', 'LAST_NAME', 'EVENT_LOCATION' );
					$placeholder_values = array( $event->details->post_title, tc_get_ticket_download_link( '', '', $order_attendee_id, true ), $ticket_type_name, $ticket_code,$first_name, $last_name, $event_location );
		
					if ( ! empty( $owner_email ) ) {
		
						// Generate pdf file
						$templates = new TC_Ticket_Templates();
						$enabled_attachment = ( isset( $tc_email_settings[ 'attendee_attach_ticket' ] ) && 'yes' == $tc_email_settings[ 'attendee_attach_ticket' ] ) ? true : false;
						$content = ( $enabled_attachment ) ? $templates->generate_preview( $order_attendee_id, false, false, false, $enabled_attachment ) : '';
		
						$placeholders = apply_filters( 'tc_order_completed_attendee_email_placeholders', $placeholders );
						$placeholder_values = apply_filters( 'tc_order_completed_attendee_email_placeholder_values', $placeholder_values, $order_attendee_id, $order_id );
						$message = str_replace( $placeholders, $placeholder_values, $message );
		
						if ( $email_send_type == 'wp_mail' ) {
		
							$attachment = array( $content );
							$_POST[ 'ticket_instance_id' ] = $order_attendee_id;
		
							// Override PHPMailer addAttachment method if attachment is not a physical file
							add_action( 'phpmailer_init', 'insert_string_attachment' );
		
							$message = apply_filters( 'tc_order_completed_attendee_email_message', wpautop( $message ) );
							$attendee_headers = apply_filters( 'tc_order_completed_attendee_email_headers', $attendee_headers );
		
							@wp_mail( sanitize_email( $owner_email ), sanitize_text_field( stripslashes( $subject ) ), tc_sanitize_string( stripcslashes( wpautop ( $message ) ) ), $attendee_headers, $attachment );
		
						} else {
		
							// Boundary
							$semi_rand = md5( time() );
							$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		
							// Header for sender info
							$headers = "From: " . attendee_email_from_email( '' ) . " <" . attendee_email_from_email( '' ) . ">\n" .
								'Reply-To: ' . attendee_email_from_email( '' ) . "\n" .
								'X-Mailer: PHP/' . phpversion();
		
							// Headers for attachment
							$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;" . " boundary=\"{$mime_boundary}\"";
		
							// Multipart boundary
							$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
								"Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
		
							// Attachment Content
							$message .= "--{$mime_boundary}\n";
							$message .= "Content-Type: application/octet-stream; name=\"" . $ticket_code . '.pdf' . "\"\n" .
								"Content-Description: " . $ticket_code . '.pdf' . "\n" .
								"Content-Disposition: attachment;" . " filename=\"" . $ticket_code . '.pdf' . "\";\n" .
								"Content-Transfer-Encoding: base64;\n\n" . base64_encode( $content ) . "\n\n";
							$message .= "--{$mime_boundary}--";
		
							@mail( sanitize_email( $owner_email ), sanitize_text_field( stripslashes( $subject ) ), stripcslashes( wpautop( $message ) ), $headers );
							
						}
						
					}
					update_post_meta( $order_attendee_id, 'tc_email_sent', 'Yes' );		
					$done++;
				} else {
					$email_sent = get_post_meta( $order_attendee_id, 'tc_email_sent', true );		
					if($email_sent=='Yes') {
						$done++;
					}
					
				}
				
			}
			
			if( count( $order_attendees ) == $done ) {
				update_post_meta( $order_id, 'tc_email_sent', 'Yes' );
			}
		}
	}
    
	/**
	 * Cron to send emails
	 */
	public function process_attendees_email() {
		
		global $wpdb;

		$rows = $wpdb->get_results( "select post_id from ".$wpdb->prefix."postmeta where meta_key = '_is_round_table' and meta_value = '1' " );
		$args = array(
			'limit' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			'return' => 'ids',
		);

		foreach( $rows as $row ) {
			$expired = get_post_meta( $row->post_id, '_round_table_expiry', true );
			if( !empty($expired)) {
				if( time() >= strtotime( $expired ) ) {
					
					$order_ids = $this->get_orders_id_from_product_id( $row->post_id, $args );
					foreach( $order_ids as $order_id ) {
						$this->tc_order_paid_attendee_email( $order_id, $row->post_id );
					}
				}
			}
		}

		exit;
	}
	
}


/**
 * @return bool
 */
function TC_Load() {

	return Tickera_Customization::instance();
}
add_action( 'plugins_loaded', 'TC_Load' );

remove_action( 'tc_wb_allowed_tickets_access', 'tc_maybe_send_order_paid_attendee_email' );

add_action( 'tc_wb_allowed_tickets_access', 'tc_maybe_send_order_paid_attendee_email_customized' );
function tc_maybe_send_order_paid_attendee_email_customized( $wc_order ) {
	
	global $tc;

	$user_id = get_current_user_id();
    $order_id = $wc_order->get_id();

	$tc_email_settings = get_option( 'tc_email_setting', false );
	$email_send_type = isset( $tc_email_settings[ 'email_send_type' ] ) ? $tc_email_settings[ 'email_send_type' ] : 'wp_mail';

	if ( isset( $tc_email_settings[ 'attendee_send_message' ] ) && 'yes' == $tc_email_settings[ 'attendee_send_message' ] ) {

		add_filter( 'wp_mail_content_type', 'set_content_type' );

		if ( ! function_exists( 'set_content_type' ) ) {

			function set_content_type( $content_type ) {
				return 'text/html';
			}
		}

		add_filter( 'wp_mail_from', 'attendee_email_from_email', 999 );
		add_filter( 'wp_mail_from_name', 'attendee_email_from_name', 999 );

		$subject = isset( $tc_email_settings[ 'attendee_order_subject' ] ) ? $tc_email_settings[ 'attendee_order_subject' ] : __( 'Your Ticket is here!', TC_TEXT_DOMAIN );
		$default_message = 'Hello, <br /><br />You can download ticket for EVENT_NAME here DOWNLOAD_URL';
		$order = new TC_Order( $order_id );

		$tc_attendee_order_message = isset( $tc_email_settings[ 'attendee_order_message' ] ) ? $tc_email_settings[ 'attendee_order_message' ] : '';
		$tc_attendee_order_message = apply_filters( 'tc_attendee_order_message', $tc_attendee_order_message, $order );

		$attendee_headers = '';
		$order_attendees = TC_Orders::get_tickets_ids( $order->details->ID );

		foreach ( $order_attendees as $order_attendee_id ) {

			$ticket_meta = get_post_meta( $order_attendee_id );
			$ticket_type_id = isset( $ticket_meta[ 'ticket_type_id' ] ) ? reset( $ticket_meta[ 'ticket_type_id' ] ) : '';

			$is_round_table = get_post_meta( $ticket_type_id, '_is_round_table', true );
			if( intval($is_round_table) != 1 ) {

				$ticket_type_name = get_the_title( $ticket_type_id );

				$ticket_code = isset( $ticket_meta[ 'ticket_code' ] ) ? reset( $ticket_meta[ 'ticket_code' ] ) : '';
				$ticket_code = strtoupper( $ticket_code );

				$event_id = isset( $ticket_meta[ 'event_id' ] ) ? reset( $ticket_meta[ 'event_id' ] ) : '';
				$event_id = (int) $event_id;

				$first_name = isset( $ticket_meta[ 'first_name' ] ) ? reset( $ticket_meta[ 'first_name' ] ) : '';
				$last_name = isset( $ticket_meta[ 'last_name' ] ) ? reset( $ticket_meta[ 'last_name' ] ) : '';
				$owner_email = isset( $ticket_meta[ 'owner_email' ] ) ? reset( $ticket_meta[ 'owner_email' ] ) : '';

				$event = new TC_Event( $event_id );
				$event_location = get_post_meta( $event_id, 'event_location', true );

				$message = isset( $tc_attendee_order_message ) ? $tc_attendee_order_message : $default_message;
				$placeholders = array( 'EVENT_NAME', 'DOWNLOAD_URL', 'TICKET_TYPE', 'TICKET_CODE','FIRST_NAME', 'LAST_NAME', 'EVENT_LOCATION' );
				$placeholder_values = array( $event->details->post_title, tc_get_ticket_download_link( '', '', $order_attendee_id, true ), $ticket_type_name, $ticket_code,$first_name, $last_name, $event_location );

				if ( ! empty( $owner_email ) ) {

					// Generate pdf file
					$templates = new TC_Ticket_Templates();
					$enabled_attachment = ( isset( $tc_email_settings[ 'attendee_attach_ticket' ] ) && 'yes' == $tc_email_settings[ 'attendee_attach_ticket' ] ) ? true : false;
					$content = ( $enabled_attachment ) ? $templates->generate_preview( $order_attendee_id, false, false, false, $enabled_attachment ) : '';

					$placeholders = apply_filters( 'tc_order_completed_attendee_email_placeholders', $placeholders );
					$placeholder_values = apply_filters( 'tc_order_completed_attendee_email_placeholder_values', $placeholder_values, $order_attendee_id, $order_id );
					$message = str_replace( $placeholders, $placeholder_values, $message );

					if ( $email_send_type == 'wp_mail' ) {

						$attachment = array( $content );
						$_POST[ 'ticket_instance_id' ] = $order_attendee_id;

						// Override PHPMailer addAttachment method if attachment is not a physical file
						add_action( 'phpmailer_init', 'insert_string_attachment' );

						$message = apply_filters( 'tc_order_completed_attendee_email_message', wpautop( $message ) );
						$attendee_headers = apply_filters( 'tc_order_completed_attendee_email_headers', $attendee_headers );

						@wp_mail( sanitize_email( $owner_email ), sanitize_text_field( stripslashes( $subject ) ), tc_sanitize_string( stripcslashes( wpautop ( $message ) ) ), $attendee_headers, $attachment );

					} else {

						// Boundary
						$semi_rand = md5( time() );
						$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

						// Header for sender info
						$headers = "From: " . attendee_email_from_email( '' ) . " <" . attendee_email_from_email( '' ) . ">\n" .
							'Reply-To: ' . attendee_email_from_email( '' ) . "\n" .
							'X-Mailer: PHP/' . phpversion();

						// Headers for attachment
						$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;" . " boundary=\"{$mime_boundary}\"";

						// Multipart boundary
						$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
							"Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

						// Attachment Content
						$message .= "--{$mime_boundary}\n";
						$message .= "Content-Type: application/octet-stream; name=\"" . $ticket_code . '.pdf' . "\"\n" .
							"Content-Description: " . $ticket_code . '.pdf' . "\n" .
							"Content-Disposition: attachment;" . " filename=\"" . $ticket_code . '.pdf' . "\";\n" .
							"Content-Transfer-Encoding: base64;\n\n" . base64_encode( $content ) . "\n\n";
						$message .= "--{$mime_boundary}--";

						@mail( sanitize_email( $owner_email ), sanitize_text_field( stripslashes( $subject ) ), stripcslashes( wpautop( $message ) ), $headers );
					}
				}
			}
		}
	} 
}