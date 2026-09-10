<?php
/**
 * Contact page (post #29) — TASK-WEBSITE.md Phase J "Contact".
 * Run with: wp --user=1 eval-file /tools/pages/15-contact.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );
$faq_url   = home_url( '/faq/' );

$hero = eqc_page_hero(
	'Contact Us',
	"We're here to help",
	'Questions about courses, pricing or scheduling? Send a message or reach us directly. We typically reply within one business day.',
	'mail'
);

// ------------------------------------------------------- CONTACT INFO + FORM
$contact_email   = get_theme_mod( 'eqc_contact_email', 'info@easyquranclasses.com' );
$contact_phone   = get_theme_mod( 'eqc_phone_display', '' );
$contact_address = get_theme_mod( 'eqc_address', '' );

// Dark green panel rather than a second white card: the page was a cream form
// beside a cream box with no colour anywhere (client review, round 9). Same
// treatment as the free-trial aside and the footer CTA.
$contact_info_html =
	'<div class="eqc-panel--invite">'
	. '<h3>Get in <em>touch</em></h3>'
	. '<p>Reach us directly and we will help you choose a course, a teacher and a time that works.</p>'
	. '<ul class="eqc-panel-contact">'
	. '<li>' . eqc_icon_str( 'mail' ) . '<a href="mailto:' . esc_attr( $contact_email ) . '">' . esc_html( $contact_email ) . '</a></li>';
if ( $contact_phone ) {
	$contact_info_html .= '<li>' . eqc_icon_str( 'phone' ) . '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $contact_phone ) ) . '">' . esc_html( $contact_phone ) . '</a></li>';
}
if ( $contact_address ) {
	$contact_info_html .= '<li>' . eqc_icon_str( 'map-pin' ) . '<span>' . esc_html( $contact_address ) . '</span></li>';
}
$contact_info_html .=
	'</ul>'
	. '<div class="eqc-panel-actions">'
	. '<a class="eqc-btn eqc-btn--secondary eqc-btn--whatsapp" href="' . eqc_whatsapp_url( "Assalamu alaikum, I'd like to ask about Easy Quran Classes." ) . '">' . eqc_icon_str( 'whatsapp' ) . ' Chat on WhatsApp</a>'
	. '<a class="eqc-btn eqc-btn--bronze" href="' . esc_url( $trial_url ) . '">' . eqc_icon_str( 'calendar' ) . ' Book a Free Trial</a>'
	. '</div>'
	. '<p class="eqc-panel-foot">Looking for a quick answer instead? Check our <a href="' . esc_url( $faq_url ) . '">FAQ</a>.</p>'
	. '</div>';

// Restates the reply time already promised in the hero; no new claim.
$reassure_html =
	'<ul class="eqc-reassure">'
	. '<li>' . eqc_icon_str( 'clock' ) . '<span><strong>Within one business day.</strong> That is our usual reply time.</span></li>'
	. '<li>' . eqc_icon_str( 'headset' ) . '<span><strong>Talk to a person.</strong> Questions between classes are welcome too.</span></li>'
	. '</ul>';

$contact_section = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_section_heading_el(
					'Send a Message',
					'Ask us <span style="color:var(--eqc-bronze-700)">anything</span>',
					true
				),
				eqc_container(
					array( 'css_classes' => 'eqc-form-grid eqc-form-grid--balanced', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-form-wrap', 'flex_direction' => 'column' ),
							array( eqc_widget( 'shortcode', array( 'shortcode' => '[fluentform id="3"]' ) ) )
						),
						eqc_container(
							array( 'css_classes' => 'eqc-form-aside', 'flex_direction' => 'column' ),
							array( eqc_html( $contact_info_html ), eqc_html( $reassure_html ) )
						),
					)
				),
			)
		),
	)
);

eqc_save_elementor_page( eqc_page_id( 'contact' ), array( $hero, $contact_section ) );
