<?php
/**
 * All AJAX related functions
 */
namespace Worzen\Suffix_Master\App;
use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage AJAX
 * @author Worzen<hello@worzen.com>
 */
class AJAX extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];

		// Register AJAX actions
		$this->init_ajax_actions();
	}

	/**
	 * Initialize AJAX actions
	 */
	private function init_ajax_actions() {
		add_action( 'wp_ajax_suffix_master_reset_all', [ $this, 'reset_all_settings' ] );
	}

	/**
	 * Reset all suffix master settings
	 */
	public function reset_all_settings() {
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'suffix_master_ajax' ) ) {
			\Worzen\Suffix_Master\Validator::log_security_event( 'Invalid nonce for reset all settings' );
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check user capabilities
		if ( ! \Worzen\Suffix_Master\Validator::user_can_manage_settings() ) {
			\Worzen\Suffix_Master\Validator::log_security_event( 'Unauthorized reset all settings attempt' );
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Log the action
		\Worzen\Suffix_Master\Validator::log_security_event( 'Settings reset performed' );

		// Delete all plugin options (including the new sectioned options)
		delete_option( 'suffix-master' );
		delete_option( 'suffix_master_global' );
		delete_option( 'suffix_master_woocommerce' );
		delete_option( 'suffix_master_tools' );

		// Send success response
		wp_send_json_success( 'All settings have been reset successfully.' );
	}

}