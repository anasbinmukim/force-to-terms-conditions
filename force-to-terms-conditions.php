<?php
/*
Plugin Name: Force To Terms & Conditions
Plugin URI: http://plugins.rmweblab.com/
Description: Force To Updated Terms & Conditions plugin work for logged in user. So user will redirect to terms and conditions page automatically or display updated term notice at top/bottom if current user have new/updated terms and conditions content.
Author: Anas
Version: 1.0
Author URI: http://rmweblab.com
Copyright: © 2017 RMWebLab.
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: force-to-tnc
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Profile
require_once( 'force-to-tnc-user-profile.php' );

/**
 * Main ForceToUpdatedTnC clas set up for us
 */
class ForceToUpdatedTnC {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'FTTNC_VERSION', '1.0' );
		define( 'FTTNC_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'FTTNC_MAIN_FILE', __FILE__ );

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
    add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
    add_action( 'admin_menu', array( $this, 'admin_settings_menu' ) );
    add_action( 'save_post', array( $this, 'save_latest_updated_time' ), 11, 3 );
		add_action( 'wp', array( $this, 'process_redirect' ) );
		add_action( 'init', array( $this, 'process_agree_link' ) );
		add_action( 'wp_footer', array( $this, 'display_forcettnc_notice' ) );
		add_action( 'wp_head', array( $this, 'add_script_forcettnc_head' ) );
		add_action( 'manage_users_columns', array( $this, 'fttnc_modify_user_columns' ) );
		add_action( 'manage_users_custom_column', array( $this, 'fttnc_user_column_content' ), 10, 3 );

	}

	/**
	 * Init localisations and hook
	 */
	public function init() {
		// Includes
		//require_once( 'inc/class-wc-gateway-invoiceme.php' );

		// Localisation
		load_plugin_textdomain( 'force-to-tnc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'options-general.php?page=fttnc-settings' ) . '">' . __( 'Settings', 'force-to-tnc' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}


  /**
	 * Add admin settings menu
	 */
	public function admin_settings_menu() {
    add_options_page('Force to Terms and Conditins', 'Force Terms', 'manage_options', 'fttnc-settings', array(	$this,	'fttnc_settings_page'));
	}

  public function fttnc_settings_page(){
    // Admin Seettings page
		require_once( 'force-to-tnc-admin.php' );

  }


	public function add_script_forcettnc_head(){
		// Frontend settings
		if($this->is_force()){
			require_once( 'script_frontend.php' );
		}
	}

	public function fttnc_modify_user_columns($column_headers) {
		$column_headers['agree_fttnc'] = 'Terms Agree';
		return $column_headers;
	}

	public function fttnc_user_column_content($value, $column_name, $user_id) {
		$user = get_userdata( $user_id );
		if ( 'agree_fttnc' == $column_name ) {
			$redirect_exclude = get_the_author_meta( 'fttnc_exclude_user', $user_id );
			if($redirect_exclude == 1){
				return 'Always Agreed.';
			}elseif(!$this->is_force($user_id)){
				return 'Yes, I agree.';
			}

		}
		return $value;
	}



  public function save_latest_updated_time($post_id, $post, $update){
    if ( wp_is_post_revision( $post_id ) ){
		  return;
    }

    //set tnc last updated time
    $selected_page_id = get_option('fttnc_page_id', true);
    if($selected_page_id == $post_id){
        $current_time = current_time( 'mysql' );
        update_option('fttnc_last_update', $current_time);
    }

  }


	/**
	 * Process I agree when user click I agree button, link or checkbox
	 */
	public function process_redirect(){
			$display_type = get_option('termnc_display_type');
			if ($this->is_force() && ($display_type == 'redirect')) {
					global $post;
					$current_page_id = get_the_ID();
					$term_page_id = get_option('fttnc_page_id', true);
					if(($current_page_id != $term_page_id)){
						$url_redierct = get_permalink($term_page_id);
						wp_redirect( $url_redierct );
						exit;
					}
			}
	}

	/**
	 * Display notice box to top or bottom
	 */
	public function display_forcettnc_notice(){
			$display_type = get_option('termnc_display_type');
			if($this->is_force() && (($display_type == 'display_top') || ($display_type == 'display_bottom'))){
				echo '<div id="force_to_term_notice_box" class="force_totnc_notice '.$display_type.'">';
					echo '<div class="force_totnc_notice_inner">';
						$notice_info = esc_textarea(get_option('termnc_notice_info'));
						$notice_info = apply_filters('the_content', $notice_info);
						echo do_shortcode($notice_info);
					echo '</div>';
				echo '</div>';
			}
	}

	/**
	 * Check force to tnc or not for current visitor or folks
	 * Return true if redirect enable False otherwise
	 */
	public function is_force($user_id = ''){
		if($user_id == ''){
			$user_id = get_current_user_id();
		}

		global $post;
		$is_force = FALSE;
		//Check logg users
		if( is_user_logged_in() && ( is_page() || is_single() || is_singular('product'))){
			$is_force = TRUE;
		}

		//Exclude always agree user
		if( is_user_logged_in() ){
				$ulast_agree_date = get_user_meta( $user_id, 'ulast_agree_date', TRUE );
				$updated_agree_date = get_option('fttnc_last_update', true);
				if(($ulast_agree_date != $updated_agree_date)){
						$is_force = TRUE;
				}else{
						$is_force = FALSE;
				}

				$redirect_exclude = get_the_author_meta( 'fttnc_exclude_user', $user_id );
				if($redirect_exclude == 1){
					$is_force = FALSE;
				}
		}

		return $is_force;
	}

	//Process agree when click agree link generate by agree link shortcode
	public function process_agree_link(){
		if(isset($_GET['fttncaction']) && ($_GET['fttncaction'] == 'yesiagree')){
				$nonce = $_REQUEST['agree_nonce'];
				if ( ! wp_verify_nonce( $nonce, 'process_agree' ) ) {
					//Nothing to do
				} else {
					$user_id = get_current_user_id();
					$updated_agree_date = get_option('fttnc_last_update', true);
					update_user_meta($user_id, 'ulast_agree_date', $updated_agree_date);
				}
		}
	}

	public static function fttnc_agree_shortcode_func( $atts, $content = "" ) {
		extract(shortcode_atts(array(
			'checkbox_label' => 'I agree with this terms and conditions',
			'button_label' => 'Yes, I agree',
			'agree_redirect' => '',
			'agreed_ok_message' => 'Thank you',
			'agreed_error_message' => 'Please checked terms',
			'non_loged_message' => '<a href="/login/">Login</a> to agree this term.',
		), $atts));

		if( !is_user_logged_in() ){
			$result_data = $non_loged_message;
			return $result_data;
		}

		$result_data = '';
		$result_data .= '<div class="fttnc_form_wrap">';
		if ( ! isset( $_POST['name_fttnc_agree_nonce'] ) || ! wp_verify_nonce( $_POST['name_fttnc_agree_nonce'], 'fttnc_agree_action' )) {
			//Verifiy not match..
		} else {
			if(isset($_POST['fttnc_agree_submit']) && (isset($_POST['fttnc_agree_checked']) && ($_POST['fttnc_agree_checked'] == 'Yes'))){
					$user_id = get_current_user_id();
					$ulast_agree_date = get_user_meta( $user_id, 'ulast_agree_date', TRUE );
					$last_updated_time = sanitize_text_field($_POST['last_updated_time']);
					update_user_meta($user_id, 'ulast_agree_date', $last_updated_time);
					if($agree_redirect != ''){
						echo '<script type="text/javascript">window.location = "'.$agree_redirect.'"</script>';
						exit;
					}else{
						$result_data .= '<div class="agreed_ok_message">';
						$result_data .= $agreed_ok_message;
						$result_data .= '</div></div><!-- rttnc_form_wrap -->';
						return $result_data;
					}
			}else{
				$result_data .= '<div class="agreed_error_message">';
				$result_data .= $agreed_error_message;
				$result_data .= '</div></div><!-- agreed_error_message -->';
			}
		}

		$result_data .= '<form class="rttnc_form" action="" method="post">';
		$result_data .= wp_nonce_field( 'fttnc_agree_action', 'name_fttnc_agree_nonce', '', false );
		$result_data .= '<input type="hidden" name="last_updated_time" value="'.get_option('fttnc_last_update', true).'">';
		$result_data .= '<div class="agree_check_wrap"><input type="checkbox" name="fttnc_agree_checked" id="fttnc_agree_checked" value="Yes"> <label for="fttnc_agree_checked">'.$checkbox_label.'</label></div>';
		$result_data .= '<div class="rttnc_submit"><input type="submit" name="fttnc_agree_submit" id="fttnc_agree_submit" class="button button-primary" value="'.$button_label.'"></div>';
		$result_data .= '</form>';
		$result_data .= '</div>';
		return $result_data;
	}

	public static function fttnc_agree_link_shortcode_func( $atts, $content = "" ) {
		extract(shortcode_atts(array(
			'link_label' => 'Yes, I agree',
		), $atts));

		global $post;
		$current_page_url = get_permalink($post->ID);
		$current_page_url = add_query_arg( array('fttncaction' => 'yesiagree'), $current_page_url);

		$result_data = '';
		$result_data .= '<a href="'.wp_nonce_url($current_page_url, 'process_agree', 'agree_nonce').'">'.$link_label.'</a>';
		return $result_data;
	}

}

new ForceToUpdatedTnC();

add_shortcode( 'fttnc-agree', array( 'ForceToUpdatedTnC', 'fttnc_agree_shortcode_func' ) );
add_shortcode( 'fttnc-agree-link', array( 'ForceToUpdatedTnC', 'fttnc_agree_link_shortcode_func' ) );
