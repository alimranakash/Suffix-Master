<?php
/**
 * Plugin Name: Suffix Master – Smart Title, Price & Content Branding
 * Description: Plugin for Client
 * Plugin URI:  https://wpplugines.com/
 * Author:      Al Imran Akash
 * Author URI:  https://profiles.wordpress.org/al-imran-akash/
 * Version: 	0.9
 * Text Domain: suffix-master
 * Domain Path: /languages
 */

namespace Worzen\Suffix_Master;
use Codexpert\Plugin\Notice;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Worzen<hello@worzen.com>
 */
final class Plugin {
	
	/**
	 * Plugin instance
	 * 
	 * @access private
	 * 
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * The constructor method
	 * 
	 * @access private
	 * 
	 * @since 0.9
	 */
	private function __construct() {
		/**
		 * Includes required files
		 */
		$this->include();

		/**
		 * Defines contants
		 */
		$this->define();

		/**
		 * Runs actual hooks
		 */
		$this->hook();
	}

	/**
	 * Includes files
	 * 
	 * @access private
	 * 
	 * @uses composer
	 * @uses psr-4
	 */
	private function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 * 
	 * @access private
	 * 
	 * @uses get_plugin_data
	 * @uses plugin_basename
	 */
	private function define() {

		/**
		 * Define some constants
		 * 
		 * @since 0.9
		 */
		define( 'SUFFIXMASTER', __FILE__ );
		define( 'SUFFIXMASTER_DIR', dirname( SUFFIXMASTER ) );
		define( 'SUFFIXMASTER_ASSET', plugins_url( 'assets', SUFFIXMASTER ) );
		define( 'SUFFIXMASTER_DEBUG', apply_filters( 'plugin-client_debug', true ) );

		/**
		 * The plugin data
		 * 
		 * @since 0.9
		 * @var $plugin
		 */
		$this->plugin					= get_plugin_data( SUFFIXMASTER );
		$this->plugin['basename']		= plugin_basename( SUFFIXMASTER );
		$this->plugin['file']			= SUFFIXMASTER;
		$this->plugin['server']			= apply_filters( 'plugin-client_server', 'https://worzen.com/dashboard' );
		$this->plugin['min_php']		= '5.6';
		$this->plugin['min_wp']			= '4.0';
		$this->plugin['icon']			= SUFFIXMASTER_ASSET . '/img/icon.png';
		$this->plugin['depends']		= [ 'woocommerce/woocommerce.php' => 'WooCommerce' ];
		
	}

	/**
	 * Hooks
	 * 
	 * @access private
	 * 
	 * Executes main plugin features
	 *
	 * To add an action, use $instance->action()
	 * To apply a filter, use $instance->filter()
	 * To register a shortcode, use $instance->register()
	 * To add a hook for logged in users, use $instance->priv()
	 * To add a hook for non-logged in users, use $instance->nopriv()
	 * 
	 * @return void
	 */
	private function hook() {

		if( is_admin() ) :

			/**
			 * Admin facing hooks
			 */
			$admin = new App\Admin( $this->plugin );
			$admin->activate( 'install' );
			$admin->action( 'admin_footer', 'modal' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->action( 'admin_footer_text', 'footer_text' );

			// Suffix Master admin hooks
			$admin->action( 'add_meta_boxes', 'add_suffix_metaboxes' );
			$admin->action( 'save_post', 'save_suffix_metabox' );

			/**
			 * Settings related hooks
			 */
			$settings = new App\Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

			/**
			 * Renders different notices
			 * 
			 * @package Codexpert\Plugin
			 * 
			 * @author Worzen<hello@worzen.com>
			 */
			$notice = new Notice( $this->plugin );

		else : // ! is_admin() ?

			/**
			 * Front facing hooks
			 */
			$front = new App\Front( $this->plugin );
			$front->action( 'wp_head', 'head' );
			$front->action( 'wp_footer', 'modal' );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );

			// Suffix Master frontend hooks
			$front->filter( 'the_title', 'apply_title_suffix', 20, 2 );
			$front->filter( 'the_content', 'apply_content_suffix', 20 );
			$front->filter( 'wp_unique_post_slug', 'apply_slug_suffix', 10, 6 );
			$front->action( 'save_post', 'apply_slug_suffix_on_save', 10 );
			$front->action( 'transition_post_status', 'apply_slug_suffix_on_transition', 10, 3 );

			/**
			 * Shortcode related hooks
			 */
			$shortcode = new App\Shortcode( $this->plugin );
			$shortcode->register( 'my_shortcode', 'my_shortcode' );

		endif;

		// WooCommerce functionality - separate class
		$woocommerce = new App\Woocommerce( $this->plugin );
		$woocommerce->action( 'woocommerce_loaded', 'init_woocommerce_hooks' );
		$woocommerce->action( 'init', 'init_woocommerce_hooks', 20 );
		$woocommerce->action( 'plugins_loaded', 'init_woocommerce_hooks', 25 );

		/**
		 * Cron facing hooks
		 */
		$cron = new App\Cron( $this->plugin );
		$cron->activate( 'install' );
		$cron->deactivate( 'uninstall' );

		/**
		 * Common hooks
		 *
		 * Executes on both the admin area and front area
		 */
		$common = new App\Common( $this->plugin );

		/**
		 * AJAX related hooks
		 */
		$ajax = new App\AJAX( $this->plugin );
	}

	/**
	 * Cloning is forbidden.
	 * 
	 * @access public
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 * 
	 * @access public
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 * 
	 * @access public
	 * 
	 * @return $_instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

// Initialize plugin
Plugin::instance();

// Activation hook
register_activation_hook( __FILE__, function() {
	// Flush rewrite rules to ensure proper URL handling
	flush_rewrite_rules();

	// Set default options if they don't exist
	if ( ! get_option( 'suffix-master' ) ) {
		add_option( 'suffix-master', [] );
	}
} );