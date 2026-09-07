<?php
/**
 * Online Quran Classes for Kids page (post #33) — TASK-WEBSITE.md Phase J "Kids".
 * Run with: wp --user=1 eval-file /tools/pages/18-kids.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );
$boy_img   = eqc_media_id( 'about-child-reading-quran' );

$hero = eqc_section(
	'eqc-section eqc-section--tight eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-hero-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-hero-text eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'users' ) . ' For Parents' . '</span>' ),
								eqc_heading( 'Online Quran Classes for Kids, Built Around Their Attention Span', 'h1' ),
								eqc_text( '<p class="eqc-body-l">Short, focused, 1-to-1 sessions with a patient teacher — so your child looks forward to class instead of dreading it.</p>' ),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group eqc-align-start', 'flex_direction' => 'row' ),
									array( eqc_icon_button( 'calendar', __( 'Book a Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ) )
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-hero-media', 'flex_direction' => 'column' ),
							array(
								eqc_widget(
									'image',
									array(
										'image'        => array( 'id' => $boy_img, 'url' => wp_get_attachment_image_url( $boy_img, 'large' ) ),
										'image_size'   => 'large',
										'_css_classes' => 'eqc-arch-media',
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

// ------------------------------------------------------- WHO IT SUITS
$suits = array(
	array( 'graduation-cap', 'Complete Beginners', 'Children who have never seen Arabic letters before.' ),
	array( 'book-open', 'Building Confidence', 'Children who can read a little but need patient correction.' ),
	array( 'users', 'Siblings Learning Together', 'Multiple children on schedules that fit around each other.' ),
);
$suit_cards = array();
foreach ( $suits as $s ) {
	$suit_cards[] = eqc_trust_tile( $s[0], $s[1], $s[2] );
}
$who_suits = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Who This Is For', 'Every child starts somewhere different', true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $suit_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- LEARNING EXPERIENCE
$experience = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( 'What a Class Actually Looks Like', 'Calm, encouraging, and never rushed', true ),
				eqc_text(
					'<p style="text-align:center;">Classes are short enough to hold a child\'s attention and structured around gentle repetition rather than pressure. Teachers are experienced with children specifically — praising progress, correcting kindly, and pacing the lesson to how the child is doing that day, not a fixed script.</p>'
				),
			)
		),
	)
);

// ------------------------------------------------------- BEGINNER PATHWAY
$pathway_steps = array(
	array( 'Noorani Qaida', 'Arabic letters and sounds, taught from zero.' ),
	array( 'Quran Reading with Tajweed', 'Reading words and short verses correctly.' ),
	array( 'Islamic Studies', 'Prayer, duas and the basics of the faith alongside reading.' ),
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
				array( eqc_section_heading_el( 'A Simple Starting Pathway', 'Where most children begin', true ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $pathway_cards ) ),
				array(
					eqc_container(
						array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
						array( eqc_button( __( 'See All Courses', 'easy-quran-classes' ), home_url( '/courses/' ), 'eqc-btn--primary' ) )
					),
				)
			)
		),
	)
);

// ------------------------------------------------------- TEACHER MATCHING
$matching = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( 'Matched to a Teacher Your Child Responds To', 'Male and female teachers experienced with children', true ),
				eqc_text( '<p style="text-align:center;">Tell us your child\'s age and personality during the free trial, and we will match a teacher whose pace and style suit them — switching later is always fine if the fit isn\'t right.</p>' ),
			)
		),
	)
);

// ------------------------------------------------------- KIDS FAQ
$faq_items = array(
	array( 'What age can my child start?', 'Most children of school age can begin; the free trial helps a teacher assess readiness.' ),
	array( 'What if my child gets distracted easily?', 'Classes are kept short and interactive specifically because young attention spans are limited — teachers adjust pacing accordingly.' ),
	array( 'Can I stay in the room during class?', 'Yes, parents are welcome to sit in, especially for younger or newer students.' ),
	array( 'Will my child keep the same teacher?', 'We aim to keep the same teacher and time slot consistent each week wherever possible.' ),
);
$faq = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array_merge(
				array( eqc_section_heading_el( 'Questions Parents Often Ask', 'Kids FAQ', true ) ),
				array( eqc_faq_group( '', $faq_items ) )
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
				eqc_heading( "Give your child a calm first Quran class", 'h2', 'eqc-align-center' ),
				eqc_text( '<p style="text-align:center;color:var(--eqc-cream-100);opacity:0.85;">Book a free trial and see how your child responds before committing to anything.</p>' ),
				eqc_container(
					array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
					array( eqc_icon_button( 'calendar', __( 'Book Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ) )
				),
			)
		),
	)
);

eqc_save_elementor_page( 33, array( $hero, $who_suits, $experience, $pathway, $matching, $faq, $cta ) );
