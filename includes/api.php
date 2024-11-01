<?php
/**
 * WP Courseware - ConvertKit - Api.
 *
 * Defines all functions that are used
 * to interact with the ConvertKit Api.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

use WP_REST_Request;
use WP_REST_Response;
use WPCW\Core\Api;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Hooks.
add_filter( 'wpcw_api_endoints', __NAMESPACE__ . '\register_endpoints', 10, 2 );

/**
 * Register ConvertKit WPCW Api Endpoints.
 *
 * @since 1.0.0
 *
 * @param array $endpoints The endpoints to filter.
 * @param Api The api object.
 *
 * @return array $endpoints The modified array of endpoints.
 */
function register_endpoints( $endpoints, Api $api ) {
	$endpoints[] = array( 'endpoint' => 'convertkit-forms', 'method' => 'GET', 'callback' => __NAMESPACE__ . '\api_endpoint_get_forms' );
	$endpoints[] = array( 'endpoint' => 'convertkit-sequences', 'method' => 'GET', 'callback' => __NAMESPACE__ . '\api_endpoint_get_sequences' );
	$endpoints[] = array( 'endpoint' => 'convertkit-tags', 'method' => 'GET', 'callback' => __NAMESPACE__ . '\api_endpoint_get_tags' );
	$endpoints[] = array( 'endpoint' => 'convertkit-webhooks', 'method' => 'GET', 'callback' => __NAMESPACE__ . '\api_endpoint_get_webhooks' );
	$endpoints[] = array( 'endpoint' => 'convertkit-create-webhook', 'method' => 'POST', 'callback' => __NAMESPACE__ . '\api_endpoint_create_webhook' );
	$endpoints[] = array( 'endpoint' => 'convertkit-delete-webhook', 'method' => 'POST', 'callback' => __NAMESPACE__ . '\api_endpoint_delete_webhook' );
	$endpoints[] = array( 'endpoint' => 'convertkit-clear-cache', 'method' => 'POST', 'callback' => __NAMESPACE__ . '\api_endpoint_clear_cache' );

	/**
	 * Fitler: ConvertKit Api Endpoints.
	 *
	 * @since 1.0.0
	 *
	 * @param array $endpoints The convertkit api endpoints.
	 *
	 * @return array $endpoints The convertkit api endpoints.
	 */
	return apply_filters( 'wpcw_convertkit_api_endpoints', $endpoints );
}

/**
 * API Endpoint Get forms.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_get_forms( WP_REST_Request $request ) {
	return rest_ensure_response( array( 'objects' => converkit_get_forms() ) );
}

/**
 * API Endpoint Get Sequences.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_get_sequences( WP_REST_Request $request ) {
	return rest_ensure_response( array( 'objects' => convertkit_get_sequences() ) );
}

/**
 * API Endpoint Get Tags.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_get_tags( WP_REST_Request $request ) {
	return rest_ensure_response( array( 'objects' => convertkit_get_tags() ) );
}

/**
 * API Endpoint Get Webhooks.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_get_webhooks( WP_REST_Request $request ) {
	$course_id = $request->get_param( 'course_id' );

	return rest_ensure_response( array( 'webhooks' => get_course_webhooks( $course_id ) ) );
}

/**
 * API Endpoint Create Webhook
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_create_webhook( WP_REST_Request $request ) {
	$course_id = $request->get_param( 'course_id' );

	if ( empty( $course_id ) ) {
		return rest_ensure_response( array( 'webhooks' => false ) );
	}

	// Webhook Args.
	$webhook_name     = $request->get_param( 'name' );
	$webhook_type     = $request->get_param( 'type' );
	$webhook_form     = $request->get_param( 'form' );
	$webhook_sequence = $request->get_param( 'sequence' );
	$webhook_tag      = $request->get_param( 'tag' );
	$webhook_args     = array();

	if ( empty( $webhook_type ) ) {
		$webhook_type = 'activate';
	}

	if ( ! empty( $webhook_form ) ) {
		$webhook_args['form_id'] = $webhook_form;
	}

	if ( ! empty( $webhook_sequence ) ) {
		$webhook_args['sequence_id'] = $webhook_sequence;
	}

	if ( ! empty( $webhook_tag ) ) {
		$webhook_args['tag_id'] = $webhook_tag;
	}

	if ( empty( $webhook_name ) ) {
		$webhook_name = sprintf( esc_html__( 'Webhook: %s', 'wpcw-convertkit' ), ucwords( str_replace( '_', ' ', $webhook_type ) ) );
	}

	$webhook_url = get_webhook_url( array( 'course_id' => $course_id ) );

	if ( ! $webhook_details = convertkit_add_webhook( $webhook_type, $webhook_url, $webhook_args ) ) {
		return rest_ensure_response( array( 'webhooks' => false ) );
	}

	$webhook_id     = isset( $webhook_details['id'] ) ? absint( $webhook_details['id'] ) : uniqid();
	$webhook_url    = isset( $webhook_details['target_url'] ) ? esc_url_raw( $webhook_details['target_url'] ) : $webhook_url;
	$webhook_event  = isset( $webhook_details['event'] ) ? sanitize_text_field( $webhook_details['event'] ) : $webhook_type;
	$webhook_action = get_webhook_event_action( $webhook_type );

	$webhook = array(
		'id'     => absint( $webhook_id ),
		'name'   => sanitize_text_field( $webhook_name ),
		'url'    => esc_url_raw( $webhook_url ),
		'type'   => sanitize_text_field( $webhook_type ),
		'action' => sanitize_text_field( $webhook_action ),
		'event'  => is_array( $webhook_event ) ? array_map( 'sanitize_text_field', $webhook_event ) : sanitize_text_field( $webhook_event ),
		'delete' => false,
	);

	return rest_ensure_response( array( 'webhooks' => create_course_webhook( $course_id, $webhook ) ) );
}

/**
 * API Endpoint Delete Webhook
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_delete_webhook( WP_REST_Request $request ) {
	$course_id  = $request->get_param( 'course_id' );
	$webhook_id = $request->get_param( 'webhook_id' );

	if ( empty( $course_id ) ) {
		return rest_ensure_response( array( 'success' => false ) );
	}

	if ( ! $webhook_details = convertkit_delete_webhook( $webhook_id ) ) {
		return rest_ensure_response( array( 'success' => false ) );
	}

	return rest_ensure_response( array( 'webhooks' => delete_course_webhook( $course_id, $webhook_id ) ) );
}

/**
 * API Endpoint Clear Cache.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The api request.
 *
 * @return WP_REST_Response The api response.
 */
function api_endpoint_clear_cache( WP_REST_Request $request ) {
	delete_transient( 'wpcw_convertkit_forms' );
	delete_transient( 'wpcw_convertkit_sequences' );
	delete_transient( 'wpcw_convertkit_tags' );

	/**
	 * Action: ConvertKit Api Clear Cache.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wpcw_convertkit_api_clear_cache' );

	return rest_ensure_response( array( 'success' => true ) );
}

/**
 * Get ConvertKit Forms.
 *
 * @since 1.0.0
 *
 * @return array $forms The ConvertKit forms. Default is empty.
 */
function converkit_get_forms() {
	$api_key = get_convertkit_api_key();
	$forms   = get_transient( 'wpcw_convertkit_forms' );

	if ( $api_key && false === $forms ) {
		$request = wp_remote_get( 'https://api.convertkit.com/v3/forms?api_key=' . $api_key );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			$request_data = json_decode( wp_remote_retrieve_body( $request ) );

			if ( ! empty( $request_data ) && ! empty( $request_data->forms ) ) {
				foreach ( $request_data->forms as $key => $form ) {
					$forms[ $form->id ] = $form->name;
				}
			}

			set_transient( 'wpcw_convertkit_forms', $forms, DAY_IN_SECONDS );
		}
	}

	return (array) $forms;
}

/**
 * Get ConvertKit Sequences.
 *
 * @since 1.0.0
 *
 * @return array $sequences The ConvertKit sequences. Default is empty.
 */
function convertkit_get_sequences() {
	$api_key   = get_convertkit_api_key();
	$sequences = get_transient( 'wpcw_convertkit_sequences' );

	if ( $api_key && false === $sequences ) {
		$request = wp_remote_get( 'https://api.convertkit.com/v3/sequences?api_key=' . $api_key );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			$request_data = json_decode( wp_remote_retrieve_body( $request ) );

			if ( ! empty( $request_data ) && ! empty( $request_data->courses ) ) {
				foreach ( $request_data->courses as $key => $sequence ) {
					$sequences[ $sequence->id ] = $sequence->name;
				}
			}

			set_transient( 'wpcw_convertkit_sequences', $sequences, DAY_IN_SECONDS );
		}
	}

	return (array) $sequences;
}

/**
 * Get ConvertKit Tags.
 *
 * @since 1.0.0
 *
 * @return array $tags The ConvertKit tags. Default is empty.
 */
function convertkit_get_tags() {
	$api_key = get_convertkit_api_key();
	$tags    = get_transient( 'wpcw_convertkit_tags' );

	if ( $api_key && false === $tags ) {
		$request = wp_remote_get( 'https://api.convertkit.com/v3/tags?api_key=' . $api_key );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			$request_data = json_decode( wp_remote_retrieve_body( $request ) );

			if ( ! empty( $request_data ) && ! empty( $request_data->tags ) ) {
				foreach ( $request_data->tags as $key => $tag ) {
					$tags[ $tag->id ] = $tag->name;
				}
			}

			set_transient( 'wpcw_convertkit_tags', $tags, DAY_IN_SECONDS );
		}
	}

	return (array) $tags;
}

/**
 * ConvertKit Add to Form.
 *
 * @since 1.0.0
 *
 * @param string $form_id Required. The form id.
 * @param string $email Required. The email address of the user.
 * @param string $first_name Optional. The first name of the user.
 *
 * @return bool True upon addition, false otherwise.
 */
function convertkit_add_to_form( $form_id, $email, $first_name = '' ) {
	if ( $api_key = get_convertkit_api_key() ) {
		$args = array(
			'api_key' => $api_key,
			'email'   => $email
		);

		if ( ! empty( $first_name ) ) {
			$args['first_name'] = esc_attr( $first_name );
		}

		$request = wp_remote_post( 'https://api.convertkit.com/v3/forms/' . esc_attr( $form_id ) . '/subscribe', array(
			'body'    => $args,
			'timeout' => 30,
		) );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			return true;
		}
	}

	return false;
}

/**
 * ConvertKit Add to Sequence.
 *
 * @since 1.0.0
 *
 * @param string $sequence_id Required. The form id.
 * @param string $email Required. The email address of the user.
 * @param string $first_name Optional. The first name of the user.
 *
 * @return bool True upon addition, false otherwise.
 */
function convertkit_add_to_sequence( $sequence_id, $email, $first_name = '' ) {
	if ( $api_key = get_convertkit_api_key() ) {
		$args = array(
			'api_key' => $api_key,
			'email'   => $email
		);

		if ( ! empty( $first_name ) ) {
			$args['first_name'] = esc_attr( $first_name );
		}

		$request = wp_remote_post( 'https://api.convertkit.com/v3/courses/' . esc_attr( $sequence_id ) . '/subscribe', array(
			'body'    => $args,
			'timeout' => 30,
		) );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			return true;
		}
	}

	return false;
}

/**
 * ConvertKit Add Tag.
 *
 * @since 1.0.0
 *
 * @param string $tag_id Required. The tag id.
 * @param string $email Required. The email address of the user.
 * @param string $first_name Optional. The first name of the user.
 *
 * @return bool True upon addition, false otherwise.
 */
function convertkit_add_tag( $tag_id, $email, $first_name = '' ) {
	if ( $api_key = get_convertkit_api_key() ) {
		$args = array(
			'api_key' => $api_key,
			'email'   => $email
		);

		if ( ! empty( $first_name ) ) {
			$args['first_name'] = esc_attr( $first_name );
		}

		$request = wp_remote_post( 'https://api.convertkit.com/v3/tags/' . esc_attr( $tag_id ) . '/subscribe', array(
			'body'    => $args,
			'timeout' => 30,
		) );

		if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
			return true;
		}
	}

	return false;
}

/**
 * ConvertKit Add Webhook.
 *
 * @since 1.0.0
 *
 * @param string $type Required. The webhook type.
 * @param string $url Required. The webhook url.
 * @param array  $args Optional. Additional request args.
 *
 * @return bool True upon addition, false otherwise.
 */
function convertkit_add_webhook( $type, $url, $args = array() ) {
	if ( $api_secret = get_convertkit_api_secret() ) {
		$webhook_args = array(
			'api_secret' => $api_secret,
			'target_url' => $url,
		);

		$webhook_args['event'] = get_webhook_event_args( $type, $args );

		if ( empty( $webhook_args['event'] ) ) {
			return false;
		}

		$request = wp_remote_post( 'https://api.convertkit.com/v3/automations/hooks', array(
			'body'    => $webhook_args,
			'timeout' => 30,
		) );

		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );

		if ( ! is_wp_error( $request ) && 200 === $response_code && $response_body ) {
			$response_body = json_decode( $response_body, true );

			return $response_body['rule'] ?: $response_body;
		}
	}

	return false;
}

/**
 * ConvertKit Delete Webhook.
 *
 * @since 1.0.0
 *
 * @param int $webhook_id Required. The webhook id.
 *
 * @return bool True upon addition, false otherwise.
 */
function convertkit_delete_webhook( $webhook_id ) {
	if ( $api_secret = get_convertkit_api_secret() ) {
		$webhook_args = array(
			'api_secret' => $api_secret,
		);

		$request = wp_remote_request( 'https://api.convertkit.com/v3/automations/hooks/' . $webhook_id, array(
			'method'  => 'DELETE',
			'body'    => $webhook_args,
			'timeout' => 30,
		) );

		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );

		if ( ! is_wp_error( $request ) && $response_body ) {
			$response_body = json_decode( $response_body, true );

			// Found, so should be deleted.
			if ( 200 === $response_code ) {
				return isset( $response_body['success'] ) ? true : false;
			}

			// Not Found, so should be deleted.
			if ( 404 === $response_code ) {
				return isset( $response_body['error'] ) ? true : false;
			}
		}
	}

	return false;
}

