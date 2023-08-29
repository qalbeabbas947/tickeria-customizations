<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Tickera_Customization_Settings
 */
class Tickera_Customization_Template_pages {

	public $page_tab;
    function __construct() {
		$this->page_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		add_filter('template_include',array($this,'template_pages'));
		//add_action( 'admin_enqueue_scripts', [ $this, 'tc_add_admin_scripts' ] );
    }
	
	function template_pages($page_template)
	{
		global $post;
		
		$tc_roundtable_main_page 	= get_option( 'tc_roundtable_main_page' );
		$tc_roundtable_sub_page 	= get_option( 'tc_roundtable_sub_page' );
		$tc_roundtable_form_page	= get_option( 'tc_roundtable_form_page' );
		if($post->ID == $tc_roundtable_main_page ){
			$page_template = dirname( __FILE__ ) . '/views/main_page.php'; 
		}
		
		if($post->ID == $tc_roundtable_sub_page ){
			$page_template = dirname( __FILE__ ) . '/views/sub-pages.php'; 
		}
		
		if($post->ID == $tc_roundtable_form_page ){
		
			$page_template = dirname( __FILE__ ) . '/views/form-page.php'; 
		}
		
		return $page_template;
	}
    
    
}

new Tickera_Customization_Template_pages();