<?php
/**
 * All public facing functions
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
 * @subpackage Front
 * @author Worzen<hello@worzen.com>
 */
class Front extends Base {

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

	public function head() {}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'SUFFIXMASTER_DEBUG' ) && SUFFIXMASTER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", SUFFIXMASTER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", SUFFIXMASTER ), [ 'jquery' ], $this->version, true );

		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce( 'suffix_master_ajax' ),
		];
		wp_localize_script( $this->slug, 'SUFFIXMASTER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
		echo '
		<div id="plugin-client-modal" style="display: none">
			<img id="plugin-client-modal-loader" src="' . esc_attr( SUFFIXMASTER_ASSET . '/img/loader.gif' ) . '" />
		</div>';
	}

	/**
	 * Apply title suffix to posts and pages
	 *
	 * @param string $title Post title
	 * @param int $post_id Post ID
	 * @return string Modified title
	 */
	public function apply_title_suffix( $title, $post_id = null ) {
		// Skip if in admin, feeds, or no post ID
		if ( is_admin() || is_feed() || ! $post_id ) {
			return $title;
		}


		// Get post type
		$post_type = get_post_type( $post_id );

		// Skip products as they have their own handler
		if ( $post_type === 'product' ) {
			return $title;
		}

		// Skip if not a valid post type
		if ( ! in_array( $post_type, [ 'post', 'page' ] ) ) {
			return $title;
		}

		// Get suffix using the new post-type aware method
		$suffix = Helper::get_title_suffix_by_post_type( $post_id, $post_type );

		if ( ! empty( $suffix ) ) {
			$title .= ' ' . $suffix;
		}

		return $title;
	}





	/**
	 * Apply content footer suffix
	 *
	 * @param string $content Post content
	 * @return string Modified content
	 */
	public function apply_content_suffix( $content ) {
		// Skip if in admin, feeds, or not in main query
		if ( is_admin() || is_feed() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Get current post
		global $post;
		if ( ! $post || ! $post->ID ) {
			return $content;
		}

		// Only apply on singular pages (posts, pages, products)
		if ( ! is_singular() ) {
			return $content;
		}

		// Get suffix (post meta or global setting)
		$suffix = Helper::get_post_suffix( $post->ID, '_suffix_master_content', 'content_footer_suffix' );

		if ( ! empty( $suffix ) ) {
			$content .= '<div class="suffix-master-content-footer">' . wpautop( $suffix ) . '</div>';
		}

		return $content;
	}

	/**
	 * Apply slug suffix to new posts
	 *
	 * @param string $slug Post slug
	 * @param int $post_ID Post ID
	 * @param string $post_status Post status
	 * @param string $post_type Post type
	 * @param int $post_parent Post parent
	 * @param string $original_slug Original slug
	 * @return string Modified slug
	 */
	public function apply_slug_suffix( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		// Skip if not a valid post type
		if ( ! in_array( $post_type, [ 'post', 'page', 'product' ] ) ) {
			return $slug;
		}

		// Skip if this is an existing post being updated
		if ( $post_ID && get_post_status( $post_ID ) && get_post_status( $post_ID ) !== 'auto-draft' ) {
			return $slug;
		}

		// Get the appropriate suffix setting
		$suffix_setting = '';
		if ( $post_type === 'product' ) {
			$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );
		} else {
			$suffix_setting = Helper::get_suffix_setting( 'global_slug_suffix' );
		}

		if ( ! empty( $suffix_setting ) ) {
			$processed_suffix = Helper::process_placeholders( $suffix_setting );
			$sanitized_suffix = sanitize_title( $processed_suffix );

			// Only add suffix if it's not already present
			if ( ! empty( $sanitized_suffix ) && strpos( $slug, $sanitized_suffix ) === false ) {
				$slug .= '-' . $sanitized_suffix;
			}
		}

		return $slug;
	}

	/**
	 * Alternative slug suffix method using save_post hook
	 * This ensures slug suffixes are applied when posts are saved
	 */
	public function apply_slug_suffix_on_save( $post_id ) {
		// Skip if this is an autosave, revision, or bulk edit
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || defined( 'DOING_BULK_EDIT' ) ) {
			return;
		}

		// Skip if user can't edit posts
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Skip if not a valid post type
		if ( ! in_array( $post->post_type, [ 'post', 'page', 'product' ] ) ) {
			return;
		}

		// Only apply to new posts (auto-draft) or posts without custom slugs
		if ( $post->post_status !== 'auto-draft' && ! empty( $post->post_name ) && $post->post_name !== sanitize_title( $post->post_title ) ) {
			return;
		}

		// Get the appropriate suffix setting
		$suffix_setting = '';
		if ( $post->post_type === 'product' ) {
			$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );
		} else {
			$suffix_setting = Helper::get_suffix_setting( 'global_slug_suffix' );
		}

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Suffix Master] Slug suffix check - Post ID: ' . $post_id . ', Type: ' . $post->post_type . ', Setting: "' . $suffix_setting . '"' );
		}

		if ( ! empty( $suffix_setting ) ) {
			$processed_suffix = Helper::process_placeholders( $suffix_setting );
			$sanitized_suffix = sanitize_title( $processed_suffix );

			if ( ! empty( $sanitized_suffix ) ) {
				// Generate base slug from title
				$base_slug = sanitize_title( $post->post_title );

				// Check if suffix is already present
				if ( strpos( $base_slug, $sanitized_suffix ) === false ) {
					$new_slug = $base_slug . '-' . $sanitized_suffix;

					// Make sure the slug is unique
					$unique_slug = wp_unique_post_slug( $new_slug, $post_id, $post->post_status, $post->post_type, $post->post_parent );

					// Debug logging
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( '[Suffix Master] Applying slug suffix - Original: "' . $base_slug . '", New: "' . $unique_slug . '"' );
					}

					// Update the post slug
					remove_action( 'save_post', [ $this, 'apply_slug_suffix_on_save' ] );
					wp_update_post( [
						'ID' => $post_id,
						'post_name' => $unique_slug
					] );
					add_action( 'save_post', [ $this, 'apply_slug_suffix_on_save' ] );
				}
			}
		}
	}

	/**
	 * Apply slug suffix on post transition (when status changes)
	 * This catches posts when they transition from auto-draft to draft/publish
	 */
	public function apply_slug_suffix_on_transition( $new_status, $old_status, $post ) {
		// Only apply when transitioning from auto-draft to another status
		if ( $old_status !== 'auto-draft' || $new_status === 'auto-draft' ) {
			return;
		}

		// Skip if not a valid post type
		if ( ! in_array( $post->post_type, [ 'post', 'page', 'product' ] ) ) {
			return;
		}

		// Get the appropriate suffix setting
		$suffix_setting = '';
		if ( $post->post_type === 'product' ) {
			$suffix_setting = Helper::get_suffix_setting( 'wc_slug_suffix' );
		} else {
			$suffix_setting = Helper::get_suffix_setting( 'global_slug_suffix' );
		}

		// Debug logging for products
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $post->post_type === 'product' ) {
			error_log( '[Suffix Master] Product slug transition - Post ID: ' . $post->ID . ', Setting: "' . $suffix_setting . '"' );
		}

		if ( ! empty( $suffix_setting ) ) {
			$processed_suffix = Helper::process_placeholders( $suffix_setting );
			$sanitized_suffix = sanitize_title( $processed_suffix );

			if ( ! empty( $sanitized_suffix ) ) {
				// Generate base slug from title
				$base_slug = sanitize_title( $post->post_title );

				// Check if suffix is already present
				if ( strpos( $base_slug, $sanitized_suffix ) === false && strpos( $post->post_name, $sanitized_suffix ) === false ) {
					$new_slug = $base_slug . '-' . $sanitized_suffix;

					// Make sure the slug is unique
					$unique_slug = wp_unique_post_slug( $new_slug, $post->ID, $new_status, $post->post_type, $post->post_parent );

					// Update the post slug directly in database to avoid infinite loops
					global $wpdb;
					$wpdb->update(
						$wpdb->posts,
						[ 'post_name' => $unique_slug ],
						[ 'ID' => $post->ID ],
						[ '%s' ],
						[ '%d' ]
					);

					// Clean post cache
					clean_post_cache( $post->ID );
				}
			}
		}
	}







}