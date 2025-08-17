<?php
/**
 * All WooCommerce related functions
 */
namespace Worzen\Suffix_Master\App;
use Codexpert\Plugin\Base;
use Worzen\Suffix_Master\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Woocommerce
 * @author Worzen<hello@worzen.com>
 */
class Woocommerce extends Base {

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

	/**
	 * Initialize WooCommerce-specific hooks
	 */
	public function init_woocommerce_hooks() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Prevent duplicate hook registration
		static $hooks_registered = false;
		if ( $hooks_registered ) {
			return;
		}
		$hooks_registered = true;

		// Product title hooks - comprehensive coverage
		$this->filter( 'woocommerce_product_get_name', 'apply_product_title_suffix', 20, 2 );
		$this->filter( 'woocommerce_product_get_title', 'apply_product_title_suffix', 20, 2 );
		$this->filter( 'the_title', 'apply_product_title_in_loop', 25, 2 );
		$this->filter( 'woocommerce_product_title', 'apply_product_title_suffix_simple', 20, 2 );
		
		// Additional WooCommerce product title hooks for better coverage
		$this->filter( 'single_product_title', 'apply_product_title_suffix_simple', 20, 2 );
		$this->filter( 'woocommerce_single_product_title', 'apply_product_title_suffix_simple', 20, 2 );
		
		// Price suffix hooks - multiple hooks for better coverage
		$this->filter( 'woocommerce_get_price_html', 'apply_price_suffix', 20, 2 );
		$this->filter( 'woocommerce_price_html', 'apply_price_suffix', 20, 2 );

		// WooCommerce-specific slug suffix hooks
		$this->action( 'save_post', 'apply_woocommerce_slug_suffix', 15 );
		$this->action( 'transition_post_status', 'apply_wc_slug_on_transition', 10, 3 );

		// Hook into WordPress slug generation for products
		$this->filter( 'wp_unique_post_slug', 'apply_product_slug_suffix', 10, 6 );

		// Additional WooCommerce product creation hooks
		$this->action( 'woocommerce_new_product', 'handle_wc_product_creation', 10 );
		$this->action( 'woocommerce_update_product', 'handle_wc_product_creation', 10 );

		// Scheduled action for delayed slug application
		$this->action( 'suffix_master_apply_wc_slug', 'scheduled_wc_slug_application' );

		// Hook into WordPress post insertion for products
		$this->action( 'wp_insert_post', 'handle_product_insertion', 10, 3 );
	}

	/**
	 * Apply title suffix to WooCommerce products
	 *
	 * @param string $name Product name
	 * @param object $product Product object
	 * @return string Modified name
	 */
	public function apply_product_title_suffix( $name, $product ) {
		// Skip if in admin, feeds, or invalid product
		if ( is_admin() || is_feed() || ! is_object( $product ) ) {
			return $name;
		}

		// Get product ID
		$product_id = $product->get_id();
		if ( ! $product_id ) {
			return $name;
		}

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Suffix Master] Product title suffix - Product ID: ' . $product_id . ', Original: "' . $name . '"' );
		}

		// Get suffix (post meta or global setting)
		$suffix = Helper::get_post_suffix( $product_id, '_suffix_master_product_title', 'wc_product_title_suffix' );
		
		if ( ! empty( $suffix ) ) {
			$name .= ' ' . $suffix;
			
			// Debug logging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[Suffix Master] Product title suffix applied - New: "' . $name . '"' );
			}
		}

		return $name;
	}

	/**
	 * Apply product title suffix in loops and archives
	 *
	 * @param string $title Post title
	 * @param int $post_id Post ID
	 * @return string Modified title
	 */
	public function apply_product_title_in_loop( $title, $post_id = null ) {
		// Skip if in admin or feeds
		if ( is_admin() || is_feed() || ! $post_id ) {
			return $title;
		}

		// Only apply to products
		if ( get_post_type( $post_id ) !== 'product' ) {
			return $title;
		}

		// Get suffix (post meta or global setting)
		$suffix = Helper::get_post_suffix( $post_id, '_suffix_master_product_title', 'wc_product_title_suffix' );
		
		if ( ! empty( $suffix ) ) {
			$title .= ' ' . $suffix;
		}

		return $title;
	}

	/**
	 * Simple product title suffix application
	 *
	 * @param string $title Product title
	 * @param object $product Product object
	 * @return string Modified title
	 */
	public function apply_product_title_suffix_simple( $title, $product ) {
		if ( ! is_object( $product ) ) {
			return $title;
		}

		$product_id = $product->get_id();
		if ( ! $product_id ) {
			return $title;
		}

		// Get suffix (post meta or global setting)
		$suffix = Helper::get_post_suffix( $product_id, '_suffix_master_product_title', 'wc_product_title_suffix' );
		
		if ( ! empty( $suffix ) ) {
			$title .= ' ' . $suffix;
		}

		return $title;
	}

	/**
	 * Apply price suffix to WooCommerce products
	 *
	 * @param string $price_html Price HTML
	 * @param object $product Product object
	 * @return string Modified price HTML
	 */
	public function apply_price_suffix( $price_html, $product ) {
		// Skip if in admin, empty price, or invalid product
		if ( is_admin() || empty( $price_html ) || ! is_object( $product ) ) {
			return $price_html;
		}

		// Get product ID
		$product_id = $product->get_id();
		if ( ! $product_id ) {
			return $price_html;
		}

		// Get suffix (post meta or global setting)
		$suffix = Helper::get_post_suffix( $product_id, '_suffix_master_price', 'global_price_suffix' );
		
		if ( ! empty( $suffix ) ) {
			$price_html .= ' <span class="suffix-master-price-suffix">' . esc_html( $suffix ) . '</span>';
		}

		return $price_html;
	}

	/**
	 * Apply product slug suffix during WordPress slug generation
	 * This hooks into wp_unique_post_slug to add suffix during the slug creation process
	 *
	 * @param string $slug Post slug
	 * @param int $post_ID Post ID
	 * @param string $post_status Post status
	 * @param string $post_type Post type
	 * @param int $post_parent Post parent
	 * @param string $original_slug Original slug
	 * @return string Modified slug
	 */
	public function apply_product_slug_suffix( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		// Only apply to products
		if ( $post_type !== 'product' ) {
			return $slug;
		}

		// Skip if this is an existing post being updated (has a non-auto-draft status)
		if ( $post_ID && get_post_status( $post_ID ) && ! in_array( get_post_status( $post_ID ), [ 'auto-draft', false ] ) ) {
			// Check if suffix is already applied
			$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );
			if ( ! empty( $suffix_setting ) ) {
				$processed_suffix = Helper::process_placeholders( $suffix_setting );
				$sanitized_suffix = sanitize_title( $processed_suffix );
				if ( ! empty( $sanitized_suffix ) && strpos( $slug, $sanitized_suffix ) !== false ) {
					return $slug; // Suffix already applied
				}
			}
		}

		// Get WooCommerce slug suffix setting
		$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );

		if ( ! empty( $suffix_setting ) ) {
			$processed_suffix = Helper::process_placeholders( $suffix_setting );
			$sanitized_suffix = sanitize_title( $processed_suffix );

			if ( ! empty( $sanitized_suffix ) ) {
				// Check if suffix is already present
				if ( strpos( $slug, $sanitized_suffix ) === false ) {
					$slug .= '-' . $sanitized_suffix;

					// Debug logging
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( '[Suffix Master] Product slug suffix applied via wp_unique_post_slug - New slug: "' . $slug . '"' );
					}
				}
			}
		}

		return $slug;
	}

	/**
	 * WooCommerce-specific slug suffix handler
	 * This method specifically handles WooCommerce product slug suffixes
	 */
	public function apply_woocommerce_slug_suffix( $post_id ) {
		// Skip if not a product
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'product' ) {
			return;
		}

		// Skip if this is an autosave, revision, or bulk edit
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || defined( 'DOING_BULK_EDIT' ) ) {
			return;
		}

		// Skip if user can't edit posts
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Skip if post doesn't have a proper title yet
		if ( empty( $post->post_title ) || $post->post_title === 'Auto Draft' ) {
			return;
		}

		// Get WooCommerce slug suffix setting
		$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Suffix Master] WC Slug Check - Product ID: ' . $post_id . ', Title: "' . $post->post_title . '", Status: ' . $post->post_status . ', Setting: "' . $suffix_setting . '"' );
		}

		if ( ! empty( $suffix_setting ) ) {
			$processed_suffix = Helper::process_placeholders( $suffix_setting );
			$sanitized_suffix = sanitize_title( $processed_suffix );

			if ( ! empty( $sanitized_suffix ) ) {
				// Generate base slug from title (not from current post_name which might be auto-draft)
				$base_slug = sanitize_title( $post->post_title );

				// Skip if the title generates an empty slug
				if ( empty( $base_slug ) ) {
					return;
				}

				// Check if suffix is already present in the base slug
				if ( strpos( $base_slug, $sanitized_suffix ) === false ) {
					$new_slug = $base_slug . '-' . $sanitized_suffix;

					// Make sure the slug is unique
					$unique_slug = wp_unique_post_slug( $new_slug, $post_id, $post->post_status, $post->post_type, $post->post_parent );

					// Only update if the current slug is different and not already correct
					if ( $post->post_name !== $unique_slug ) {
						// Debug logging
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
							error_log( '[Suffix Master] WC Slug Update - Original: "' . $post->post_name . '", New: "' . $unique_slug . '"' );
						}

						// Update the post slug directly
						global $wpdb;
						$wpdb->update(
							$wpdb->posts,
							[ 'post_name' => $unique_slug ],
							[ 'ID' => $post_id ],
							[ '%s' ],
							[ '%d' ]
						);

						// Clean post cache
						clean_post_cache( $post_id );
					}
				}
			}
		}
	}

	/**
	 * Handle WooCommerce product slug on status transition
	 */
	public function apply_wc_slug_on_transition( $new_status, $old_status, $post ) {
		// Only handle products
		if ( $post->post_type !== 'product' ) {
			return;
		}

		// Only apply when transitioning from auto-draft to another status
		if ( $old_status !== 'auto-draft' ) {
			return;
		}

		// Skip if the new status is still auto-draft
		if ( $new_status === 'auto-draft' ) {
			return;
		}

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Suffix Master] WC Status Transition - Product ID: ' . $post->ID . ', From: ' . $old_status . ', To: ' . $new_status );
		}

		// Apply slug suffix
		$this->apply_woocommerce_slug_suffix( $post->ID );
	}

	/**
	 * Handle WooCommerce product creation via REST API or admin
	 * This catches products created through WooCommerce's own methods
	 */
	public function handle_wc_product_creation( $product_id ) {
		// Ensure we have a valid product ID
		if ( ! $product_id || ! is_numeric( $product_id ) ) {
			return;
		}

		// Small delay to ensure the product is fully created
		wp_schedule_single_event( time() + 1, 'suffix_master_apply_wc_slug', [ $product_id ] );
	}

	/**
	 * Scheduled action to apply WooCommerce slug suffix
	 */
	public function scheduled_wc_slug_application( $product_id ) {
		$this->apply_woocommerce_slug_suffix( $product_id );
	}

	/**
	 * Handle product insertion via wp_insert_post
	 */
	public function handle_product_insertion( $post_id, $post, $update ) {
		// Only handle products
		if ( $post->post_type !== 'product' ) {
			return;
		}

		// Only handle new products (not updates)
		if ( $update ) {
			return;
		}

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Suffix Master] Product insertion detected - ID: ' . $post_id . ', Status: ' . $post->post_status );
		}

		// Apply slug suffix
		$this->apply_woocommerce_slug_suffix( $post_id );
	}
}
