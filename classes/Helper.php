<?php
/**
 * All helpers functions
 */
namespace Worzen\Suffix_Master;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Helper
 * @author Worzen<hello@worzen.com>
 */
class Helper {

	public static function pri( $data, $admin_only = true, $hide_adminbar = true ) {

		if( $admin_only && ! current_user_can( 'manage_options' ) ) return;

		echo '<pre>';
		if( is_object( $data ) || is_array( $data ) ) {
			print_r( $data );
		}
		else {
			var_dump( $data );
		}
		echo '</pre>';

		if( is_admin() && $hide_adminbar ) {
			echo '<style>#adminmenumain{display:none;}</style>';
		}
	}

	/**
	 * @param bool $show_cached either to use a cached list of posts or not. If enabled, make sure to wp_cache_delete() with the `save_post` hook
	 */
	public static function get_posts( $args = [], $show_heading = false, $show_cached = false ) {

		$defaults = [
			'post_type'         => 'post',
			'posts_per_page'    => -1,
			'post_status'		=> 'publish'
		];

		$_args = wp_parse_args( $args, $defaults );

		// use cache
		if( true === $show_cached && ( $cached_posts = wp_cache_get( "SUFFIXMASTER_{$_args['post_type']}", 'SUFFIXMASTER' ) ) ) {
			$posts = $cached_posts;
		}

		// don't use cache
		else {
			$queried = new \WP_Query( $_args );

			$posts = [];
			foreach( $queried->posts as $post ) :
				$posts[ $post->ID ] = $post->post_title;
			endforeach;
			
			wp_cache_add( "SUFFIXMASTER_{$_args['post_type']}", $posts, 'SUFFIXMASTER', 3600 );
		}

		$posts = $show_heading ? [ '' => sprintf( __( '- Choose a %s -', 'SUFFIXMASTER' ), $_args['post_type'] ) ] + $posts : $posts;

		return apply_filters( 'SUFFIXMASTER_get_posts', $posts, $_args );
	}

	public static function get_option( $key, $section, $default = '', $repeater = false ) {

		$options = get_option( $key );

		if ( isset( $options[ $section ] ) ) {
			$option = $options[ $section ];

			if( $repeater === true ) {
				$_option = [];
				foreach ( $option as $key => $values ) {
					$index = 0;
					foreach ( $values as $value ) {
						$_option[ $index ][ $key ] = $value;
						$index++;
					}
				}

				return $_option;
			}

			return $option;
		}

		return $default;
	}

	/**
	 * Get suffix setting value
	 *
	 * @param string $setting_key The setting key
	 * @param mixed $default Default value if setting not found
	 * @return mixed Setting value
	 */
	public static function get_suffix_setting( $setting_key, $default = '' ) {
		// Map setting keys to their option names (how Settings API stores them)
		$option_map = [
			'global_title_suffix' => 'suffix_master_global',
			'post_title_suffix' => 'suffix_master_global',
			'page_title_suffix' => 'suffix_master_global',
			'global_slug_suffix' => 'suffix_master_global',
			'content_footer_suffix' => 'suffix_master_global',
			'wc_product_title_suffix' => 'suffix_master_woocommerce',
			'global_price_suffix' => 'suffix_master_woocommerce',
			'wc_slug_suffix' => 'suffix_master_woocommerce',
			'live_preview' => 'suffix_master_tools',
		];

		if ( isset( $option_map[ $setting_key ] ) ) {
			$option_name = $option_map[ $setting_key ];

			// Get the option data directly
			$option_data = get_option( $option_name, [] );

			if ( is_array( $option_data ) && isset( $option_data[ $setting_key ] ) ) {
				return $option_data[ $setting_key ];
			}
		}

		// Fallback: try the old method for backward compatibility
		$fallback_result = self::get_option( 'suffix-master', $setting_key, null );
		if ( $fallback_result !== null ) {
			return $fallback_result;
		}

		return $default;
	}

	/**
	 * Process placeholders in suffix text
	 *
	 * @param string $text Text containing placeholders
	 * @return string Text with placeholders replaced
	 */
	public static function process_placeholders( $text ) {
		if ( empty( $text ) ) {
			return $text;
		}

		$placeholders = [
			'{year}' => date( 'Y' ),
			'{site_name}' => get_bloginfo( 'name' ),
		];

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
	}

	/**
	 * Get post meta with fallback to global setting
	 *
	 * @param int $post_id Post ID
	 * @param string $meta_key Meta key
	 * @param string $global_setting_key Global setting key
	 * @return string Meta value or global setting
	 */
	public static function get_post_suffix( $post_id, $meta_key, $global_setting_key ) {
		$post_suffix = get_post_meta( $post_id, $meta_key, true );

		if ( ! empty( $post_suffix ) ) {
			return self::process_placeholders( $post_suffix );
		}

		$global_suffix = self::get_suffix_setting( $global_setting_key );
		return self::process_placeholders( $global_suffix );
	}

	/**
	 * Get title suffix based on post type with proper fallback hierarchy
	 *
	 * @param int $post_id Post ID
	 * @param string $post_type Post type
	 * @return string Title suffix
	 */
	public static function get_title_suffix_by_post_type( $post_id, $post_type ) {
		// 1. First check for post-specific meta override
		$post_meta_suffix = get_post_meta( $post_id, '_suffix_master_title', true );
		if ( ! empty( $post_meta_suffix ) ) {
			return self::process_placeholders( $post_meta_suffix );
		}

		// 2. Then check for post-type specific setting
		$post_type_suffix = '';
		if ( $post_type === 'post' ) {
			$post_type_suffix = self::get_suffix_setting( 'post_title_suffix' );
		} elseif ( $post_type === 'page' ) {
			$post_type_suffix = self::get_suffix_setting( 'page_title_suffix' );
		}

		if ( ! empty( $post_type_suffix ) ) {
			return self::process_placeholders( $post_type_suffix );
		}

		// 3. Finally fallback to global title suffix
		$global_suffix = self::get_suffix_setting( 'global_title_suffix' );
		return self::process_placeholders( $global_suffix );
	}

	/**
	 * Includes a template file resides in /views diretory
	 *
	 * It'll look into /plugin-client directory of your active theme
	 * first. if not found, default template will be used.
	 * can be overwriten with plugin-client_template_overwrite_dir hook
	 *
	 * @param string $slug slug of template. Ex: template-slug.php
	 * @param string $sub_dir sub-directory under base directory
	 * @param array $fields fields of the form
	 */
	public static function get_template( $slug, $base = 'views', $args = null ) {

		// templates can be placed in this directory
		$overwrite_template_dir = apply_filters( 'SUFFIXMASTER_template_overwrite_dir', get_stylesheet_directory() . '/plugin-client/', $slug, $base, $args );
		
		// default template directory
		$plugin_template_dir = dirname( SUFFIXMASTER ) . "/{$base}/";

		// full path of a template file in plugin directory
		$plugin_template_path =  $plugin_template_dir . $slug . '.php';
		
		// full path of a template file in overwrite directory
		$overwrite_template_path =  $overwrite_template_dir . $slug . '.php';

		// if template is found in overwrite directory
		if( file_exists( $overwrite_template_path ) ) {
			ob_start();
			include $overwrite_template_path;
			return ob_get_clean();
		}
		// otherwise use default one
		elseif ( file_exists( $plugin_template_path ) ) {
			ob_start();
			include $plugin_template_path;
			return ob_get_clean();
		}
		else {
			return __( 'Template not found!', 'plugin-client' );
		}
	}
}