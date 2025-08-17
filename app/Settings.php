<?php
/**
 * All settings related functions
 */
namespace Worzen\Suffix_Master\App;
use Worzen\Suffix_Master\Helper;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Settings as Settings_API;

/**
 * @package Plugin
 * @subpackage Settings
 * @author Worzen<hello@worzen.com>
 */
class Settings extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}
	
	public function init_menu() {
		
		$site_config = [
			'PHP Version'				=> PHP_VERSION,
			'WordPress Version' 		=> get_bloginfo( 'version' ),
			'WooCommerce Version'		=> is_plugin_active( 'woocommerce/woocommerce.php' ) ? get_option( 'woocommerce_version' ) : 'Not Active',
			'Memory Limit'				=> defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT ? WP_MEMORY_LIMIT : 'Not Defined',
			'Debug Mode'				=> defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled',
			'Active Plugins'			=> get_option( 'active_plugins' ),
		];

		$settings = [
			'id'            => $this->slug,
			'label'         => __( 'Suffix Master', 'suffix-master' ),
			'title'         => "{$this->name} v{$this->version}",
			'header'        => $this->name,
			'parent'        => '',
			'capability'    => 'manage_options',
			'icon'          => 'dashicons-admin-generic',
			'sections'      => [
				'suffix_master_global'	=> [
					'id'        => 'suffix_master_global',
					'label'     => __( 'Global Suffix Settings', 'suffix-master' ),
					'icon'      => 'dashicons-admin-tools',
					'sticky'	=> false,
					'fields'    => [
						'global_title_suffix' => [
							'id'        => 'global_title_suffix',
							'label'     => __( 'Global Title Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be applied to all post and page titles when specific suffixes are not set. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., - {year}', 'suffix-master' ),
						],
						'post_title_suffix' => [
							'id'        => 'post_title_suffix',
							'label'     => __( 'Post Title Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be applied specifically to blog post titles. Overrides global title suffix. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., - Blog {year}', 'suffix-master' ),
						],
						'page_title_suffix' => [
							'id'        => 'page_title_suffix',
							'label'     => __( 'Page Title Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be applied specifically to page titles. Overrides global title suffix. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., - Page {year}', 'suffix-master' ),
						],
						'global_slug_suffix' => [
							'id'        => 'global_slug_suffix',
							'label'     => __( 'Global Slug Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be automatically appended to slugs of new posts and pages. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., {year}', 'suffix-master' ),
						],
						'content_footer_suffix' => [
							'id'        => 'content_footer_suffix',
							'label'     => __( 'Content Footer Suffix', 'suffix-master' ),
							'type'      => 'wysiwyg',
							'desc'      => __( 'This content will be appended to the end of all post and page content. HTML is allowed. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'width'     => '100%',
							'rows'      => 5,
							'teeny'     => true,
							'text_mode' => false,
							'media_buttons' => false,
						],

					]
				],
				'suffix_master_woocommerce'	=> [
					'id'        => 'suffix_master_woocommerce',
					'label'     => __( 'WooCommerce Settings', 'suffix-master' ),
					'icon'      => 'dashicons-cart',
					'sticky'	=> false,
					'fields'    => [
						'wc_product_title_suffix' => [
							'id'        => 'wc_product_title_suffix',
							'label'     => __( 'Product Title Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be applied specifically to WooCommerce product titles. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., - {year} Edition', 'suffix-master' ),
						],
						'global_price_suffix' => [
							'id'        => 'global_price_suffix',
							'label'     => __( 'Global Price Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be applied to all WooCommerce product prices. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., (incl. tax)', 'suffix-master' ),
						],
						'wc_slug_suffix' => [
							'id'        => 'wc_slug_suffix',
							'label'     => __( 'Product Slug Suffix', 'suffix-master' ),
							'type'      => 'text',
							'desc'      => __( 'This suffix will be automatically appended to slugs of new WooCommerce products. Use placeholders: {year}, {site_name}', 'suffix-master' ),
							'default'   => '',
							'placeholder' => __( 'e.g., {year}', 'suffix-master' ),
						],

					]
				],
				'suffix_master_tools'	=> [
					'id'        => 'suffix_master_tools',
					'label'     => __( 'Tools & Actions', 'suffix-master' ),
					'icon'      => 'dashicons-hammer',
					'sticky'	=> false,
					'fields'    => [
						'live_preview' => [
							'id'        => 'live_preview',
							'label'     => __( 'Live Preview', 'suffix-master' ),
							'type'      => 'switch',
							'desc'      => __( 'Enable live preview to see suffix changes in real-time as you type.', 'suffix-master' ),
							'default'   => 'on',
						],
						'reset_all_button' => [
							'id'        => 'reset_all_button',
							'label'     => __( 'Reset All Settings', 'suffix-master' ),
							'type'      => 'html',
							'desc'      => __( 'This will clear all suffix settings and restore defaults. This action cannot be undone.', 'suffix-master' ),
							'html'      => '<button type="button" id="suffix-master-reset-all" class="button button-secondary" style="color: #d63638; border-color: #d63638;">' . __( 'Reset All Settings', 'suffix-master' ) . '</button>',
						],
						'report' => [
							'id'        => 'report',
							'label'     => __( 'System Report', 'suffix-master' ),
							'type'      => 'textarea',
							'desc'      => '<button id="suffix-master-report-copy" class="button button-primary"><span class="dashicons dashicons-admin-page"></span> ' . __( 'Copy Report', 'suffix-master' ) . '</button>',
							'columns'   => 24,
							'rows'      => 10,
							'default'   => json_encode( $site_config, JSON_PRETTY_PRINT ),
							'readonly'  => true,
						],
					]
				],
			],
		];

		// Add validation callback
		add_filter( 'pre_update_option_suffix-master', [ $this, 'validate_settings' ], 10, 2 );

		new Settings_API( $settings );
	}

	/**
	 * Validate settings before saving
	 *
	 * @param array $new_value New settings value
	 * @param array $old_value Old settings value
	 * @return array Validated settings
	 */
	public function validate_settings( $new_value, $old_value ) {
		if ( ! \Worzen\Suffix_Master\Validator::user_can_manage_settings() ) {
			\Worzen\Suffix_Master\Validator::log_security_event( 'Unauthorized settings save attempt' );
			return $old_value;
		}

		return \Worzen\Suffix_Master\Validator::validate_settings( $new_value );
	}
}