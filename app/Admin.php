<?php
/**
 * All admin facing functions
 */
namespace Worzen\Suffix_Master\App;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Metabox;
use Worzen\Suffix_Master\Helper;
use Worzen\Suffix_Master\Validator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Worzen<hello@worzen.com>
 */
class Admin extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->server	= $this->plugin['server'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'suffix-master', false, SUFFIXMASTER_DIR . '/languages/' );
	}

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		if( ! get_option( 'suffix_master_version' ) ){
			update_option( 'suffix_master_version', $this->version );
		}

		if( ! get_option( 'suffix_master_install_time' ) ){
			update_option( 'suffix_master_install_time', time() );
		}
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'SUFFIXMASTER_DEBUG' ) && SUFFIXMASTER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", SUFFIXMASTER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", SUFFIXMASTER ), [ 'jquery' ], $this->version, true );

		// Localize script for AJAX and other data
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce( 'suffix_master_ajax' ),
			'site_name'	=> get_bloginfo( 'name' ),
		];
		wp_localize_script( $this->slug, 'SUFFIXMASTER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		// Only show custom footer text on plugin pages, without external links
		return sprintf( esc_html__( 'Thank you for using %s!', 'suffix-master' ), esc_html( $this->name ) );
	}

	public function modal() {
		echo '
		<div id="suffix-master-modal" style="display: none">
			<img id="suffix-master-modal-loader" src="' . esc_attr( SUFFIXMASTER_ASSET . '/img/loader.gif' ) . '" />
		</div>';
	}

	/**
	 * Add suffix metaboxes to post edit screens
	 */
	public function add_suffix_metaboxes() {
		$post_types = [ 'post', 'page' ];

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'suffix_master_metabox',
				__( 'Suffix Master Settings', 'suffix-master' ),
				[ $this, 'render_suffix_metabox' ],
				$post_type,
				'side',
				'default'
			);
		}

		// Add metabox for WooCommerce products if WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			add_meta_box(
				'suffix_master_product_metabox',
				__( 'Suffix Master Settings', 'suffix-master' ),
				[ $this, 'render_product_suffix_metabox' ],
				'product',
				'side',
				'default'
			);
		}
	}

	/**
	 * Render suffix metabox for posts and pages
	 *
	 * @param object $post Post object
	 */
	public function render_suffix_metabox( $post ) {
		wp_nonce_field( 'suffix_master_metabox', 'suffix_master_metabox_nonce' );

		$title_suffix = get_post_meta( $post->ID, '_suffix_master_title', true );
		$content_suffix = get_post_meta( $post->ID, '_suffix_master_content', true );

		echo '<div class="suffix-master-metabox">';
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<td><label for="suffix_master_title">' . esc_html__( 'Title Suffix:', 'suffix-master' ) . '</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><input type="text" id="suffix_master_title" name="suffix_master_title" value="' . esc_attr( $title_suffix ) . '" style="width: 100%;" placeholder="' . esc_attr__( 'Override global title suffix', 'suffix-master' ) . '" /></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><label for="suffix_master_content">' . esc_html__( 'Content Footer:', 'suffix-master' ) . '</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><textarea id="suffix_master_content" name="suffix_master_content" rows="3" style="width: 100%;" placeholder="' . esc_attr__( 'Override global content footer', 'suffix-master' ) . '">' . esc_textarea( $content_suffix ) . '</textarea></td>';
		echo '</tr>';
		echo '</table>';

		echo '<p><small>' . esc_html__( 'Available placeholders: {year}, {site_name}', 'suffix-master' ) . '</small></p>';
		echo '</div>';
	}

	/**
	 * Render suffix metabox for WooCommerce products
	 *
	 * @param object $post Post object
	 */
	public function render_product_suffix_metabox( $post ) {
		wp_nonce_field( 'suffix_master_product_metabox', 'suffix_master_product_metabox_nonce' );

		$title_suffix = get_post_meta( $post->ID, '_suffix_master_product_title', true );
		$price_suffix = get_post_meta( $post->ID, '_suffix_master_price', true );
		$content_suffix = get_post_meta( $post->ID, '_suffix_master_content', true );

		echo '<div class="suffix-master-metabox">';
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<td><label for="suffix_master_product_title">' . esc_html__( 'Product Title Suffix:', 'suffix-master' ) . '</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><input type="text" id="suffix_master_product_title" name="suffix_master_product_title" value="' . esc_attr( $title_suffix ) . '" style="width: 100%;" placeholder="' . esc_attr__( 'Override global product title suffix', 'suffix-master' ) . '" /></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><label for="suffix_master_price">' . esc_html__( 'Price Suffix:', 'suffix-master' ) . '</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><input type="text" id="suffix_master_price" name="suffix_master_price" value="' . esc_attr( $price_suffix ) . '" style="width: 100%;" placeholder="' . esc_attr__( 'Override global price suffix', 'suffix-master' ) . '" /></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><label for="suffix_master_content">' . esc_html__( 'Content Footer:', 'suffix-master' ) . '</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><textarea id="suffix_master_content" name="suffix_master_content" rows="3" style="width: 100%;" placeholder="' . esc_attr__( 'Override global content footer', 'suffix-master' ) . '">' . esc_textarea( $content_suffix ) . '</textarea></td>';
		echo '</tr>';
		echo '</table>';

		echo '<p><small>' . esc_html__( 'Available placeholders: {year}, {site_name}', 'suffix-master' ) . '</small></p>';
		echo '</div>';
	}

	/**
	 * Save suffix metabox data
	 *
	 * @param int $post_id Post ID
	 */
	public function save_suffix_metabox( $post_id ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			Validator::log_security_event( 'Unauthorized metabox save attempt', [ 'post_id' => $post_id ] );
			return;
		}

		$post_type = get_post_type( $post_id );

		// Handle regular posts and pages
		if ( in_array( $post_type, [ 'post', 'page' ] ) ) {
			if ( ! isset( $_POST['suffix_master_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['suffix_master_metabox_nonce'] ) ), 'suffix_master_metabox' ) ) {
				Validator::log_security_event( 'Invalid nonce for post metabox', [ 'post_id' => $post_id ] );
				return;
			}

			if ( isset( $_POST['suffix_master_title'] ) ) {
				$sanitized_title = Validator::sanitize_post_meta( $_POST['suffix_master_title'], '_suffix_master_title' );
				update_post_meta( $post_id, '_suffix_master_title', $sanitized_title );
			}

			if ( isset( $_POST['suffix_master_content'] ) ) {
				$sanitized_content = Validator::sanitize_post_meta( $_POST['suffix_master_content'], '_suffix_master_content' );
				update_post_meta( $post_id, '_suffix_master_content', $sanitized_content );
			}
		}

		// Handle WooCommerce products
		if ( $post_type === 'product' ) {
			if ( ! isset( $_POST['suffix_master_product_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['suffix_master_product_metabox_nonce'] ) ), 'suffix_master_product_metabox' ) ) {
				Validator::log_security_event( 'Invalid nonce for product metabox', [ 'post_id' => $post_id ] );
				return;
			}

			if ( isset( $_POST['suffix_master_product_title'] ) ) {
				$sanitized_title = Validator::sanitize_post_meta( $_POST['suffix_master_product_title'], '_suffix_master_product_title' );
				update_post_meta( $post_id, '_suffix_master_product_title', $sanitized_title );
			}

			if ( isset( $_POST['suffix_master_price'] ) ) {
				$sanitized_price = Validator::sanitize_post_meta( $_POST['suffix_master_price'], '_suffix_master_price' );
				update_post_meta( $post_id, '_suffix_master_price', $sanitized_price );
			}

			if ( isset( $_POST['suffix_master_content'] ) ) {
				$sanitized_content = Validator::sanitize_post_meta( $_POST['suffix_master_content'], '_suffix_master_content' );
				update_post_meta( $post_id, '_suffix_master_content', $sanitized_content );
			}
		}
	}
}