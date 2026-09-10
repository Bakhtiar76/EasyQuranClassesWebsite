<?php
/**
 * Free Trial page (post #32) — TASK-WEBSITE.md Phase J "Free Trial".
 * Run with: wp --user=1 eval-file /tools/pages/17-free-trial.php
 */
require_once '/tools/elementor-helpers.php';

$hero = eqc_page_hero(
	'Free Trial',
	'Your first class is free',
	"Tell us the student's age, level and availability. We'll match a suitable teacher and confirm your trial, usually within a day.",
	'calendar'
);

// ------------------------------------------------------- WHAT HAPPENS NEXT
$steps = array(
	array( 'clipboard-check', 'You Submit This Form', "Share the student's age, level and preferred timing." ),
	array( 'users', 'We Match a Teacher', 'Based on level, learning goals and schedule fit.' ),
	array( 'calendar', 'Trial Is Confirmed', "We'll reach out to confirm the exact date and time." ),
	array( 'headset', 'Meet Your Teacher', 'Join the class and see how it feels. There is no obligation.' ),
);
$step_cards = array();
foreach ( $steps as $i => $s ) {
	// .eqc-card--step (components.css) rather than inline styles: the icon
	// gets the site's gold seal disc and the copy gets body size, instead of
	// a bare dark glyph over fs-small text in an oversized box.
	$step_cards[] = eqc_html(
		'<div class="eqc-card eqc-card--step">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<span class="eqc-step-icon">' . eqc_icon_str( $s[0] ) . '</span>'
		. '<h3>' . esc_html( $s[1] ) . '</h3>'
		. '<p>' . esc_html( $s[2] ) . '</p>'
		. '</div>'
	);
}
$what_happens = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array_merge(
				array(
					eqc_section_heading_el(
						'What Happens Next',
						'From this form to <span style="color:var(--eqc-bronze-700)">your first class</span>',
						true
					),
				),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $step_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- FORM + ALTERNATIVE CONTACT
// The aside is a dark green panel, not another white card — it is the only
// strong colour on a page that is otherwise a cream form, and it matches the
// footer CTA's treatment so it reads as part of the system.
$alt_contact_html =
	'<div class="eqc-panel--invite">'
	. '<h3>Prefer to <em>talk first?</em></h3>'
	. '<p>If you have questions before requesting a trial, reach out directly and we will help you choose.</p>'
	. '<div class="eqc-panel-actions">'
	. '<a class="eqc-btn eqc-btn--secondary eqc-btn--whatsapp" href="' . eqc_whatsapp_url( "Assalamu alaikum, I'd like to ask about a free trial class." ) . '">' . eqc_icon_str( 'whatsapp' ) . ' Chat on WhatsApp</a>'
	. '<a class="eqc-btn eqc-btn--secondary" href="' . esc_url( home_url( '/contact/' ) ) . '">' . eqc_icon_str( 'mail' ) . ' Contact Us</a>'
	. '</div>'
	. '<p class="eqc-panel-foot">Have a question first? See our <a href="' . esc_url( home_url( '/faq/' ) ) . '">FAQ</a>.</p>'
	. '</div>';

// Fills the column the short aside used to leave empty, and every line here
// restates a promise already made above — no new claim is introduced.
$reassure_html =
	'<ul class="eqc-reassure">'
	. '<li>' . eqc_icon_str( 'check' ) . '<span><strong>No obligation.</strong> Join the class and see how it feels.</span></li>'
	. '<li>' . eqc_icon_str( 'clock' ) . '<span><strong>Usually within a day.</strong> We confirm the exact date and time with you.</span></li>'
	. '<li>' . eqc_icon_str( 'users' ) . '<span><strong>Male and female teachers.</strong> Choose what suits your family.</span></li>'
	. '</ul>';

$form_section = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el(
					'Request Your Trial',
					'Tell us about the <span style="color:var(--eqc-bronze-700)">student</span>',
					true
				),
				eqc_container(
					array( 'css_classes' => 'eqc-form-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-form-wrap', 'flex_direction' => 'column' ),
							array( eqc_widget( 'shortcode', array( 'shortcode' => '[fluentform id="4"]' ) ) )
						),
						eqc_container(
							array( 'css_classes' => 'eqc-form-aside', 'flex_direction' => 'column' ),
							array( eqc_html( $alt_contact_html ), eqc_html( $reassure_html ) )
						),
					)
				),
			)
		),
	)
);

eqc_save_elementor_page( eqc_page_id( 'free-trial' ), array( $hero, $what_happens, $form_section ) );
