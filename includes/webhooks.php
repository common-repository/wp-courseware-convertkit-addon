<?php
/**
 * WP Courseware - ConvertKit - Webooks.
 *
 * Defines all functions required for
 * listening to WP Courseware webhooks.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Hooks.
add_action( 'wpcw_api_convertkit', __NAMESPACE__ . '\webhook_handler' );

/**
 * Get ConvertKit Webhook Url.
 *
 * @since 4.6.1
 *
 * @param array $args The webhook query args.
 *
 * @return string The webhook url.
 */
function get_webhook_url( $args = array() ) {
	/**
	 * Filter: ConvertKit Webhook Url
	 *
	 * @since 1.0.0
	 *
	 * @param string The convertkit webhook url.
	 *
	 * @return string The convertkit webhook url.
	 */
	$webhook_url = apply_filters( 'wpcw_convertkit_webhook_url', wpcw()->api->get_api_url( 'convertkit', true ) );

	if ( ! empty( $args ) ) {
		$webhook_url = add_query_arg( $args, $webhook_url );
	}

	return esc_url_raw( $webhook_url );
}

/**
 * Get Webhook Events.
 *
 * @since 1.0.0
 *
 * @param array $args The webhook args.
 *
 * @return array The webhook events.
 */
function get_webhook_events( $args = array() ) {
	/**
	 * Filter: ConvertKit Webhook Events.
	 *
	 * @since 1.0.0
	 *
	 * @param array The convertkit webhook events.
	 * @param array $args Optional. The convertkit webhook event args.
	 *
	 * @return array The convertkit webhook events.
	 */
	return apply_filters( 'wpcw_convertkit_webhook_events', array(
		'activate'          => array(
			'label'  => esc_html__( 'Subscriber activated', 'wpcw-convertkit' ),
			'action' => esc_html__( 'Subscriber activated', 'wpcw-convertkit' ),
			'args'   => array( 'name' => 'subscriber.subscriber_activate' ),
		),
		//'unsubscribe'       => array(
		//	'label'  => esc_html__( 'Subscriber unsubscribes', 'wpcw-convertkit' ),
		//	'action' => esc_html__( 'Subscriber unsubscribes', 'wpcw-convertkit' ),
		//	'args'   => array( 'name' => 'subscriber.subscriber_unsubscribe' ),
		//),
		'form'              => array(
			'label'  => esc_html__( 'Subscribes to a form', 'wpcw-convertkit' ),
			'action' => esc_html__( 'Subscribes to a form', 'wpcw-convertkit' ),
			'args'   => array( 'name' => 'subscriber.form_subscribe', 'form_id' => isset( $args['form_id'] ) ? absint( $args['form_id'] ) : 0 ),
		),
		'sequence'          => array(
			'label'  => esc_html__( 'Subscribes to a sequence', 'wpcw-convertkit' ),
			'action' => esc_html__( 'Subscribes to a sequence', 'wpcw-convertkit' ),
			'args'   => array( 'name' => 'subscriber.course_subscribe', 'sequence_id' => isset( $args['sequence_id'] ) ? absint( $args['sequence_id'] ) : 0 ),
		),
		'sequence_complete' => array(
			'label'  => esc_html__( 'Completes a sequence', 'wpcw-convertkit' ),
			'action' => esc_html__( 'Completes a sequence', 'wpcw-convertkit' ),
			'args'   => array( 'name' => 'subscriber.course_complete', 'sequence_id' => isset( $args['sequence_id'] ) ? absint( $args['sequence_id'] ) : 0 ),
		),
		'tag_add'           => array(
			'label'  => esc_html__( 'Tag added', 'wpcw-convertkit' ),
			'action' => esc_html__( 'Tag added', 'wpcw-convertkit' ),
			'args'   => array( 'name' => 'subscriber.tag_add', 'tag_id' => isset( $args['tag_id'] ) ? absint( $args['tag_id'] ) : 0 ),
		),
		//'tag_remove'        => array(
		//	'label'  => esc_html__( 'Tag removed', 'wpcw-convertkit' ),
		//	'action' => esc_html__( 'Tag removed', 'wpcw-convertkit' ),
		//	'args'   => array( 'name' => 'subscriber.tag_remove', 'tag_id' => isset( $args['tag_id'] ) ? absint( $args['tag_id'] ) : 0 ),
		//),
	), $args );
}

/**
 * Get Webhook Event Label.
 *
 * @since 1.0.0
 *
 * @param string $event The event identifier.
 *
 * @return string The event label. Default is blank.
 */
function get_webhook_event_label( $event ) {
	$events = get_webhook_events();

	return isset( $events[ $event ]['label'] ) ? $events[ $event ]['label'] : '';
}

/**
 * Get Webhook Event Action.
 *
 * @since 1.0.0
 *
 * @param string $event The event identifier.
 *
 * @return string The event action. Default is blank.
 */
function get_webhook_event_action( $event ) {
	$events = get_webhook_events();

	return isset( $events[ $event ]['action'] ) ? $events[ $event ]['action'] : '';
}

/**
 * Get Webhook Event Args.
 *
 * @since 1.0.0
 *
 * @param string $event The event identifier.
 */
function get_webhook_event_args( $event, $args = array() ) {
	$events = get_webhook_events( $args );

	return isset( $events[ $event ]['args'] ) ? $events[ $event ]['args'] : '';
}

/**
 * Get Course Webhooks.
 *
 * @since 1.0.0
 *
 * @param int $course_id The course id.
 *
 * @return array|bool The array of course webhooks or false if empty.
 */
function get_course_webhooks( $course_id ) {
	return wpcw_get_course_meta( absint( $course_id ), 'convertkit_webhooks', true );
}

/**
 * Create Course Webhook.
 *
 * @since 1.0.0
 *
 * @param int   $course_id The course id.
 * @param array $webhook The course webhook.
 *
 * @return array|bool The array of webhooks if successful. False otherwise.
 */
function create_course_webhook( $course_id, $webhook ) {
	if ( ! ( $webhooks = get_course_webhooks( absint( $course_id ) ) ) ) {
		$webhooks = array();
	}

	$webhooks[] = $webhook;

	if ( ! wpcw_update_course_meta( absint( $course_id ), 'convertkit_webhooks', $webhooks ) ) {
		return false;
	}

	return $webhooks;
}

/**
 * Delete Course Webhook.
 *
 * @since 1.0.0
 *
 * @param int   $course_id The course id.
 * @param array $webhook_id The course webhook id.
 *
 * @return array|bool The array of existing webhooks if successful. False otherwise.
 */
function delete_course_webhook( $course_id, $webhook_id ) {
	if ( ! ( $webhooks = get_course_webhooks( absint( $course_id ) ) ) ) {
		$webhooks = array();
	}

	foreach ( $webhooks as $key => $existing_webhook ) {
		if ( absint( $webhook_id ) === absint( $existing_webhook['id'] ) ) {
			unset( $webhooks[ $key ] );
		}
	}

	$webhooks = array_values( $webhooks );

	if ( ! wpcw_update_course_meta( absint( $course_id ), 'convertkit_webhooks', $webhooks ) ) {
		return $webhooks;
	}

	return $webhooks;
}

/**
 * Process Course Webhook.
 *
 * @since 1.0.0
 *
 * @param int   $course_id The course id.
 * @param array $webhook The webhook data.
 */
function process_course_webhook( $course_id, $webhook ) {
	if ( ! function_exists( 'wpcw' ) ) {
		return;
	}

	if ( ! isset( $webhook['subscriber'] ) ) {
		return;
	}

	$name  = isset( $webhook['subscriber']['first_name'] ) ? sanitize_text_field( $webhook['subscriber']['first_name'] ) : false;
	$email = isset( $webhook['subscriber']['email_address'] ) ? sanitize_email( $webhook['subscriber']['email_address'] ) : false;
	$state = isset( $webhook['subscriber']['state'] ) ? sanitize_text_field( $webhook['subscriber']['state'] ) : false;

	if ( ! $name && ! $email && ! $state ) {
		return;
	}

	if ( $existing_user_id = email_exists( $email ) ) {
		wpcw()->enrollment->enroll_student( $existing_user_id, array( $course_id ) );
	} else {
		add_filter( 'wpcw_registration_generate_username', '__return_true' );
		add_filter( 'wpcw_registration_generate_password', '__return_true' );

		$student_id = wpcw_create_new_student( $email, '', '', array( $course_id ) );

		if ( is_wp_error( $student_id ) ) {
			wpcw_log( $student_id->get_error_message() );
		}

		if ( $name && ! is_wp_error( $student_id ) ) {
			wp_update_user( array(
				'ID'           => absint( $student_id ),
				'display_name' => sanitize_text_field( $name ),
				'first_name'   => sanitize_text_field( $name )
			) );
		}
	}
}

/**
 * Webhook Get Headers.
 *
 * @since 1.0.0
 *
 * @return array|bool The webhook headers, false otherwise.
 */
function webhook_headers() {
	if ( ! function_exists( 'getallheaders' ) ) {
		$headers = [];
		foreach ( $_SERVER as $name => $value ) {
			if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}

		return $headers;
	} else {
		return getallheaders();
	}
}

/**
 * Webhook Handler.
 *
 * @since 1.0.0
 */
function webhook_handler() {
	$request_body    = file_get_contents( 'php://input' );
	$request_headers = array_change_key_case( webhook_headers(), CASE_UPPER );

	// Set Status Header.
	status_header( 200 );

	// Check for Course Id.
	$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : false;

	// Get Webhook Data.
	$webhook = json_decode( $request_body, true );

	// Process Course Webhook.
	if ( $course_id && $webhook ) {
		process_course_webhook( $course_id, $webhook );
	}

	// Response Code, Clean Data, and Finally Exit!
	http_response_code( 200 );
	ob_end_clean();
	exit;
}

