<?php
/**
 * FAQ page (post #30) — TASK-WEBSITE.md Phase J "FAQ".
 * Run with: wp --user=1 eval-file /tools/pages/16-faq.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );

$hero = eqc_page_hero(
	'FAQ',
	'Common questions, answered',
	"Everything families usually ask before starting: classes, beginners, children, scheduling and the free trial. Still have a question? Reach out and we'll help directly.",
	'quote'
);

// Shared with 10-home.php's 6-item teaser via eqc_faq_data() — see that
// function's docblock (elementor-helpers.php) for why this moved off a
// hardcoded array here.
$groups = eqc_faq_data();

$group_sections = array();
foreach ( $groups as $title => $items ) {
	$group_sections[] = eqc_faq_group( $title, $items );
}

$faq_content = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner( 'eqc-container--narrow', $group_sections ),
	)
);

// A trailing dark "Didn't find your answer?" CTA used to close this page,
// directly above the global footer's own dark "Your First Class Is Free"
// panel (footer.php) — same color, adjacent, no content between them. Its
// "Contact Us" link stays reachable via the header nav and footer Quick
// Links, so removing the duplicate panel loses no path while fixing the
// double-dark-block heaviness. See 11-about.php for the full note.

eqc_save_elementor_page( eqc_page_id( 'faq' ), array( $hero, $faq_content ) );
