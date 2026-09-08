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
	'Questions about courses, pricing or scheduling? Send a message or reach us directly — we typically reply within one business day.',
	'mail'
);

// ------------------------------------------------------- CONTACT INFO + FORM
$contact_email = get_theme_mod( 'eqc_contact_email', 'info@easyquranclasses.com' );
$contact_phone = get_theme_mod( 'eqc_phone_display', '' );

$contact_info_html =
	'<div class="eqc-card" style="height:100%;">'
	. '<h3 style="margin-top:0;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">Get in Touch</h3>'
	. '<ul class="eqc-footer-contact" style="margin-top:1.2em;">'
	. '<li>' . eqc_icon_str( 'mail' ) . '<span><a href="mailto:' . esc_attr( $contact_email ) . '" style="color:inherit;text-decoration:none;">' . esc_html( $contact_email ) . '</a></span></li>'
	. '<li>' . eqc_icon_str( 'phone' ) . '<span>' . esc_html( $contact_phone ) . '</span></li>'
	. '</ul>'
	. '<div style="margin-top:1.5em;">'
	. '<a class="eqc-btn eqc-btn--secondary" href="' . eqc_whatsapp_url( "Assalamu alaikum, I'd like to ask about Easy Quran Classes." ) . '">' . eqc_icon_str( 'whatsapp' ) . ' Chat on WhatsApp</a>'
	. '</div>'
	. '<p style="margin-top:1.5em;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">Looking for a quick answer instead? Check our <a href="' . esc_url( $faq_url ) . '" style="color:var(--eqc-bronze-700);">FAQ</a> or <a href="' . esc_url( $trial_url ) . '" style="color:var(--eqc-bronze-700);">book a free trial</a> directly.</p>'
	. '</div>';

$contact_section = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-about-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => '', 'flex_direction' => 'column' ),
							array( eqc_html( $contact_info_html ) )
						),
						eqc_container(
							array( 'css_classes' => 'eqc-form-wrap', 'flex_direction' => 'column' ),
							array( eqc_widget( 'shortcode', array( 'shortcode' => '[fluentform id="3"]' ) ) )
						),
					)
				),
			)
		),
	)
);

eqc_save_elementor_page( eqc_page_id( 'contact' ), array( $hero, $contact_section ) );
