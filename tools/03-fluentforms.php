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

/**
 * Select field builder. Pass a plain list for simple options (value === label),
 * or an explicit value=>label map (keys not 0..n-1) for e.g. dial codes.
 */
function eqc_ff_select( $name, $label, $options, $required = false, $placeholder = '' ) {
	$field    = eqc_ff_field( 'select', $name, $label, $required, $placeholder );
	$is_assoc = array_keys( $options ) !== range( 0, count( $options ) - 1 );
	$field['options'] = $is_assoc ? $options : array_combine( $options, $options );
	return $field;
}

/**
 * Fluent Forms' native Country dropdown (SelectCountry component). With
 * active_list = 'all' the component populates the full ISO country list at
 * render time, so no country names are hardcoded here.
 */
function eqc_ff_select_country( $name, $label, $required = false, $placeholder = '' ) {
	return array(
		'element'        => 'select_country',
		'attributes'     => array(
			'name'        => $name,
			'value'       => '',
			'id'          => '',
			'class'       => '',
			'placeholder' => $placeholder,
		),
		'settings'       => array(
			'container_class'   => '',
			'label'             => $label,
			'admin_field_label' => '',
			'label_placement'   => '',
			'help_message'      => '',
			'enable_select_2'   => 'no',
			'validation_rules'  => array(
				'required' => array( 'value' => $required, 'message' => 'This field is required', 'global' => true ),
			),
			'country_list'      => array(
				'active_list'  => 'all',
				'visible_list' => array(),
				'hidden_list'  => array(),
			),
			'conditional_logics' => array(),
		),
		'options'        => array( 'US' => 'United States of America' ),
		'editor_options' => array(
			'title'      => $label,
			'element'    => 'country-list',
			'icon_class' => 'ff-edit-country',
			'template'   => 'selectCountry',
		),
	);
}

/**
 * Country dial-code options for the Free Trial phone field (value=>label).
 * A broad spread across every region — Fluent Forms Lite has no native
 * international phone field, so this is a plain select.
 */
function eqc_ff_dial_codes() {
	return array(
		'+93'  => 'Afghanistan (+93)',
		'+61'  => 'Australia (+61)',
		'+43'  => 'Austria (+43)',
		'+973' => 'Bahrain (+973)',
		'+880' => 'Bangladesh (+880)',
		'+32'  => 'Belgium (+32)',
		'+55'  => 'Brazil (+55)',
		'+86'  => 'China (+86)',
		'+45'  => 'Denmark (+45)',
		'+20'  => 'Egypt (+20)',
		'+358' => 'Finland (+358)',
		'+33'  => 'France (+33)',
		'+49'  => 'Germany (+49)',
		'+30'  => 'Greece (+30)',
		'+91'  => 'India (+91)',
		'+62'  => 'Indonesia (+62)',
		'+964' => 'Iraq (+964)',
		'+353' => 'Ireland (+353)',
		'+39'  => 'Italy (+39)',
		'+81'  => 'Japan (+81)',
		'+962' => 'Jordan (+962)',
		'+254' => 'Kenya (+254)',
		'+965' => 'Kuwait (+965)',
		'+961' => 'Lebanon (+961)',
		'+60'  => 'Malaysia (+60)',
		'+52'  => 'Mexico (+52)',
		'+212' => 'Morocco (+212)',
		'+977' => 'Nepal (+977)',
		'+31'  => 'Netherlands (+31)',
		'+64'  => 'New Zealand (+64)',
		'+234' => 'Nigeria (+234)',
		'+47'  => 'Norway (+47)',
		'+968' => 'Oman (+968)',
		'+92'  => 'Pakistan (+92)',
		'+63'  => 'Philippines (+63)',
		'+48'  => 'Poland (+48)',
		'+351' => 'Portugal (+351)',
		'+974' => 'Qatar (+974)',
		'+7'   => 'Russia (+7)',
		'+966' => 'Saudi Arabia (+966)',
		'+65'  => 'Singapore (+65)',
		'+27'  => 'South Africa (+27)',
		'+82'  => 'South Korea (+82)',
		'+34'  => 'Spain (+34)',
		'+94'  => 'Sri Lanka (+94)',
		'+46'  => 'Sweden (+46)',
		'+41'  => 'Switzerland (+41)',
		'+66'  => 'Thailand (+66)',
		'+90'  => 'Turkey (+90)',
		'+971' => 'United Arab Emirates (+971)',
		'+44'  => 'United Kingdom (+44)',
		'+1'   => 'United States / Canada (+1)',
		'+380' => 'Ukraine (+380)',
		'+84'  => 'Vietnam (+84)',
	);
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
	$form_fields = array(
		'fields'       => $fields,
		'submitButton' => eqc_ff_submit_button( $submit_text ),
	);

	$existing = Form::where( 'title', $title )->first();
	if ( $existing ) {
		// Converge the field set of an already-created form to match this code
		// (IDs stay the same so the [fluentform id="N"] shortcodes still
		// resolve). formSettings / notifications meta is left untouched.
		$existing->form_fields = wp_json_encode( $form_fields );
		$existing->save();
		WP_CLI::log( "Updated form: {$title} (#{$existing->id})" );
		return $existing->id;
	}

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
// Field-level format rules (letters-only, digits-only, strict email, min
// length) are enforced client-side in assets/js/eqc-forms.js; Fluent Forms
// keeps "required" as the server-side backstop.
$contact_fields = array(
	eqc_ff_field( 'input_text', 'full_name', 'Full Name', true, 'Enter your full name' ),
	eqc_ff_field( 'input_email', 'email', 'Email Address', true, 'you@example.com' ),
	eqc_ff_field( 'input_text', 'subject', 'Subject', false, 'What is your message about?' ),
	eqc_ff_field( 'textarea', 'message', 'Message', true, 'Write your message here' ),
);
$contact_form_id = eqc_ff_ensure_form(
	'Contact Form',
	$contact_fields,
	'Send Message',
	'<p>Thank you for reaching out. We will get back to you shortly.</p>'
);

// ------------------------------------------------------------ FREE TRIAL FORM
// Format rules (name = letters only min 3, phone = digits only, strict email)
// live in assets/js/eqc-forms.js. Country is Fluent Forms' native country
// dropdown; the dial code is a plain select (no intl phone field in FF Lite).
$trial_fields = array(
	eqc_ff_field( 'input_text', 'student_name', "Student's Name", true, "Enter the student's full name" ),
	eqc_ff_select( 'age_range', 'Age Range', array( 'Under 7', '7 - 12', '13 - 17', '18 and over' ), true, 'Select an age range' ),
	eqc_ff_select( 'current_level', 'Current Level', array( 'Complete Beginner', 'Some Reading Ability', 'Confident Reader', 'Memorization Stage' ), true, 'Select the current level' ),
	eqc_ff_select( 'preferred_course', 'Preferred Course', array( 'Noorani Qaida', 'Quran Reading with Tajweed', 'Tajweed Course', 'Quran Tafseer', 'Quran Memorization', 'Islamic Studies', 'Not Sure Yet' ), true, 'Select a course' ),
	eqc_ff_select_country( 'country', 'Country', true, 'Select your country' ),
	eqc_ff_field( 'input_text', 'guardian_name', 'Parent / Guardian Name', false, "Parent or guardian's name" ),
	eqc_ff_field( 'input_email', 'email', 'Email Address', true, 'you@example.com' ),
	eqc_ff_select( 'phone_country_code', 'Country Code', eqc_ff_dial_codes(), true, 'Select country code' ),
	eqc_ff_field( 'input_text', 'whatsapp_phone', 'Phone / WhatsApp Number', true, 'e.g. 3001234567' ),
);
$trial_form_id = eqc_ff_ensure_form(
	'Free Trial Request',
	$trial_fields,
	'Request Free Trial',
	"<p>Thank you! We'll confirm your free trial and match a suitable teacher shortly.</p>"
);

WP_CLI::success( "Contact Form ID: {$contact_form_id}, Free Trial Form ID: {$trial_form_id}" );
