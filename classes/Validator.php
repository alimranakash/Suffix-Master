<?php
/**
 * Validation and Sanitization functionality
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
 * @subpackage Validator
 * @author Worzen<hello@worzen.com>
 */
class Validator {

	/**
	 * Sanitize text field input
	 *
	 * @param string $input Raw input
	 * @return string Sanitized input
	 */
	public static function sanitize_text_field( $input ) {
		return sanitize_text_field( $input );
	}

	/**
	 * Sanitize textarea input (allows basic HTML)
	 *
	 * @param string $input Raw input
	 * @return string Sanitized input
	 */
	public static function sanitize_textarea( $input ) {
		return wp_kses_post( $input );
	}

	/**
	 * Validate and sanitize suffix setting
	 *
	 * @param string $input Raw input
	 * @param string $type Type of suffix (text|textarea)
	 * @return string Sanitized and validated input
	 */
	public static function validate_suffix_setting( $input, $type = 'text' ) {
		if ( empty( $input ) ) {
			return '';
		}

		// Check for maximum length
		if ( strlen( $input ) > 500 ) {
			add_settings_error(
				'suffix-master',
				'suffix_too_long',
				__( 'Suffix text is too long. Maximum 500 characters allowed.', 'suffix-master' )
			);
			return '';
		}

		// Validate placeholders
		if ( ! self::validate_placeholders( $input ) ) {
			add_settings_error(
				'suffix-master',
				'invalid_placeholder',
				__( 'Invalid placeholder detected. Only {year} and {site_name} are allowed.', 'suffix-master' )
			);
			return '';
		}

		// Sanitize based on type
		if ( $type === 'textarea' ) {
			return self::sanitize_textarea( $input );
		} else {
			return self::sanitize_text_field( $input );
		}
	}

	/**
	 * Validate placeholders in input
	 *
	 * @param string $input Input to validate
	 * @return bool True if valid, false otherwise
	 */
	public static function validate_placeholders( $input ) {
		// Find all placeholders
		preg_match_all( '/\{([^}]+)\}/', $input, $matches );
		
		if ( empty( $matches[1] ) ) {
			return true; // No placeholders found, valid
		}

		$allowed_placeholders = [ 'year', 'site_name' ];
		
		foreach ( $matches[1] as $placeholder ) {
			if ( ! in_array( $placeholder, $allowed_placeholders ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate slug suffix
	 *
	 * @param string $input Raw input
	 * @return string Sanitized slug suffix
	 */
	public static function validate_slug_suffix( $input ) {
		if ( empty( $input ) ) {
			return '';
		}

		// Process placeholders first
		$processed = Helper::process_placeholders( $input );
		
		// Sanitize for slug use
		$sanitized = sanitize_title( $processed );
		
		// Check if the sanitized version is too different from original
		if ( strlen( $sanitized ) < strlen( $processed ) * 0.5 ) {
			add_settings_error(
				'suffix-master',
				'slug_invalid_chars',
				__( 'Slug suffix contains too many invalid characters. Please use only letters, numbers, and hyphens.', 'suffix-master' )
			);
			return '';
		}

		return $input; // Return original with placeholders intact
	}

	/**
	 * Validate settings array
	 *
	 * @param array $settings Raw settings array
	 * @return array Validated and sanitized settings
	 */
	public static function validate_settings( $settings ) {
		$validated = [];

		// Define field validation rules
		$field_rules = [
			'global_title_suffix' => 'text',
			'post_title_suffix' => 'text',
			'page_title_suffix' => 'text',
			'wc_product_title_suffix' => 'text',
			'global_price_suffix' => 'text',
			'global_slug_suffix' => 'slug',
			'wc_slug_suffix' => 'slug',
			'content_footer_suffix' => 'textarea',
			'live_preview' => 'checkbox',
		];

		foreach ( $field_rules as $field => $type ) {
			if ( ! isset( $settings[ $field ] ) ) {
				continue;
			}

			$value = $settings[ $field ];

			switch ( $type ) {
				case 'text':
					$validated[ $field ] = self::validate_suffix_setting( $value, 'text' );
					break;
				case 'textarea':
					$validated[ $field ] = self::validate_suffix_setting( $value, 'textarea' );
					break;
				case 'slug':
					$validated[ $field ] = self::validate_slug_suffix( $value );
					break;
				case 'checkbox':
					$validated[ $field ] = $value === 'on' ? 'on' : '';
					break;
				default:
					$validated[ $field ] = sanitize_text_field( $value );
			}
		}

		return $validated;
	}

	/**
	 * Check if user can manage suffix settings
	 *
	 * @return bool True if user has permission
	 */
	public static function user_can_manage_settings() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Verify nonce for suffix master actions
	 *
	 * @param string $nonce Nonce to verify
	 * @param string $action Action name
	 * @return bool True if valid
	 */
	public static function verify_nonce( $nonce, $action = 'suffix_master_action' ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Sanitize post meta value
	 *
	 * @param mixed $value Meta value to sanitize
	 * @param string $meta_key Meta key
	 * @return mixed Sanitized value
	 */
	public static function sanitize_post_meta( $value, $meta_key ) {
		switch ( $meta_key ) {
			case '_suffix_master_title':
			case '_suffix_master_product_title':
			case '_suffix_master_price':
				return self::validate_suffix_setting( $value, 'text' );
			
			case '_suffix_master_content':
				return self::validate_suffix_setting( $value, 'textarea' );
			
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Log security events
	 *
	 * @param string $event Event description
	 * @param array $data Additional data
	 */
	public static function log_security_event( $event, $data = [] ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'[Suffix Master Security] %s - User: %d - Data: %s',
				$event,
				get_current_user_id(),
				wp_json_encode( $data )
			) );
		}
	}
}
