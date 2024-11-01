<?php
/**
 * WP Courseware - ConvertKit - Utilities.
 *
 * Defines some utility functions that
 * can be used throughout the plugin.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Utility - Array Insert Before Key.
 *
 * @since 1.0.0
 *
 * @param array  $array
 * @param string $key
 * @param null   $data
 *
 * @return array
 */
function array_insert_before_key( $array, $key, $data = null ) {
	if ( ( $offset = array_search( $key, array_keys( $array ) ) ) === false ) {
		$offset = 0;
		$offset = count( $array );
	}

	return array_merge( array_slice( $array, 0, $offset ), (array) $data, array_slice( $array, $offset ) );
}

/**
 * Assets File Helper.
 *
 * @since 1.0.0
 *
 * @param string $file The file name.
 * @param string $path The file path.
 *
 * @return string The asset file url.
 */
function asset_file( $file, $path ) {
	$asset_url = trailingslashit( WPCW_CONVERTKIT_URL . 'assets/' . $path ) . $file;

	$mix_file     = "/{$path}/{$file}";
	$mix_manifest = WPCW_CONVERTKIT_PATH . 'assets/mix-manifest.json';

	if ( file_exists( $mix_manifest ) ) {
		$mix_assets = json_decode( file_get_contents( $mix_manifest ), true );

		if ( isset( $mix_assets[ $mix_file ] ) ) {
			$asset_url = untrailingslashit( WPCW_CONVERTKIT_URL . 'assets/' ) . $mix_assets[ $mix_file ];
		}
	}

	return esc_url( $asset_url );
}
