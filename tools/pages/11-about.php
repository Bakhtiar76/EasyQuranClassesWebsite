<?php
/**
 * About page (post #25) — TASK-WEBSITE.md Phase J "About".
 * Run with: wp --user=1 eval-file /tools/pages/11-about.php
 */
require_once '/tools/elementor-helpers.php';

$boy_img  = eqc_media_id( 'about-child-reading-quran' );
$book_img = eqc_media_id( 'about-quran-open-page' );
$trial_url = home_url( '/free-trial/' );

$hero = eqc_page_hero(
	'About Us',
	'Learning the Quran, built around real life',
	'Easy Quran Classes exists for one reason: to make qualified, personal Quran teaching available to anyone, regardless of where they live or how packed their week already is.'
);

// ------------------------------------------------------- MISSION / PURPOSE
$mission = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-about-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-about-media', 'flex_direction' => 'column' ),
							array(
								eqc_widget(
									'image',
									array(
										'image'        => array( 'id' => $boy_img, 'url' => wp_get_attachment_image_url( $boy_img, 'large' ) ),
										'image_size'   => 'large',
										'_css_classes' => 'eqc-arch-media eqc-arch-media--keel eqc-arch-media--keel-round',
									)
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'shield' ) . ' Our Mission</span>' ),
								eqc_heading( 'Consistent, personal teaching that fits real life', 'h2' ),
								eqc_text(
									'<p>Plenty of apps promise to teach the Quran. Very few replace what a real teacher gives: correction the moment a mistake happens, encouragement that responds to how a specific student is actually doing, and a relationship that keeps a student coming back next week.</p>'
									. '<p>We built Easy Quran Classes around live, 1-to-1 teaching first, and treat scheduling flexibility as the thing that keeps consistency realistic for busy families.</p>'
								),
							)
						),
					)
				),
			)
		),
	)
);

// ------------------------------------------------------- WHO WE SERVE
$audiences = array(
	array( 'graduation-cap', 'Complete Beginners', 'Children and adults starting from the Arabic alphabet itself, with no reading ability assumed.' ),
	array( 'users', 'Families with Multiple Learners', 'Siblings or a parent and child learning on schedules that fit around each other.' ),
	array( 'rehal-quran', 'Returning Adults', 'Adults who read as children but want to correct Tajweed and rebuild consistency now.' ),
	array( 'globe', 'Learners Anywhere', 'Students outside easy reach of a qualified local teacher, in any time zone.' ),
);
$audience_cards = array();
foreach ( $audiences as $a ) {
	$audience_cards[] = eqc_html(
		'<div class="eqc-card" style="text-align:center;">'
		. '<div style="color:var(--eqc-green-800);">' . eqc_icon_str( $a[0] ) . '</div>'
		. '<h3 style="margin:0.6em 0 0.3em;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:700;">' . esc_html( $a[1] ) . '</h3>'
		. '<p style="margin:0;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">' . esc_html( $a[2] ) . '</p>'
		. '</div>'
	);
}
$who_we_serve = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Who We Serve', 'A learning path for every starting point', true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $audience_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- TEACHING APPROACH
$approach_img = eqc_media_id( 'about-quran-open-page' );
$approach = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					// Text-then-media order (opposite of the mission section above)
					// achieves the alternating layout directly — no reverse CSS needed.
					array( 'css_classes' => 'eqc-about-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'headset' ) . ' Our Approach</span>' ),
								eqc_heading( 'Paced to the student, not a fixed curriculum clock', 'h2' ),
								eqc_text(
									'<p>Every student starts with an honest assessment of where they actually are, not where a course outline assumes they should be. From there, teachers adjust pace, revision and difficulty as progress happens.</p>'
								),
								// Icon chip cards, the same component the home hero uses
								// (client review, QA/qa-10092026/20.png) — the plain ticked
								// list read as a bare panel next to the rest of the page.
								eqc_html(
									'<div class="eqc-chip-row eqc-chip-row--cards eqc-chip-row--3">'
									. eqc_chip( 'person-filled', '1-to-1 Live Video', 'Never pre-recorded' )
									. eqc_chip( 'users-filled', 'Male & Female', 'Teachers to choose from' )
									. eqc_chip( 'chart-up', 'Progress Check-Ins', 'With parents and guardians' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group eqc-align-start', 'flex_direction' => 'row' ),
									array( eqc_button( __( 'Book a Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ) )
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-about-media', 'flex_direction' => 'column' ),
							array(
								eqc_widget(
									'image',
									array(
										'image'        => array( 'id' => $approach_img, 'url' => wp_get_attachment_image_url( $approach_img, 'large' ) ),
										'image_size'   => 'large',
										'_css_classes' => 'eqc-arch-media eqc-arch-media--keel eqc-arch-media--keel-round',
									)
								),
							)
						),
					)
				),
			)
		),
	)
);

// A trailing dark CTA section used to close every inner page here, directly
// above the global footer's own dark "Your First Class Is Free" panel
// (footer.php) — same color, same message, zero content between them, so
// the two read as one accidental double-height block rather than two
// intentional moments. Removed in favor of letting the footer's sitewide
// CTA be the one closer; the page's own last section stays the content
// close instead of a second copy of the same pitch.

eqc_save_elementor_page( eqc_page_id( 'about' ), array( $hero, $mission, $who_we_serve, $approach ) );
