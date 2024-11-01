<?php
/**
 * WP Courseware - ConvertKit - Settings.
 *
 * Defines all required functions for
 * setting and retrieving settings.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

// Exit if accessed directly.
use WP_Screen;

defined( 'ABSPATH' ) || exit;

// Hooks.
add_filter( 'wpcw_admin_settings_tab_addons', __NAMESPACE__ . '\settings_tab_addons' );
add_action( 'wpcw_enqueue_scripts', __NAMESPACE__ . '\settings_enqueue_assets' );

/**
 * Settings Tab Add-ons.
 *
 * @since 1.0.0
 *
 * @param array $addons The settings tab addons.
 *
 * @return array $addons The settings tab addons.
 */
function settings_tab_addons( $addons ) {
	// Check to see if Convert Kit extension exists.
	if ( isset( $addons['sections']['convertkit'] ) ) {
		return $addons;
	}

	/**
	 * Filter: Convert Kit Settings Fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array The array of convertkit settings fields.
	 *
	 * @return array The array of convertkit settings fields.
	 */
	$convertkit_fields = apply_filters( 'wpcw_convertkit_settings_fields', array(
		array(
			'type'  => 'heading',
			'key'   => 'convertkit_section_heading',
			'title' => esc_html__( 'ConvertKit', 'wpcw-convertkit' ),
			'desc'  => esc_html__( 'Below are settings related to the ConvertKit addon.', 'wpcw-convertkit' ),
		),
		array(
			'type'     => 'checkbox',
			'key'      => 'convertkit_enable',
			'title'    => esc_html__( 'Enable ConvertKit?', 'wpcw-convertkit' ),
			'label'    => esc_html__( 'Enable the ConvertKit integration.', 'wpcw-convertkit' ),
			'desc_tip' => esc_html__( 'When Convert Kit is enabled, WP Courseware will use the settings below to add users to forms, sequences, or tags when enrolled into a course.', 'wpcw-convertkit' ),
			'default'  => 'yes',
		),
		array(
			'type'        => 'password',
			'key'         => 'convertkit_api_key',
			'default'     => '',
			'placeholder' => esc_html__( 'ConvertKit Api Key', 'wpcw-convertkit' ),
			'title'       => esc_html__( 'ConvertKit Api Key', 'wpcw-convertkit' ),
			'desc'        => esc_html__( 'Enter your ConvertKit Api key.', 'wpcw-convertkit' ),
			'desc_tip'    => esc_html__( 'Your ConvertKit Api key can be found in your ConvertKit account under the Account menu.', 'wpcw-convertkit' ),
			'condition'   => array(
				'field' => 'convertkit_enable',
				'value' => 'on',
			),
		),
		array(
			'type'        => 'password',
			'key'         => 'convertkit_api_secret',
			'default'     => '',
			'placeholder' => esc_html__( 'ConvertKit Api Secret', 'wpcw-convertkit' ),
			'title'       => esc_html__( 'ConvertKit Api Secret', 'wpcw-convertkit' ),
			'desc'        => esc_html__( 'Enter your Convert Kit Api secret.', 'wpcw-convertkit' ),
			'desc_tip'    => esc_html__( 'Your ConvertKit Api secret can be found in your ConvertKit account under the Account menu.', 'wpcw-convertkit' ),
			'condition'   => array(
				'field' => 'convertkit_enable',
				'value' => 'on',
			),
		),
		array(
			'type'      => 'content',
			'key'       => 'convertkit_content',
			'title'     => esc_html__( 'ConvertKit Api Cache', 'wpcw-convertkit' ),
			'desc_tip'  => esc_html__( 'By default we cache ConvertKit Api data for 24 hours. Click the button to clear the ConvertKit Api cache.', 'wpcw-convertkit' ),
			'content'   => sprintf(
				'<button id="wpcw-clear-convertkit-cache" class="button-primary" data-loading="%1$s">
					<i class="wpcw-fas wpcw-fa-eraser left"></i> %2$s
				</button>',
				esc_html__( 'Clearing ConverKit Api Cache....', 'wpcw-convertkit' ),
				esc_html__( 'Clear ConvertKit Api Cache', 'wpcw-convertkit' )
			),
			'condition' => array(
				'field' => 'convertkit_enable',
				'value' => 'on',
			),
		)
	) );

	/**
	 * Filter: ConvertKit Settings Section
	 *
	 * @since 1.0.0
	 *
	 * @param array The convertkit settings section params.
	 *
	 * @return array The convertkit settings section params.
	 */
	$addons['sections']['convertkit'] = apply_filters( 'wpcw_convertkit_settings_section', array(
		'label'   => esc_html__( 'ConvertKit', 'wpcw-convertkit' ),
		'form'    => true,
		'default' => true,
		'fields'  => $convertkit_fields,
		'submit'  => esc_html__( 'Save Settings', 'wpcw-convertkit' ),
	) );

	return $addons;
}

/**
 * Settings Enqueue Assets.
 *
 * @since 1.0.0
 *
 * @param WP_Screen $admin_screen The admin screen slug.
 */
function settings_enqueue_assets( $admin_screen ) {
	if ( 'wp-courseware_page_wpcw-settings' !== $admin_screen->id ) {
		return;
	}

	wp_enqueue_script( 'wpcw-convertkit-settings-js', asset_file( 'settings.js', 'js' ), array( 'wpcw-admin' ), WPCW_CONVERTKIT_VERSION, true );
}
