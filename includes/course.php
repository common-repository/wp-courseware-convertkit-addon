<?php
/**
 * WP Courseware - ConvertKit - Course.
 *
 * Defines all required functions
 * for course functionality.
 *
 * @since 1.0.0
 */

namespace FlyPlugins\WPCW\ConvertKit;

use WP_Screen;
use WPCW\Admin\Fields;
use WPCW\Admin\Pages\Page_Course;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Hooks.
add_filter( 'wpcw_course_tabs', __NAMESPACE__ . '\course_settings_tab_convertkit', 10, 2 );
add_action( 'wpcw_fields_field_convertkitselect', __NAMESPACE__ . '\course_settings_field_convertkitselect', 10, 2 );
add_action( 'wpcw_fields_field_convertkitselect_views', __NAMESPACE__ . '\course_settings_field_convertkitselect_view', 10, 2 );
add_filter( 'wpcw_fields_validate_field_convertkitselect', __NAMESPACE__ . '\course_settings_field_convertkitselect_validate' );
add_action( 'wpcw_fields_field_convertkitwebhooks', __NAMESPACE__ . '\course_settings_field_convertkitwebhooks', 10, 2 );
add_action( 'wpcw_fields_field_convertkitwebhooks_views', __NAMESPACE__ . '\course_settings_field_convertkitwebhooks_view', 10, 2 );
add_action( 'wpcw_enqueue_scripts', __NAMESPACE__ . '\course_enqueue_assets' );

/**
 * Course Settings Tab - ConvertKit
 *
 * @since 1.0.0
 *
 * @param array $tabs The course tabs.
 * @param Page_Course The page course object.
 *
 * @return array $tabs The course tabs.
 */
function course_settings_tab_convertkit( $tabs, $page_course = false ) {
	if ( ! is_convertkit_enabled() || ! $page_course ) {
		return $tabs;
	}

	/**
	 * Filter: ConvertKit Course Settings Fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array The convertkit course settings fields.
	 *
	 * @return array The convertkit course settings fields.
	 */
	$tabs['convertkit'] = apply_filters( 'wpcw_convertkit_course_settings_fields', array(
		'id'     => 'convertkit',
		'label'  => esc_html__( 'ConvertKit', 'wpcw-convertkit' ),
		'icon'   => '<i class="wpcw-fas wpcw-fa-envelope-open"></i>',
		'fields' => array(
			'convertkit_forms'     => array(
				'id'          => 'convertkit_forms',
				'name'        => 'convertkit_forms',
				'type'        => 'convertkitselect',
				'label'       => esc_html__( 'ConvertKit Forms', 'wpcw-convertkit' ),
				'placeholder' => esc_html__( 'Select Forms', 'wpcw-convertkit' ),
				'desc'        => esc_html__( 'Select the forms that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'tip'         => esc_html__( 'Select the forms that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'object_type' => 'forms',
			),
			'convertkit_sequences' => array(
				'id'          => 'convertkit_sequences',
				'name'        => 'convertkit_sequences',
				'type'        => 'convertkitselect',
				'label'       => esc_html__( 'ConvertKit Sequences', 'wpcw-convertkit' ),
				'placeholder' => esc_html__( 'Select Sequences', 'wpcw-convertkit' ),
				'desc'        => esc_html__( 'Select the sequences that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'tip'         => esc_html__( 'Select the sequences that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'object_type' => 'sequences',
			),
			'convertkit_tags'      => array(
				'id'          => 'convertkit_tags',
				'name'        => 'convertkit_tags',
				'type'        => 'convertkitselect',
				'label'       => esc_html__( 'ConvertKit Tags', 'wpcw-convertkit' ),
				'placeholder' => esc_html__( 'Select Tags', 'wpcw-convertkit' ),
				'desc'        => esc_html__( 'Select the tags that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'tip'         => esc_html__( 'Select the tags that will be used in ConvertKit when a user is enrolled.', 'wpcw-convertkit' ),
				'object_type' => 'tags',
			),
			'convertkit_webhooks'  => array(
				'id'        => 'convertkit_webhooks',
				'name'      => 'convertkit_webhooks',
				'type'      => 'convertkitwebhooks',
				'ignore'    => true,
				'label'     => esc_html__( 'ConvertKit Webooks', 'wpcw-convertkit' ),
				'desc'      => esc_html__( 'Add ConvertKit Webhooks that will allow enrollment to this course.', 'wpcw-convertkit' ),
				'tip'       => esc_html__( 'Add ConvertKit Webhooks that will allow enrollment to this course.', 'wpcw-convertkit' ),
				'course_id' => ! empty( $page_course->course ) ? $page_course->course->get_id() : 0
			),
		),
	) );

	return $tabs;
}

/**
 * Course Settings Field - ConvertKit Select.
 *
 * @since 1.0.0
 *
 * @param array  $field The field array.
 * @param Fields $fields The fields object.
 */
function course_settings_field_convertkitselect( $field, $fields ) {
	$field = wp_parse_args( $field, array(
		'id'          => '',
		'name'        => '',
		'value'       => '',
		'default'     => '',
		'size'        => 'large',
		'placeholder' => esc_html__( 'Select', 'wpcw-convertkit' ),
	) );

	$id          = $fields->get_field_id( $field );
	$name        = $fields->get_field_name( $field );
	$placeholder = $fields->get_field_placeholder( $field );
	$value       = $fields->get_field_value( $field );
	$size        = isset( $field['size'] ) ? esc_attr( $field['size'] ) : 'large';
	$object_type = isset( $field['object_type'] ) ? esc_attr( $field['object_type'] ) : 'forms';
	$objects     = array();

	if ( is_array( $value ) && ! empty( $value ) ) {
		$objects = $value;
	}

	printf(
		'<wpcw-field-convertkitselect id="%s" name="%s[]" sizeclass="size-%s" objects="%s" placeholder="%s" object_type="%s">%s</wpcw-field-convertkitselect>',
		$id,
		$name,
		$size,
		htmlspecialchars( wp_json_encode( $objects ) ),
		$placeholder,
		$object_type,
		esc_html__( 'Loading...', 'wpcw-convertkit' )
	);
}

/**
 * Course Settings Field - ConvertKit Select - View
 *
 * @since 1.0.0
 *
 * @param string $type The field type.
 * @param Fields $fields The fields object.
 */
function course_settings_field_convertkitselect_view( $type, $fields ) {
	?>
	<script type="text/x-template" id="wpcw-field-convertkitselect">
		<div class="wpcw-field-convertkitselect-wrapper">
			<select :id="fieldId"
			        class="wpcw-field-convertkitselect-dropdown"
			        :data-placeholder="placeholder"
			        data-allow_clear="true"
			        :name="name"
			        style="width:100%;"
			        multiple="multiple">
			</select>
		</div>
	</script>
	<?php
}

/**
 * Course Settings Field - ConvertKit Select - Validate
 *
 * @since 1.0.0
 *
 * @param mixed $value The field value.
 *
 * @return mixed $value The field value validated.
 */
function course_settings_field_convertkitselect_validate( $value ) {
	if ( ! is_null( $value ) && ! is_array( $value ) ) {
		$value = maybe_unserialize( $value );
	}

	// Backwards Compatability.
	if ( is_array( $value ) ) {
		$new_value_array = array();

		foreach ( $value as $select_key => $select_value ) {
			if ( 'on' === $select_value ) {
				$new_value_array[] = $select_key;
			}
		}

		if ( ! empty( $new_value_array ) ) {
			$value = $new_value_array;
		}
	}

	$value = is_null( $value ) ? '' : $value;

	return $value;
}

/**
 * Course Settings Field - ConvertKit Webhooks.
 *
 * @since 1.0.0
 *
 * @param array  $field The field array.
 * @param Fields $fields The fields object.
 */
function course_settings_field_convertkitwebhooks( $field, $fields ) {
	$field = wp_parse_args( $field, array(
		'course_id' => 0
	) );

	$course_id = ! empty( $field['course_id'] ) ? absint( $field['course_id'] ) : 0;

	printf( '<wpcw-field-convertkitwebhooks course_id="%d">%s</wpcw-field-convertkitwebhooks>', $course_id, esc_html__( 'Loading...', 'wpcw-convertkit' ) );
}

/**
 * Course Settings Field - ConvertKit Webhooks - View
 *
 * @since 1.0.0
 *
 * @param string $type The field type.
 * @param Fields $fields The fields object.
 */
function course_settings_field_convertkitwebhooks_view( $type, $fields ) {
	?>
	<script type="text/x-template" id="wpcw-field-convertkitwebhooks">
		<?php course_settings_field_convertkitwebhooks_view_html( $type, $fields ); ?>
	</script>
	<?php
}

/**
 * Course Settings Field - ConvertKit Webhooks - View Html
 *
 * @since 1.0.0
 *
 * @param string $type The field type.
 * @param Fields $fields The fields object.
 */
function course_settings_field_convertkitwebhooks_view_html( $type, $fields ) {
	?>
	<div class="wpcw-field-convertkitwebhooks-wrapper">
		<div id="wpcw-field-convertkitwebhooks-modal" class="wpcw-field-convertkitwebhooks-modal wpcw-modal wpcw-mfp-hide">
			<div class="modal-title">
				<h1><?php esc_html_e( 'Create a Webhook', 'wpcw-convertkit' ); ?></h1>
			</div>

			<div class="modal-body">
				<div class="wpcw-form-field first">
					<label for="name" class="label"><?php esc_html_e( 'Webhook Name', 'wpcw-convertkit' ); ?></label>
					<input type="text" id="name" v-model="name" placeholder="<?php esc_html_e( 'Webhook Name', 'wpcw-convertkit' ); ?>"/>
				</div>

				<div class="wpcw-form-field">
					<label for="type" class="label"><?php esc_html_e( 'Webhook Event Type', 'wpcw-convertkit' ); ?></label>

					<select ref="type"
					        v-model="type"
					        id="wpcw-field-convertkitselect-type-dropdown"
					        class="wpcw-field-convertkitselect-type-dropdown wpcw-field-convertkitselect-dropdown"
					        data-placeholder="<?php esc_html_e( 'Select an Event Type', 'wpcw-convertkit' ); ?>"
					        data-allow_clear="true"
					        style="width:100%;">
						<option value=""></option>
						<?php foreach ( get_webhook_events() as $event_key => $event ) { ?>
							<option value="<?php echo esc_attr( $event_key ); ?>"><?php echo esc_attr( get_webhook_event_label( $event_key ) ); ?></option>
						<?php } ?>
					</select>

					<div class="desc"><?php esc_html_e( 'Select the Event that will occur inside ConvertKit.', 'wpcw-convertkit' ); ?></div>
				</div>

				<div v-show="isTagsNeeded" class="wpcw-form-field">
					<label for="type" class="label"><?php esc_html_e( 'ConvertKit Tags', 'wpcw-convertkit' ); ?></label>

					<select id="wpcw-field-convertkitselect-tags-dropdown"
					        class="wpcw-field-convertkitselect-tags-dropdown wpcw-field-convertkitselect-dropdown"
					        data-placeholder="<?php esc_html_e( 'Select a ConvertKit Tag', 'wpcw-convertkit' ); ?>"
					        data-allow_clear="true"
					        v-model="tag"
					        style="width:100%;">
						<option value=""></option>
					</select>

					<div class="desc"><?php esc_html_e( 'Please select the tag that will be used.', 'wpcw-convertkit' ); ?></div>
				</div>

				<div v-show="isFormsNeeded" class="wpcw-form-field">
					<label for="type" class="label"><?php esc_html_e( 'Select a ConvertKit Form', 'wpcw-convertkit' ); ?></label>

					<select id="wpcw-field-convertkitselect-forms-dropdown"
					        class="wpcw-field-convertkitselect-forms-dropdown wpcw-field-convertkitselect-dropdown"
					        data-placeholder="<?php esc_html_e( 'Select a Form', 'wpcw-convertkit' ); ?>"
					        data-allow_clear="true"
					        v-model="tag"
					        style="width:100%;">
						<option value=""></option>
					</select>

					<div class="desc"><?php esc_html_e( 'Please select the form that will be used.', 'wpcw-convertkit' ); ?></div>
				</div>

				<div v-show="isSequencesNeeded" class="wpcw-form-field">
					<label for="type" class="label"><?php esc_html_e( 'Select a ConvertKit Sequence', 'wpcw-convertkit' ); ?></label>

					<select id="wpcw-field-convertkitselect-sequences-dropdown"
					        class="wpcw-field-convertkitselect-sequences-dropdown wpcw-field-convertkitselect-dropdown"
					        data-placeholder="<?php esc_html_e( 'Select a Sequence', 'wpcw-convertkit' ); ?>"
					        data-allow_clear="true"
					        v-model="tag"
					        style="width:100%;">
						<option value=""></option>
					</select>
					<div class="desc"><?php esc_html_e( 'Please select the form that will be used.', 'wpcw-convertkit' ); ?></div>
				</div>

				<div class="wpcw-form-field">
					<button type="button" class="button button-primary" :class="{ 'disabled' : creating }" @click.prevent="addWebhook" @disabled="creating">
						<i class="wpcw-fas left" :class="{ 'wpcw-fa-spinner wpcw-fa-spin' : creating, ' wpcw-fa-plus' : ! creating }" aria-hidden="true"></i>
						{{ creating ? '<?php esc_html_e( 'Creating Webhook...', 'wpcw-convertkit' ); ?>' : '<?php esc_html_e( 'Create Webhook', 'wpcw-convertkit' ); ?>' }}
					</button>
				</div>
			</div>
		</div>
		<table class="convertkit-webhooks-table" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th class="name"><?php esc_html_e( 'Name', 'wpcw-convertkit' ); ?></th>
				<th class="url"><?php esc_html_e( 'Event', 'wpcw-convertkit' ); ?></th>
				<th class="url"><?php esc_html_e( 'Target Url', 'wpcw-convertkit' ); ?></th>
				<th class="actions"><?php esc_html_e( 'Actions', 'wpcw-convertkit' ); ?></th>
			</tr>
			</thead>
			<tbody v-if="webhooks.length > 0">
			<tr v-for="webhook in webhooks" :key="webhook.id">
				<td class="name" v-html="webhook.name"></td>
				<td class="action" v-html="webhook.action"></td>
				<td class="url" v-html="webhook.url"></td>
				<td class="actions">
					<button class="button button-primary button-small"
					        :class="{ 'disabled' : webhook.delete }"
					        @click.prevent="deleteWebhook( webhook )"
					        @disabled="webhook.delete">
						<i class="wpcw-fas left" :class="{ 'wpcw-fa-spinner wpcw-fa-spin' : webhook.delete, ' wpcw-fa-trash' : ! webhook.delete }" aria-hidden="true"></i>
						{{ webhook.delete ? '<?php esc_html_e( 'Deleting...', 'wpcw-convertkit' ); ?>' : '<?php esc_html_e( 'Delete', 'wpcw-convertkit' ); ?>' }}
					</button>
				</td>
			</tr>
			</tbody>
			<tbody v-else>
			<tr>
				<td colspan="4">
					<?php esc_html_e( 'There are no webooks available.', 'wpcw-convertkit' ); ?>
					<a href="#" class="add-webhook-link" :class="{ 'disabled' : create }" @click.prevent="createWebhook" @disabled="create">
						<i class="wpcw-fas wpcw-fa-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create Webhook', 'wpcw-convertkit' ); ?>
					</a>
				</td>
			</tr>
			</tbody>
		</table>
		<div class="add-webhook">
			<button class="button button-primary add-webhook-button" :class="{ 'disabled' : create }" @click.prevent="createWebhook" @disabled="create">
				<i class="wpcw-fas left wpcw-fa-plus" aria-hidden="true"></i>
				<?php esc_html_e( 'Create Webhook', 'wpcw-convertkit' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Course Enqueue Assets.
 *
 * @since 1.0.0
 *
 * @param WP_Screen $admin_screen The admin screen slug.
 */
function course_enqueue_assets( $admin_screen ) {
	if ( ! is_convertkit_enabled() || 'wpcw_course' !== $admin_screen->id ) {
		return;
	}

	wp_enqueue_style( 'wpcw-convertkit-course-css', asset_file( 'course.css', 'css' ), array( 'wpcw-admin' ), WPCW_CONVERTKIT_VERSION );
	wp_enqueue_script( 'wpcw-convertkit-course-js', asset_file( 'course.js', 'js' ), array( 'wpcw-admin' ), WPCW_CONVERTKIT_VERSION, true );

	/**
	 * Action: ConvertKit Course Assets.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $admin_screen The admin screen.
	 */
	do_action( 'wpcw_convertkit_course_assets', $admin_screen );
}
