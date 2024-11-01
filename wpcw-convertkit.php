<?php
/**
 * Plugin Name: WP Courseware - ConvertKit Addon
 * Plugin URI:  https://wordpress.org/plugins/wp-courseware-convertkit-addon/
 * Author:      Fly Plugins
 * Author URI:  https://flyplugins.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: ConvertKit add-on for WP Courseware. Subscribe your customers to ConvertKit forms, sequences, and tags upon enrollment.
 * Version:     1.0.1
 * Text Domain: wpcw-convertkit
 * Domain Path: /languages/
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Version.
define( 'WPCW_CONVERTKIT_VERSION', '1.0.1' );

// File, Path & Url.
define( 'WPCW_CONVERTKIT_FILE', __FILE__ );
define( 'WPCW_CONVERTKIT_PATH', plugin_dir_path( WPCW_CONVERTKIT_FILE ) );
define( 'WPCW_CONVERTKIT_URL', plugin_dir_url( WPCW_CONVERTKIT_FILE ) );

/**
 * Load WP Courseware - ConvertKit Plugin.
 *
 * @since 1.0.0
 */
function wpcw_convertkit_load() {
	// Load Textdomain.
	load_plugin_textdomain( 'wpcw-convertkit', false, WPCW_CONVERTKIT_PATH . 'languages/' );

	// Load Requirements.
	require_once WPCW_CONVERTKIT_PATH . 'includes/requirements.php';

	// Meets Requirements?
	if ( ! wpcw_convertkit_meets_requirements() ) {
		return;
	}

	// Load Files.
	require_once WPCW_CONVERTKIT_PATH . 'includes/api.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/common.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/course.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/enrollment.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/settings.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/utilities.php';
	require_once WPCW_CONVERTKIT_PATH . 'includes/webhooks.php';
}
add_action( 'plugins_loaded', 'wpcw_convertkit_load' );
