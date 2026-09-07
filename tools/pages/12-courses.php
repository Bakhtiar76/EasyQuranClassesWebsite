<?php
/**
 * Courses page (post #26) — TASK-WEBSITE.md Phase J "Courses".
 * Run with: wp --user=1 eval-file /tools/pages/12-courses.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );

$hero = eqc_page_hero(
	'Our Courses',
	'Choose the course that matches where you are today',
	'Six focused pathways, from the Arabic alphabet through Tajweed, memorization and Islamic Studies. Every course is taught 1-to-1, so pace and depth adjust to the student in front of the teacher.',
	'graduation-cap'
);

// ------------------------------------------------------- COURSE GRID
$course_data = array(
	array( '01', 'Noorani Qaida', 'Beginner', 'Arabic letters, sounds and joining, taught from absolute zero, until short Quranic words can be read unaided.' ),
	array( '02', 'Quran Reading with Tajweed', 'Beginner to Advanced', 'Read the Quran fluently and correctly, applying the rules of Tajweed as you go, not as an afterthought.' ),
	array( '03', 'Tajweed Course', 'All Levels', 'Master the rules of Tajweed step by step with practical examples until recitation is both accurate and clear.' ),
	array( '04', 'Quran Tafseer', 'Advanced', 'Move from reciting the words to understanding them: context, meaning, and how each passage applies today.' ),
	array( '05', 'Quran Memorization', 'Intermediate', 'A structured Hifz plan with daily new lessons, recent revision and long-term revision paced to your capacity.' ),
	array( '06', 'Islamic Studies', 'All Levels', 'The essentials every Muslim needs: correct prayer, daily duas, seerah, and the manners that go with the knowledge.' ),
);
$course_cards = array();
foreach ( $course_data as $i => $c ) {
	$course_cards[] = eqc_course_card( $c[0], $c[1], '(' . $c[2] . ')', $c[3], $trial_url, $i );
}
$courses_grid = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner( '', array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--courses', 'flex_direction' => 'row' ), $course_cards ) ) ),
	)
);

// ------------------------------------------------------- CHOOSING GUIDANCE
$guide_rows = array(
	array( 'New to Arabic letters?', 'Start with Noorani Qaida.' ),
	array( 'Can already read, but not confidently?', 'Start with Quran Reading with Tajweed.' ),
	array( 'Reading is solid, want the rules formalized?', 'Take the Tajweed Course.' ),
	array( 'Want to understand meaning, not just recite?', 'Move into Quran Tafseer.' ),
	array( 'Working toward memorizing the Quran?', 'Join the Quran Memorization pathway.' ),
	array( 'Want the fundamentals of the faith too?', 'Add Islamic Studies alongside any course.' ),
);
$guide_html = '<div class="eqc-card" style="padding:0;overflow:hidden;">';
foreach ( $guide_rows as $i => $row ) {
	$border = $i < count( $guide_rows ) - 1 ? 'border-bottom:1px solid var(--eqc-border);' : '';
	$guide_html .= '<div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:space-between;align-items:center;padding:1.1rem 1.5rem;' . $border . '">'
		. '<span style="font-weight:600;color:var(--eqc-heading);">' . esc_html( $row[0] ) . '</span>'
		. '<span style="color:var(--eqc-bronze-700);display:flex;align-items:center;gap:0.5em;font-weight:600;">' . eqc_icon_str( 'arrow-right' ) . esc_html( $row[1] ) . '</span>'
		. '</div>';
}
$guide_html .= '</div>';

$choosing = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( 'Not Sure Where to Start?', 'A quick way to choose the right course', true ),
				eqc_html( $guide_html ),
			)
		),
	)
);

// ------------------------------------------------------- BEGINNER PATHWAY
$pathway_steps = array(
	array( 'Noorani Qaida', 'Arabic letters, sounds and joining from zero.' ),
	array( 'Quran Reading with Tajweed', 'Fluent, correct reading with Tajweed rules applied.' ),
	array( 'Tajweed Course', 'Formalize the rules with focused practice.' ),
	array( 'Quran Tafseer or Memorization', 'Go deeper into meaning, or begin Hifz.' ),
);
$pathway_cards = array();
foreach ( $pathway_steps as $i => $p ) {
	$pathway_cards[] = eqc_html(
		'<div class="eqc-card" style="text-align:center;">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<h3 style="margin:0.6em 0 0.3em;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">' . esc_html( $p[0] ) . '</h3>'
		. '<p style="margin:0;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">' . esc_html( $p[1] ) . '</p>'
		. '</div>'
	);
}
$pathway = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Beginner Pathway', 'A natural order for starting from zero', true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $pathway_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- FAQ PREVIEW
$faq_items = array(
	array( 'Can I take more than one course at a time?', 'Yes, many students pair Islamic Studies with a Quran course, or Tajweed with Memorization once reading is fluent.' ),
	array( 'How long does each course take?', 'It depends on the student\'s pace and starting point; teachers review progress regularly rather than following a fixed timeline.' ),
	array( 'Can I switch courses later?', 'Yes. Talk to your teacher or contact us and we\'ll adjust your plan.' ),
);
$faq = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array_merge(
				array( eqc_section_heading_el( 'Course FAQ', 'Common questions about courses', true ) ),
				array( eqc_faq_group( '', $faq_items ) ),
				array(
					eqc_container(
						array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
						array( eqc_button( __( 'View All FAQs', 'easy-quran-classes' ), home_url( '/faq/' ), 'eqc-btn--primary' ) )
					),
				)
			)
		),
	)
);

eqc_save_elementor_page( 26, array( $hero, $courses_grid, $choosing, $pathway, $faq ) );
