<?php
/**
 * WP Courseware - ConvertKit - Common.
 *
 * Defines all functions common and
 * useful throughout the plugin.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Is ConvertKit Enabled?
 *
 * @since 1.0.0
 *
 * @return bool True if ConvertKit is enabled.
 */
function is_convertkit_enabled() {
	$api_key    = get_convertkit_api_key();
	$api_secret = get_convertkit_api_secret();

	/**
	 * Filter: Converkit Enabled?
	 *
	 * @since 1.0.0
	 *
	 * @param bool The hook enabled boolean. Default is true.
	 *
	 * @return bool The hook enabled boolean. Default is true.
	 */
	$enabled = apply_filters( 'wpcw_convertkit_enabled', true );

	return ( 'yes' === wpcw_get_setting( 'convertkit_enable' ) ) && $api_key && $api_secret && $enabled
		? true
		: false;
}

/**
 * Get ConvertKit Api Key.
 *
 * @since 1.0.0
 *
 * @return string The ConvertKit Api key.
 */
function get_convertkit_api_key() {
	return wpcw_get_setting( 'convertkit_api_key' );
}

/**
 * Get ConvertKit Api Secret.
 *
 * @since 1.0.0
 *
 * @return string The ConvertKit Api Secret.
 */
function get_convertkit_api_secret() {
	return wpcw_get_setting( 'convertkit_api_secret' );
}

/**
 * Get Course ConvertKit Forms.
 *
 * @since 1.0.0
 *
 * @param int $course_id The course id.
 *
 * @return array The ConvertKit forms.
 */
function get_course_convertkit_forms( $course_id ) {
	return (array) wpcw_get_course_meta( $course_id, 'convertkit_forms', true );
}

/**
 * Get Course ConvertKit Sequences.
 *
 * @since 1.0.0
 *
 * @param int $course_id The course id.
 *
 * @return array The ConvertKit sequences.
 */
function get_course_convertkit_sequences( $course_id ) {
	return (array) wpcw_get_course_meta( $course_id, 'convertkit_sequences', true );
}

/**
 * Get Course ConvertKit Tags.
 *
 * @since 1.0.0
 *
 * @param int $course_id The course id.
 *
 * @return array The ConvertKit tags.
 */
function get_course_convertkit_tags( $course_id ) {
	return (array) wpcw_get_course_meta( $course_id, 'convertkit_tags', true );
}
