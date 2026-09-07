<?php
/**
 * Teachers page (post #27) — TASK-WEBSITE.md Phase J "Teachers".
 * Run with: wp --user=1 eval-file /tools/pages/13-teachers.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url   = home_url( '/free-trial/' );
$teacher_ids = array(
	eqc_media_id( 'teacher-hafiz-usman-ali' ),
	eqc_media_id( 'teacher-abdullah-hafeez' ),
	eqc_media_id( 'teacher-sana-fatima' ),
	eqc_media_id( 'teacher-maryam-zahra' ),
);

$hero = eqc_page_hero(
	'Our Qualified Teachers',
	'Learn from dedicated Quran teachers',
	'Our teachers are qualified, experienced and Tajweed-certified. They guide every student with patience, correcting gently and adjusting pace to how that specific student learns best.',
	'users'
);

// ------------------------------------------------------- TEACHER GRID
$teacher_data = array(
	array( 'Hafiz Usman Ali', 'Quran Teacher', array( 'certificate' => '7+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Expert in Kids & Adults' ) ),
	array( 'Abdullah Hafeez', 'Quran Teacher', array( 'certificate' => '5+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Quran Memorization Expert' ) ),
	array( 'Sana Fatima', 'Quran Teacher', array( 'certificate' => '6+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Specialist in Kids Teaching' ) ),
	array( 'Maryam Zahra', 'Quran Teacher', array( 'certificate' => '4+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Quran & Islamic Studies' ) ),
);
$teacher_cards = array();
foreach ( $teacher_data as $i => $t ) {
	if ( ! $teacher_ids[ $i ] ) {
		continue;
	}
	$teacher_cards[] = eqc_teacher_card( $teacher_ids[ $i ], $t[0], $t[1], $t[2] );
}
// Two clearly-marked placeholder slots round out the team page — the
// client mentioned more teachers than the four confirmed profiles we
// have; these are stubs an admin fills in via Elementor, never presented
// as real people (see eqc_teacher_card_stub() for why).
$teacher_cards[] = eqc_teacher_card_stub( 'Add a Teacher' );
$teacher_cards[] = eqc_teacher_card_stub( 'Add a Teacher' );
$grid = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner( '', array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--teachers', 'flex_direction' => 'row' ), $teacher_cards ) ) ),
	)
);

// ------------------------------------------------------- MATCHING PROCESS
$match_steps = array(
	array( 'calendar', 'Tell Us the Basics', "Student's age, current level and preferred timing." ),
	array( 'users', 'We Match a Teacher', 'Based on level, learning style and schedule fit.' ),
	array( 'headset', 'Try the Free Trial', 'Meet the teacher and confirm it feels right.' ),
	array( 'chart-up', 'Adjust if Needed', "Switching teachers is fine if the fit isn't right." ),
);
$match_cards = array();
foreach ( $match_steps as $i => $s ) {
	$match_cards[] = eqc_html(
		'<div class="eqc-card" style="text-align:center;">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<div style="margin-top:0.8em;color:var(--eqc-green-800);">' . eqc_icon_str( $s[0] ) . '</div>'
		. '<h3 style="margin:0.5em 0 0.3em;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">' . esc_html( $s[1] ) . '</h3>'
		. '<p style="margin:0;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">' . esc_html( $s[2] ) . '</p>'
		. '</div>'
	);
}
$matching = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'How Matching Works', "Finding the right teacher for your student", true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $match_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- WHAT TO EXPECT
$expect = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( 'What Students Can Expect', 'A calm, encouraging classroom, every session', true ),
				eqc_container(
					array( 'css_classes' => 'eqc-grid eqc-grid--2col', 'flex_direction' => 'row' ),
					array(
						eqc_trust_tile( 'shield', 'Patient Correction', 'Mistakes are corrected gently, in the moment, without pressure.' ),
						eqc_trust_tile( 'clock', 'Consistent Timing', 'The same teacher and time slot each week wherever possible.' ),
						eqc_trust_tile( 'chart-up', 'Real Progress Reviews', 'Teachers share how a student is actually progressing, honestly.' ),
						eqc_trust_tile( 'headset', 'Easy to Reach', 'Questions between classes are welcome, not an inconvenience.' ),
					)
				),
			)
		),
	)
);

// ------------------------------------------------------- CTA
$cta = eqc_section(
	'eqc-section eqc-section--dark',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_heading( 'Ready to meet your teacher?', 'h2', 'eqc-align-center' ),
				eqc_text( '<p style="text-align:center;color:var(--eqc-cream-100);opacity:0.85;">Book a free trial and we will match a suitable teacher before your first class.</p>' ),
				eqc_container(
					array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
					array( eqc_icon_button( 'calendar', __( 'Book Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ) )
				),
			)
		),
	)
);

eqc_save_elementor_page( 27, array( $hero, $grid, $matching, $expect, $cta ) );
