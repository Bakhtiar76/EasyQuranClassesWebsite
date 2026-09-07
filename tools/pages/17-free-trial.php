<?php
/**
 * Free Trial page (post #32) — TASK-WEBSITE.md Phase J "Free Trial".
 * Run with: wp --user=1 eval-file /tools/pages/17-free-trial.php
 */
require_once '/tools/elementor-helpers.php';

$hero = eqc_page_hero(
	'Free Trial',
	'Your first class is free — start this week',
	"Tell us the student's age, level and availability. We'll match a suitable teacher and confirm your trial, usually within a day.",
	'calendar'
);

// ------------------------------------------------------- WHAT HAPPENS NEXT
$steps = array(
	array( 'book-open', 'You Submit This Form', "Share the student's age, level and preferred timing." ),
	array( 'users', 'We Match a Teacher', 'Based on level, learning goals and schedule fit.' ),
	array( 'calendar', 'Trial Is Confirmed', "We'll reach out to confirm the exact date and time." ),
	array( 'headset', 'Meet Your Teacher', 'Join the class and see how it feels — no obligation.' ),
);
$step_cards = array();
foreach ( $steps as $i => $s ) {
	$step_cards[] = eqc_html(
		'<div class="eqc-card" style="text-align:center;">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<div style="margin-top:0.8em;color:var(--eqc-green-800);">' . eqc_icon_str( $s[0] ) . '</div>'
		. '<h3 style="margin:0.5em 0 0.3em;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">' . esc_html( $s[1] ) . '</h3>'
		. '<p style="margin:0;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">' . esc_html( $s[2] ) . '</p>'
		. '</div>'
	);
}
$what_happens = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'What Happens Next', 'From this form to your first class', true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $step_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- FORM + ALTERNATIVE CONTACT
$alt_contact_html =
	'<div class="eqc-card" style="height:100%;">'
	. '<h3 style="margin-top:0;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">Prefer to Talk First?</h3>'
	. '<p style="color:var(--eqc-muted);">If you have questions before requesting a trial, reach out directly and we will help you choose.</p>'
	. '<div style="margin-top:1.2em;display:flex;flex-direction:column;gap:0.75rem;align-items:flex-start;">'
	. '<a class="eqc-btn eqc-btn--secondary" href="' . eqc_whatsapp_url( "Assalamu alaikum, I'd like to ask about a free trial class." ) . '">' . eqc_icon_str( 'whatsapp' ) . ' Chat on WhatsApp</a>'
	. '<a class="eqc-btn eqc-btn--secondary" href="' . esc_url( home_url( '/contact/' ) ) . '">' . eqc_icon_str( 'mail' ) . ' Contact Us</a>'
	. '</div>'
	. '<p style="margin-top:1.5em;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">Have a question first? See our <a href="' . esc_url( home_url( '/faq/' ) ) . '" style="color:var(--eqc-bronze-700);">FAQ</a>.</p>'
	. '</div>';

$form_section = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-about-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-form-wrap', 'flex_direction' => 'column' ),
							array( eqc_widget( 'shortcode', array( 'shortcode' => '[fluentform id="4"]' ) ) )
						),
						eqc_container(
							array( 'css_classes' => '', 'flex_direction' => 'column' ),
							array( eqc_html( $alt_contact_html ) )
						),
					)
				),
			)
		),
	)
);

eqc_save_elementor_page( 32, array( $hero, $what_happens, $form_section ) );
