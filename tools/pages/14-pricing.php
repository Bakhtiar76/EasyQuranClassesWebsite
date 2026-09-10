<?php
/**
 * Pricing page (post #28) — TASK-WEBSITE.md Phase J "Pricing".
 * Run with: wp --user=1 eval-file /tools/pages/14-pricing.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );

$hero = eqc_page_hero(
	'Pricing',
	'Simple monthly pricing, no hidden fees',
	'One clear structure based on how many days a week your student wants to learn. Every plan includes the same 1-to-1 teaching, expert tutors and monthly progress tracking. The only difference between plans is how many days a week your student attends.',
	'certificate'
);

// ------------------------------------------------------- PLANS
$plans = array(
	array( '2 Days/Week', 39, array( '30 Minutes Each Class', '8 Classes Per Month', 'Expert Tutors', 'Monthly Tracking' ), false ),
	array( '3 Days/Week', 45, array( '30 Minutes Each Class', '12 Classes Per Month', 'Expert Tutors', 'Monthly Tracking' ), false ),
	array( '4 Days/Week', 59, array( '30 Minutes Each Class', '16 Classes Per Month', 'Expert Tutors', 'Monthly Tracking' ), false ),
	array( '5 Days/Week', 69, array( '30 Minutes Each Class', '20 Classes Per Month', 'Expert Tutors', 'Monthly Tracking' ), true ),
);
$pricing_cards = array();
foreach ( $plans as $p ) {
	$pricing_cards[] = eqc_pricing_card( $p[0], $p[1], 'month', $p[2], $trial_url, $p[3] );
}

$inclusions = array(
	array( 'users', 'Qualified Male & Female Tutors' ),
	array( 'rehal-quran', 'One-on-One Live Classes' ),
	array( 'clock', 'Flexible Schedule' ),
	array( 'shield', 'Safe & Supportive Environment' ),
);
$inclusion_html = '<div class="eqc-inclusion-strip">';
foreach ( $inclusions as $inc ) {
	$inclusion_html .= '<span class="eqc-inclusion-item"><span class="eqc-inclusion-icon">' . eqc_icon_str( $inc[0] ) . '</span>' . esc_html( $inc[1] ) . '</span>';
}
$inclusion_html .= '</div>';

$pricing_panel = eqc_container(
	array( 'css_classes' => 'eqc-pricing-panel', 'flex_direction' => 'column' ),
	array(
		eqc_section_ornaments( 'eqc-corner-motif--sm' ),
		eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--pricing', 'flex_direction' => 'row' ), $pricing_cards ),
		eqc_html( $inclusion_html ),
	)
);
$pricing_section = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner( '', array( $pricing_panel ) ),
	)
);

// ------------------------------------------------------- WHAT'S INCLUDED / GUIDANCE
$guidance = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( "What's Included", 'Every plan, no tiers, no upsells', true ),
				eqc_text(
					'<p style="text-align:center;">Every plan gives the same access to a qualified 1-to-1 teacher, the same monthly progress tracking, and the same responsive support. The only choice is how many days a week fits your family\'s schedule. There is no separate premium tier with better teaching; frequency is the only variable.</p>'
					. '<p style="text-align:center;">Not sure how many days to start with? Most beginners start at 2 to 3 days a week and add more once a routine feels comfortable. Your teacher can advise after the free trial.</p>'
				),
			)
		),
	)
);

// ------------------------------------------------------- PRICING FAQ
$faq_items = array(
	array( 'Can I change my plan later?', 'Yes, you can move to a different weekly frequency at any time by contacting us.' ),
	array( 'Is the free trial really free?', 'Yes. The trial class carries no cost and no obligation to continue.' ),
	array( 'What payment methods are accepted?', 'Payment details are confirmed directly with your account manager after the trial class.' ),
	array( 'Is there a contract or minimum commitment?', 'No long-term contract is required; plans are billed monthly.' ),
);
$faq = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array_merge(
				array( eqc_section_heading_el( 'Pricing FAQ', 'Questions about plans and payment', true ) ),
				array( eqc_faq_group( '', $faq_items ) )
			)
		),
	)
);

// Trailing dark CTA removed — it sat directly above the global footer's own
// dark "Your First Class Is Free" panel (footer.php), same color and
// message with nothing between them, reading as one duplicated block
// rather than two intentional moments. See 11-about.php for the full note.

eqc_save_elementor_page( eqc_page_id( 'pricing' ), array( $hero, $pricing_section, $guidance, $faq ) );
