<?php
/**
 * WP Courseware - ConvertKit - Plugin Requirements.
 *
 * Defines all functions required for the plugin
 * to be able to run in a stable environment.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Requirement Constants.
define( 'WPCW_CONVERTKIT_MIN_PHP_VER', '5.6.2' );
define( 'WPCW_CONVERTKIT_MIN_WP_VER', '4.8.0' );
define( 'WPCW_CONVERTKIT_MIN_WPCW_VER', '4.6.1' );

/**
 * Meets Requirements.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_meets_requirements() {
	$meets_requirements = true;

	// Check PHP Version.
	if ( ! version_compare( phpversion(), WPCW_CONVERTKIT_MIN_PHP_VER, '>=' ) ) {
		$meets_requirements = false;
		add_action( 'admin_notices', 'wpcw_convertkit_fail_min_php_version_notice' );
	}

	// Check WP Version.
	if ( ! version_compare( get_bloginfo( 'version' ), WPCW_CONVERTKIT_MIN_WP_VER, '>=' ) ) {
		$meets_requirements = false;
		add_action( 'admin_notices', 'wpcw_convertkit_fail_min_wp_version_notice' );
	}

	// Check if WP Courseware is installed?
	if ( ! function_exists( 'wpcw' ) ) {
		$meets_requirements = false;
		add_action( 'admin_notices', 'wpcw_convertkit_fail_wpcw_installed_notice' );
	}

	// Check if WP Courseware is at the latest version.
	if ( function_exists( 'wpcw' ) && ! version_compare( WPCW_VERSION, WPCW_CONVERTKIT_MIN_WPCW_VER, '>=' ) ) {
		$meets_requirements = false;
		add_action( 'admin_notices', 'wpcw_convertkit_fail_min_wpcw_version_notice' );
	}

	// Deactivate Plugin if requirements fail.
	if ( ! $meets_requirements ) {
		add_action( 'admin_init', 'wpcw_convertkit_requirements_failed_so_deactivate_plugin' );
	}

	return $meets_requirements;
}

/**
 * Minimum PHP version requirement failed notice.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_fail_min_php_version_notice() {
	/* translators: %s: PHP version */
	$message      = sprintf( esc_html__( 'WP Courseware - Convertkit requires PHP version %s+ to run. Because you are using an earlier version, the plugin cannot run and has been deactivated.', 'wpcw-convertkit' ), WPCW_CONVERTKIT_MIN_PHP_VER );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * Minimum WordPress version requirement failed notice.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_fail_min_wp_version_notice() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'WP Courseware - Convertkit requires WordPress version %s+. Because you are using an earlier version, the plugin cannot run and has been deactivated.', 'wpcw-convertkit' ), WPCW_CONVERTKIT_MIN_WP_VER );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * WP Courseware installed requirement failed notice.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_fail_wpcw_installed_notice() {
	$message      = esc_html__( 'WP Courseware - Convertkit requires WP Courseware to be installed, therefore the plugin cannot run and has been deactivated.', 'wpcw-convertkit' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * Minimum WordPress version requirement failed notice.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_fail_min_wpcw_version_notice() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'WP Courseware - Convertkit requires WP Courseware version %s+. Because you are using an earlier version, the plugin cannot run and has been deactivated.', 'wpcw-convertkit' ), WPCW_CONVERTKIT_MIN_WPCW_VER );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * Requirements have failed so deactivate plugin.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_requirements_failed_so_deactivate_plugin() {
	// Check for capability.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Check for proper function.
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	// Deactivate.
	deactivate_plugins( WPCW_CONVERTKIT_PATH . 'wpcw-convertkit.php' );

	// Unset the 'activate' paramater.
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
