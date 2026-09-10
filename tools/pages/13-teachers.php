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
// Two clearly-marked placeholder slots round the team page out to a 3x2 grid
// (client request, QA/qa-9-10.md task 15). These are stubs an admin fills in
// via Elementor, never presented as real people (see eqc_teacher_card_stub()).
for ( $i = 0; $i < 2; $i++ ) {
	$teacher_cards[] = eqc_teacher_card_stub( 'Add a Teacher' );
}
$grid = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
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
	// Styled by .eqc-card--step in components.css rather than inline: the
	// inline sizes left the copy at fs-small inside a full-height .eqc-card,
	// which is the "too small content while the cards are big" the client
	// marked up in QA/qa-10092026/11.png.
	$match_cards[] = eqc_html(
		'<div class="eqc-card eqc-card--step">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<span class="eqc-step-icon">' . eqc_icon_str( $s[0] ) . '</span>'
		. '<h3>' . esc_html( $s[1] ) . '</h3>'
		. '<p>' . esc_html( $s[2] ) . '</p>'
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
						eqc_trust_tile( 'shield-halved', 'Patient Correction', 'Mistakes are corrected gently, in the moment, without pressure.' ),
						eqc_trust_tile( 'clock', 'Consistent Timing', 'The same teacher and time slot each week wherever possible.' ),
						eqc_trust_tile( 'chart-up', 'Real Progress Reviews', 'Teachers share how a student is actually progressing, honestly.' ),
						eqc_trust_tile( 'headset', 'Easy to Reach', 'Questions between classes are welcome, not an inconvenience.' ),
					)
				),
			)
		),
	)
);

// Trailing dark CTA removed — it sat directly above the global footer's own
// dark "Your First Class Is Free" panel (footer.php), same color and
// message with nothing between them, reading as one duplicated block
// rather than two intentional moments. See 11-about.php for the full note.

eqc_save_elementor_page( eqc_page_id( 'teachers' ), array( $hero, $grid, $matching, $expect ) );
