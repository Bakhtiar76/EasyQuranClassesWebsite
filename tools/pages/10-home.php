<?php
/**
 * Homepage (post #24) — DESIGN.md §17 section blueprint.
 * Run with: wp --user=1 eval-file /tools/pages/10-home.php
 */
require_once '/tools/elementor-helpers.php';

$hero_img    = eqc_media_id( 'hero-online-quran-class' );
$about_img   = eqc_media_id( 'about-quran-open-page' );
$teacher_ids = array(
	eqc_media_id( 'teacher-hafiz-usman-ali' ),
	eqc_media_id( 'teacher-abdullah-hafeez' ),
	eqc_media_id( 'teacher-sana-fatima' ),
	eqc_media_id( 'teacher-maryam-zahra' ),
);
$testi_ids = array(
	eqc_media_id( 'testimonial-aisha-khan' ),
	eqc_media_id( 'testimonial-mohammed-rizwan' ),
	eqc_media_id( 'testimonial-abduallah-omar' ),
);

$trial_url = home_url( '/free-trial/' );

// ---------------------------------------------------------------- 1. HERO
$hero = eqc_section(
	'eqc-section eqc-section--tight eqc-section--cream eqc-section--textured eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_container(
					array(
						'css_classes'      => 'eqc-hero-grid',
						'flex_direction'   => 'row',
					),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-hero-text eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'users' ) . ' ' . esc_html__( 'Trusted by Families Worldwide', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( 'Learn Quran Online with <span style="color:var(--eqc-bronze-700)">Personal Guidance</span>', 'h1' ),
								eqc_text( '<p class="eqc-body-l">1-to-1 live classes with qualified male &amp; female teachers. Flexible timing, personalized learning, and steady progress.</p>' ),
								eqc_html(
									'<div class="eqc-chip-row">'
									. eqc_chip( 'person', '1-to-1 Live Classes' )
									. eqc_chip( 'users', 'Male &amp; Female Teachers' )
									. eqc_chip( 'calendar', 'Flexible Schedule' )
									. eqc_chip( 'chart-up', 'Progress Tracking' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row' ),
									array(
										eqc_icon_button( 'calendar', __( 'Book Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ),
										eqc_icon_button( 'whatsapp', __( 'Chat on WhatsApp', 'easy-quran-classes' ), eqc_whatsapp_url(), 'eqc-btn--secondary' ),
									)
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-hero-media', 'flex_direction' => 'column' ),
							array(
								eqc_widget(
									'image',
									array(
										'image'        => array( 'id' => $hero_img, 'url' => wp_get_attachment_image_url( $hero_img, 'large' ) ),
										'image_size'   => 'large',
										'_css_classes' => 'eqc-arch-media eqc-arch-media--masked',
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

// ------------------------------------------------------- 2. TRUST STRIP
$trust = eqc_section(
	'eqc-section eqc-section--tight eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ),
					array(
						eqc_trust_tile( 'shield', 'Safe & Secure Learning', "Your child's safety is our top priority." ),
						eqc_trust_tile( 'headset', 'Support 7 Days a Week', "We're here to help anytime you need." ),
						eqc_trust_tile( 'globe', 'Students from Many Countries', 'A global community of Quran learners.' ),
						eqc_trust_tile( 'certificate', 'Certificates Available', 'Recognize your progress with achievement.' ),
					)
				),
			)
		),
	)
);

// ------------------------------------------------------- 3. ABOUT
$about = eqc_section(
	'eqc-section eqc-section--cream eqc-section--textured',
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
										'image'        => array( 'id' => $about_img, 'url' => wp_get_attachment_image_url( $about_img, 'large' ) ),
										'image_size'   => 'large',
										'_css_classes' => 'eqc-arch-media eqc-arch-media--masked',
									)
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'book-open' ) . ' ' . esc_html__( 'Why Easy Quran Classes', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( "Learning the Quran shouldn't depend on where you live", 'h2' ),
								eqc_text(
									'<p>Most Muslim families want the same thing: children who can read the Quran properly, and adults who can finally correct the recitation they half-learned as kids. What gets in the way is rarely motivation &mdash; it is distance to a qualified teacher, a packed school run, shift work, or the quiet hesitation of being an adult who still struggles with the basics.</p>'
									. '<p>Easy Quran Classes removes those obstacles. Your teacher joins your screen, at the hour you choose, and works at the pace you set.</p>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ),
									array(
										eqc_html( '<div style="text-align:center"><strong data-eqc-countup="5000" data-eqc-suffix="+" style="font-family:var(--eqc-font-display);font-size:1.6rem;color:var(--eqc-heading)">0</strong><br><span style="font-size:var(--eqc-fs-small);color:var(--eqc-muted)">Students Taught</span></div>' ),
										eqc_html( '<div style="text-align:center"><strong data-eqc-countup="10" data-eqc-suffix="+" style="font-family:var(--eqc-font-display);font-size:1.6rem;color:var(--eqc-heading)">0</strong><br><span style="font-size:var(--eqc-fs-small);color:var(--eqc-muted)">Countries Served</span></div>' ),
										eqc_html( '<div style="text-align:center"><strong style="font-family:var(--eqc-font-display);font-size:1.6rem;color:var(--eqc-heading)">1-to-1</strong><br><span style="font-size:var(--eqc-fs-small);color:var(--eqc-muted)">Private Lessons</span></div>' ),
									)
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group eqc-align-start', 'flex_direction' => 'row' ),
									array(
										eqc_icon_button( 'arrow-right', __( 'More About Us', 'easy-quran-classes' ), home_url( '/about/' ), 'eqc-btn--primary' ),
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

// ------------------------------------------------------- 4. COURSES
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
$courses = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Our Courses', 'Choose the course that matches where <span style="color:var(--eqc-bronze-700)">you are today</span>' ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--courses', 'flex_direction' => 'row' ), $course_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- 5. HOW IT WORKS
$steps = array(
	array( 'calendar', 'Request a Free Trial', 'Tell us the student\'s age, level and availability.' ),
	array( 'users', 'Share Level & Availability', "We'll confirm details and preferred timing." ),
	array( 'person', 'Get Matched with a Teacher', 'A suitable qualified teacher is assigned to you.' ),
	array( 'chart-up', 'Begin Classes & Review Progress', 'Start learning and track progress over time.' ),
);
$step_cards = array();
foreach ( $steps as $i => $s ) {
	$step_cards[] = eqc_html(
		'<div class="eqc-card" style="text-align:center;">'
		. '<span class="eqc-card-index">' . ( $i + 1 ) . '</span>'
		. '<div style="margin-top:0.8em;color:var(--eqc-green-800);">' . eqc_icon_str( $s[0] ) . '</div>'
		. '<h3 style="margin:0.5em 0 0.3em;font-size:var(--eqc-fs-h4);font-family:var(--eqc-font-body);font-weight:600;">' . esc_html( $s[1] ) . '</h3>'
		. '<p style="margin:0;color:var(--eqc-muted);font-size:var(--eqc-fs-small);">' . esc_html( $s[2] ) . '</p>'
		. '</div>'
	);
}
$how_it_works = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'How It Works', 'From first message to first class' ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--trust', 'flex_direction' => 'row' ), $step_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- 6. TEACHERS
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
$teachers = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Our Qualified Teachers', 'Learn from dedicated <span style="color:var(--eqc-bronze-700)">Quran teachers</span>' ) ),
				array( eqc_carousel( $teacher_cards, __( 'Teacher slides', 'easy-quran-classes' ) ) ),
				array(
					eqc_container(
						array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
						array( eqc_button( __( 'View All Teachers', 'easy-quran-classes' ), home_url( '/teachers/' ), 'eqc-btn--primary' ) )
					),
				)
			)
		),
	)
);

// ------------------------------------------------------- 7. PRICING
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
$pricing_panel = eqc_container(
	array( 'css_classes' => 'eqc-pricing-panel', 'flex_direction' => 'column' ),
	array(
		eqc_section_ornaments( 'eqc-corner-motif--sm' ),
		eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--pricing', 'flex_direction' => 'row' ), $pricing_cards ),
	)
);
$pricing = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Pricing', 'Simple monthly pricing, <span style="color:var(--eqc-bronze-700)">no hidden fees</span>' ),
				$pricing_panel,
			)
		),
	)
);

// ------------------------------------------------------- 8. TESTIMONIALS
$testi_data = array(
	array( 'Aisha Khan', 'Lahore, Pakistan', 'My daughter has improved so much in her Quran recitation and Tajweed. The teachers are patient and very supportive.' ),
	array( 'Mohammed Rizwan', 'Hyderabad, India', 'Very organized classes and flexible timings. My son looks forward to every session.' ),
	array( 'Abduallah Omar', 'Nairobi, Kenya', 'The best online Quran academy we have found. My kids are learning with confidence and we can see real improvement.' ),
);
$testi_cards = array();
foreach ( $testi_data as $i => $t ) {
	if ( ! $testi_ids[ $i ] ) {
		continue;
	}
	$testi_cards[] = eqc_testimonial_card( $testi_ids[ $i ], $t[0], $t[1], $t[2], array( 'certificate' => 'Expert Tutors', 'chart-up' => 'Progress Tracking' ) );
}
// Cards advance one at a time, 3 visible on desktop (see eqc_carousel()):
// the 3 real, client-verified reviews first, then clearly-marked
// placeholder stubs (never fabricated quotes/names — see
// eqc_testimonial_card_stub()) so the carousel reads as a real, filled-out
// feature rather than a handful of invented reviews.
$testi_all_cards = array_merge(
	$testi_cards,
	array(
		eqc_testimonial_card_stub(),
		eqc_testimonial_card_stub(),
		eqc_testimonial_card_stub(),
		eqc_testimonial_card_stub(),
		eqc_testimonial_card_stub(),
		eqc_testimonial_card_stub(),
	)
);

$testimonials = eqc_section(
	'eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'What Our Families Say', 'Trusted by families, <span style="color:var(--eqc-gold-600)">loved by students</span>', true ),
				eqc_carousel( $testi_all_cards, __( 'Testimonial slides', 'easy-quran-classes' ) ),
			)
		),
	)
);

// ------------------------------------------------------- 9. FAQ PREVIEW
$faq_preview_items = array(
	array( 'Can beginners start from zero?', 'Yes. Noorani Qaida starts from the Arabic alphabet itself, with no prior reading ability assumed.' ),
	array( 'How does the free trial work?', 'Tell us the student\'s age, level and availability, and we match a suitable teacher for one trial class before any commitment.' ),
	array( 'Are timings flexible?', "Yes. Classes are scheduled around the times that work for your family, not a fixed institutional timetable." ),
);
$faq = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array_merge(
				array( eqc_section_heading_el( 'FAQ', 'Common questions, answered', true ) ),
				array( eqc_faq_group( '', $faq_preview_items ) ),
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

// ------------------------------------------------------- 10. BLOG PREVIEW
$blog_section = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Latest News', 'Directly from the latest news &amp; articles' ),
				eqc_widget( 'shortcode', array( 'shortcode' => '[eqc_latest_posts count="3"]' ) ),
			)
		),
	)
);

eqc_save_elementor_page(
	24,
	array( $hero, $trust, $about, $courses, $how_it_works, $teachers, $pricing, $testimonials, $faq, $blog_section )
);
