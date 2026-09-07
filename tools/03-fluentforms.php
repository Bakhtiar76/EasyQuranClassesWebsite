<?php
/**
 * Create the Contact and Free Trial Fluent Forms Lite forms directly via
 * Fluent Forms' own models (no plugin, no wp-cli sub-command exists for
 * this) — the Contact form reuses Fluent Forms' own "Basic Contact Form"
 * predefined template; the Free Trial form's field set comes from
 * DESIGN.md §13 / TASK-WEBSITE.md Phase J "Free Trial". Both forms leave
 * email notifications disabled (matching every predefined template's own
 * default) so no real email sends during local QA — entries are still
 * stored in the database, so submissions remain fully verifiable.
 *
 * Idempotent — skips creation if a form with the same title already
 * exists. Run with: wp --user=1 eval-file /tools/03-fluentforms.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp --user=1 eval-file /tools/03-fluentforms.php\n" );
}

if ( ! class_exists( '\FluentForm\App\Models\Form' ) ) {
	WP_CLI::error( 'Fluent Forms is not active.' );
}

use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;

/**
 * Standard formSettings/notifications meta shared by both forms:
 * same-page confirmation message, no restrictions, notifications present
 * but disabled (no real email sends locally).
 */
function eqc_ff_add_standard_meta( $form_id, $confirmation_message ) {
	FormMeta::create(
		array(
			'form_id'  => $form_id,
			'meta_key' => 'formSettings',
			'value'    => wp_json_encode(
				array(
					'confirmation'  => array(
						'redirectTo'           => 'samePage',
						'messageToShow'         => $confirmation_message,
						'customPage'            => null,
						'samePageFormBehavior'  => 'hide_form',
						'customUrl'             => null,
					),
					'restrictions'  => array(
						'limitNumberOfEntries' => array( 'enabled' => false, 'numberOfEntries' => null, 'period' => 'total', 'limitReachedMsg' => 'Maximum number of entries exceeded.' ),
						'scheduleForm'         => array( 'enabled' => false, 'start' => null, 'end' => null, 'pendingMsg' => 'Form submission is not started yet.', 'expiredMsg' => 'Form submission is now closed.' ),
						'requireLogin'         => array( 'enabled' => false, 'requireLoginMsg' => 'You must be logged in to submit the form.' ),
						'denyEmptySubmission'  => array( 'enabled' => false, 'message' => "Sorry, you cannot submit an empty form." ),
					),
					'layout'        => array(
						'labelPlacement'       => 'top',
						'helpMessagePlacement' => 'with_label',
						'errorMessagePlacement'=> 'inline',
						'asteriskPlacement'    => 'asterisk-right',
					),
				)
			),
		)
	);

	FormMeta::create(
		array(
			'form_id'  => $form_id,
			'meta_key' => 'notifications',
			'value'    => wp_json_encode(
				array(
					'name'         => 'Admin Notification Email',
					'sendTo'       => array( 'type' => 'email', 'email' => '{wp.admin_email}', 'field' => '', 'routing' => array() ),
					'fromName'     => '',
					'fromEmail'    => '',
					'replyTo'      => '',
					'bcc'          => '',
					'subject'      => '[Easy Quran Classes] New Form Submission',
					'message'      => '<p>{all_data}</p>',
					'conditionals' => array( 'status' => false, 'type' => 'all', 'conditions' => array() ),
					// Disabled on purpose: local QA must never send real email
					// (CLAUDE.md / TASK-WEBSITE.md §23). Entries are still
					// stored and visible under Fluent Forms -> Entries.
					'enabled'      => false,
					'email_template' => '',
				)
			),
		)
	);
}

/** Simple text/email/textarea field builder matching Fluent Forms' own schema. */
function eqc_ff_field( $element, $name, $label, $required = false, $placeholder = '', $extra_attrs = array(), $extra_settings = array() ) {
	$icon_map = array( 'input_text' => 'ff-edit-text', 'input_email' => 'ff-edit-email', 'textarea' => 'ff-edit-textarea', 'select' => 'ff-edit-select', 'phone' => 'ff-edit-phone' );
	return array(
		'element'       => $element,
		'attributes'    => array_merge(
			array(
				'name'        => $name,
				'value'       => '',
				'id'          => '',
				'class'       => '',
				'placeholder' => $placeholder,
			),
			'input_email' === $element ? array( 'type' => 'email' ) : array(),
			$extra_attrs
		),
		'settings'      => array_merge(
			array(
				'container_class'   => '',
				'label'             => $label,
				'label_placement'   => '',
				'help_message'      => '',
				'validation_rules'  => array(
					'required' => array( 'value' => $required, 'message' => 'This field is required', 'global' => true ),
				),
				'conditional_logics'=> array(),
			),
			$extra_settings
		),
		'editor_options'=> array(
			'title'      => $label,
			'icon_class' => isset( $icon_map[ $element ] ) ? $icon_map[ $element ] : 'ff-edit-text',
			'template'   => 'select' === $element ? 'selectionField' : ( 'textarea' === $element ? 'inputTextarea' : 'inputText' ),
		),
	);
}

/** Select field builder (options as label=>value pairs, matches label). */
function eqc_ff_select( $name, $label, $options, $required = false, $placeholder = '' ) {
	$field = eqc_ff_field( 'select', $name, $label, $required, $placeholder );
	$field['options'] = array_combine( $options, $options );
	return $field;
}

function eqc_ff_submit_button( $text ) {
	return array(
		'uniqElKey'  => 'el_' . wp_generate_password( 13, false ),
		'element'    => 'button',
		'attributes' => array( 'type' => 'submit', 'class' => '' ),
		'settings'   => array(
			'align'           => 'left',
			'button_style'    => 'default',
			'container_class' => '',
			'background_color'=> '#7A432A',
			'button_size'     => 'md',
			'color'           => '#ffffff',
			'button_ui'       => array( 'type' => 'default', 'text' => $text, 'img_url' => '' ),
		),
		'editor_options' => array( 'title' => 'Submit Button' ),
	);
}

/**
 * Create a form if a form with this exact title does not already exist.
 * Returns the (new or existing) form ID.
 */
function eqc_ff_ensure_form( $title, $fields, $submit_text, $confirmation_message ) {
	$existing = Form::where( 'title', $title )->first();
	if ( $existing ) {
		WP_CLI::log( "Form already exists: {$title} (#{$existing->id})" );
		return $existing->id;
	}

	$form_fields = array(
		'fields'       => $fields,
		'submitButton' => eqc_ff_submit_button( $submit_text ),
	);

	$form = Form::create(
		array(
			'title'       => $title,
			'form_fields' => wp_json_encode( $form_fields ),
			'status'      => 'published',
			'type'        => 'form',
			'created_by'  => get_current_user_id(),
		)
	);

	eqc_ff_add_standard_meta( $form->id, $confirmation_message );

	WP_CLI::log( "Created form: {$title} -> #{$form->id}" );
	return $form->id;
}

// --------------------------------------------------------------- CONTACT FORM
$contact_fields = array(
	eqc_ff_field( 'input_text', 'full_name', 'Full Name', true, 'Your name' ),
	eqc_ff_field( 'input_email', 'email', 'Email Address', true, 'you@example.com' ),
	eqc_ff_field( 'input_text', 'subject', 'Subject', false, 'What is this about?' ),
	eqc_ff_field( 'textarea', 'message', 'Message', true, 'How can we help?' ),
);
$contact_form_id = eqc_ff_ensure_form(
	'Contact Form',
	$contact_fields,
	'Send Message',
	'<p>Thank you for reaching out. We will get back to you shortly.</p>'
);

// ------------------------------------------------------------ FREE TRIAL FORM
$trial_fields = array(
	eqc_ff_field( 'input_text', 'student_name', "Student's Name", true, "Student's full name" ),
	eqc_ff_select( 'age_range', 'Age Range', array( 'Under 7', '7 - 12', '13 - 17', '18 and over' ), true, '- Select Age Range -' ),
	eqc_ff_select( 'current_level', 'Current Level', array( 'Complete Beginner', 'Some Reading Ability', 'Confident Reader', 'Memorization Stage' ), true, '- Select Current Level -' ),
	eqc_ff_select( 'preferred_course', 'Preferred Course', array( 'Noorani Qaida', 'Quran Reading with Tajweed', 'Tajweed Course', 'Quran Tafseer', 'Quran Memorization', 'Islamic Studies', 'Not Sure Yet' ), true, '- Select a Course -' ),
	eqc_ff_field( 'input_text', 'availability', 'Preferred Days / Time', true, 'e.g. Weekday evenings' ),
	eqc_ff_field( 'input_text', 'country_timezone', 'Country / Time Zone', true, 'e.g. Pakistan, GMT+5' ),
	eqc_ff_field( 'input_text', 'guardian_name', 'Parent / Guardian Name', false, 'If the student is a minor' ),
	eqc_ff_field( 'input_email', 'email', 'Email Address', true, 'you@example.com' ),
	eqc_ff_field( 'input_text', 'whatsapp_phone', 'Phone / WhatsApp Number', true, 'Include country code' ),
	eqc_ff_field( 'textarea', 'notes', 'Anything Else We Should Know?', false, 'Optional notes' ),
);
$trial_form_id = eqc_ff_ensure_form(
	'Free Trial Request',
	$trial_fields,
	'Request Free Trial',
	"<p>Thank you! We'll confirm your free trial and match a suitable teacher shortly.</p>"
);

WP_CLI::success( "Contact Form ID: {$contact_form_id}, Free Trial Form ID: {$trial_form_id}" );
