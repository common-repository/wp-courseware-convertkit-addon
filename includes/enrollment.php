<?php
/**
 * WP Courseware - ConvertKit - Enrollment.
 *
 * Defines all functions required to properly
 * integrate with WP Courseware enrollment.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Hooks.
add_action( 'wpcw_enroll_user', __NAMESPACE__ . '\convertkit_actions_upon_enrollment', 10, 2 );

/**
 * Convert Kit Actions upon Enrollment.
 *
 * @since 1.0.0
 *
 * @param int   $user_id The user id.
 * @param array $courses_enrolled The courses the student was enrolled.
 */
function convertkit_actions_upon_enrollment( $user_id, $courses_enrolled ) {
	if ( ! is_convertkit_enabled() || empty( $courses_enrolled ) ) {
		return;
	}

	$user       = get_userdata( $user_id );
	$user_email = $user->user_email;
	$user_name  = $user->first_name;

	$forms     = array();
	$sequences = array();
	$tags      = array();

	foreach ( $courses_enrolled as $course_enrolled_id ) {
		$course_forms     = get_course_convertkit_forms( $course_enrolled_id );
		$course_sequences = get_course_convertkit_sequences( $course_enrolled_id );
		$course_tags      = get_course_convertkit_tags( $course_enrolled_id );

		$forms     = array_unique( array_merge( $forms, $course_forms ), SORT_REGULAR );
		$sequences = array_unique( array_merge( $sequences, $course_sequences ), SORT_REGULAR );
		$tags      = array_unique( array_merge( $tags, $course_tags ), SORT_REGULAR );
	}

	// Process Forms.
	if ( ! empty( $forms ) ) {
		foreach ( $forms as $form_id ) {
			convertkit_add_to_form( $form_id, $user_email, $user_name );
		}
	}

	// Process Sequences.
	if ( ! empty( $sequences ) ) {
		foreach ( $sequences as $sequence_id ) {
			convertkit_add_to_sequence( $sequence_id, $user_email, $user_name );
		}
	}

	// Process Sequences.
	if ( ! empty( $tags ) ) {
		foreach ( $tags as $tag_id ) {
			convertkit_add_tag( $tag_id, $user_email, $user_name );
		}
	}
}
