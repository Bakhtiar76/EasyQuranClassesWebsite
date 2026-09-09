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

/*
 * Hero trust row avatars. The reference (Home.jpeg) shows five overlapping
 * circular portraits; the staging library holds four teachers and three
 * testimonial portraits, so five are drawn from both. Decorative — the
 * accessible content is the trust line beside them, so alt is empty.
 */
$hero_avatar_ids = array_slice( array_merge( $teacher_ids, $testi_ids ), 0, 5 );
$hero_avatars    = '';
foreach ( $hero_avatar_ids as $avatar_id ) {
	$hero_avatars .= sprintf(
		'<img src="%s" alt="" width="48" height="48" loading="lazy" decoding="async">',
		esc_url( wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) )
	);
}

// ---------------------------------------------------------------- 1. HERO
// No --ornamented: the reference's hero ground is plain cream with the fine
// allover texture only, no girih corner watermark (home.md finding 11).
$hero = eqc_section(
	'eqc-section eqc-section--hero eqc-section--cream eqc-section--textured',
	array(
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
								// Sentence case in a white pill with a circular icon chip, per the
								// reference; --hero drops the shared eyebrow's uppercase/tracking.
								eqc_html( '<span class="eqc-eyebrow eqc-eyebrow--hero"><span class="eqc-eyebrow-chip">' . eqc_icon_str( 'users' ) . '</span>' . esc_html__( 'Trusted by Families Worldwide', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( 'Learn Quran Online <br>with <span style="color:var(--eqc-bronze-700)">Personal Guidance</span>', 'h1' ),
								eqc_html( '<div class="eqc-hero-rule">' . eqc_divider_svg( 'rule' ) . '</div>' ),
								eqc_text( '<p class="eqc-body-l">1-to-1 live classes with qualified male &amp; female teachers. <br>Flexible timing, personalized learning, and real progress.</p>' ),
								eqc_html(
									'<div class="eqc-chip-row eqc-chip-row--cards">'
									. eqc_chip( 'person', '1-to-1', 'Live Classes' )
									. eqc_chip( 'users', 'Male & Female', 'Teachers' )
									. eqc_chip( 'calendar', 'Flexible', 'Schedule' )
									. eqc_chip( 'chart-up', 'Progress', 'Tracking' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row' ),
									array(
										eqc_icon_button( 'calendar', __( 'Book Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ),
										eqc_icon_button( 'whatsapp', __( 'Chat on WhatsApp', 'easy-quran-classes' ), eqc_whatsapp_url(), 'eqc-btn--secondary' ),
									)
								),
								// Five overlapping avatars, then five gold stars above one trust
								// line. "5,000+" is an unverified client claim, held in ONE place
								// here so replacing it is a single edit - QA/PLACEHOLDER-REGISTER.md.
								eqc_html(
									'<div class="eqc-hero-trust">'
									. '<span class="eqc-avatar-stack">' . $hero_avatars . '</span>'
									. '<span class="eqc-hero-trust-text">'
									. '<span class="eqc-star-row" aria-hidden="true">' . str_repeat( eqc_icon_str( 'star-filled' ), 5 ) . '</span>'
									. '<span>' . esc_html__( 'Trusted by 5,000+ Students & Parents', 'easy-quran-classes' ) . '</span>'
									. '</span></div>'
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
										'_css_classes' => 'eqc-arch-media eqc-arch-media--keel',
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
	'eqc-section eqc-section--truststrip',
	array(
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-grid eqc-grid--trust eqc-trust-panel', 'flex_direction' => 'row' ),
					array(
						eqc_trust_tile( 'shield', 'Safe & Secure | Learning', "Your child's safety is | our top priority" ),
						eqc_trust_tile( 'headset', 'Support 7 Days | A Week', "We're here to help | anytime you need" ),
						// "20+ Countries" is an unverified client claim - register entry.
						eqc_trust_tile( 'globe', 'Students from | 20+ Countries', 'A global community | of Quran learners' ),
						eqc_trust_tile( 'certificate', 'Certificates | Available', 'Recognize your progress | with achievement' ),
					)
				),
			)
		),
	)
);

// ------------------------------------------------------- 3. ABOUT
// Measured against Assests/Home2.jpeg — see QA/design-review/home.md.
// The reference opens this column with an ornament RAIL (rule - rosette -
// rule) spanning its full width, not the pill eyebrow used elsewhere, and
// its heading is a display size larger than the other section headings.
$about = eqc_section(
	'eqc-section eqc-section--about eqc-section--cream eqc-section--textured',
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
								// The reference's collage is three overlapping arch-masked
								// images. Two exist in the staging library; the Arabic
								// alphabet chart does not and is briefed in
								// QA/IMAGE-BRIEF.md rather than faked from a low-res crop.
								eqc_html(
									'<div class="eqc-about-collage">'
									. '<figure class="eqc-about-collage__main eqc-arch-media eqc-arch-media--keel">'
									. wp_get_attachment_image( $about_img, 'large', false, array( 'alt' => 'An open Quran beside a sunlit window' ) )
									. '</figure>'
									. '<figure class="eqc-about-collage__child eqc-arch-media eqc-arch-media--keel">'
									. wp_get_attachment_image( eqc_media_id( 'about-child-reading-quran' ), 'medium_large', false, array( 'alt' => 'A young student reading from the Quran' ) )
									. '</figure>'
									. '</div>'
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-about-text eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<div class="eqc-about-rail">' . eqc_divider_svg( 'section' ) . '</div>' ),
								eqc_heading( "Learning the Quran <br>shouldn't depend on <br>where you live", 'h2', 'eqc-display-heading' ),
								eqc_html( '<div class="eqc-about-rule">' . eqc_divider_svg( 'card' ) . '</div>' ),
								// Copy transcribed verbatim from the reference, including its
								// em dash and its "Nobody else is in the room" closing pair.
								eqc_text(
									'<p>Most Muslim families want the same thing&mdash;children who can read the Quran properly, and adults who can finally correct the recitation they half-learned as kids. What gets in the way is rarely motivation. It is distance to the nearest qualified teacher, a school run that ends at six, shift work, or the quiet embarrassment of being a grown adult who still struggles with the alphabet.</p>'
									. '<p>Easy Quran Classes removes those obstacles. Your teacher comes to your screen, at the hour you choose, and works at the pace you set. Nobody else is in the room. Nobody is watching you make mistakes.</p>',
									'eqc-body-l'
								),
								// Stat tiles: label ABOVE value in the reference, each icon in
								// a rosette-framed disc, hairlines between. "5,000" and "10"
								// are unverified client claims - QA/PLACEHOLDER-REGISTER.md.
								eqc_html(
									'<div class="eqc-about-stats">'
									. eqc_about_stat( 'monitor-play', 'Students Taught', '5,000' )
									. eqc_about_stat( 'globe', 'Countries Served', '10' )
									. eqc_about_stat( 'book-open', 'Private Quran Lessons', '1-To-1' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group eqc-align-start', 'flex_direction' => 'row' ),
									array(
										eqc_icon_button( 'arrow-right', __( 'More About Us', 'easy-quran-classes' ), home_url( '/about/' ), 'eqc-btn--green eqc-btn--icon-disc' ),
										eqc_icon_button( 'phone', __( 'Call Any Time', 'easy-quran-classes' ), 'tel:' . preg_replace( '/[^0-9+]/', '', (string) get_theme_mod( 'eqc_phone_display', '' ) ), 'eqc-btn--secondary' ),
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
	array( '01', 'Noorani Qaida', 'Beginner', 'Arabic letters, sounds and joining, taught from absolute zero. Ends when you can read short Quranic words unaided.' ),
	array( '02', 'Quran Reading With Tajweed', 'Beginner To Advanced', 'Read the Quran fluently and correctly, applying the rules of Tajweed as you go, not as an afterthought.' ),
	array( '03', 'Tajweed Course', 'All Levels', 'Master the rules of Tajweed step by step with practical examples until you recite the Quran with beauty and accuracy.' ),
	array( '04', 'Quran Tafseer', 'Advanced', 'Move from reciting the words to understanding them &mdash; context, meaning and how each passage applies now.' ),
	array( '05', 'Quran Memorization', 'Intermediate', 'A structured Hifz plan with daily new lesson, recent revision and long-term revision paced to your capacity.' ),
	array( '06', 'Islamic Studies', 'All Levels', 'The essentials every Muslim needs &mdash; how to pray correctly, daily Duas, the life of the Prophet (Peace Be Upon Him) and the manners that go with the knowledge.' ),
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
				array( eqc_section_heading_el( 'Our Courses', 'Choose the course that <br>matches where <span style="color:var(--eqc-bronze-700)">you are today</span>', false, 'eqc-eyebrow--plain', 'book-open' ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--courses', 'flex_direction' => 'row' ), $course_cards ) )
			)
		),
	)
);

// ------------------------------------------------------- 6. TEACHERS
$teacher_data = array(
	array( 'Hafiz|Usman Ali', 'Quran Teacher', array( 'certificate' => '7+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Expert in Kids & Adults' ) ),
	array( 'Abdullah|Hafeez', 'Quran Teacher', array( 'certificate' => '5+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Quran Memorization Expert' ) ),
	array( 'Sana|Fatima', 'Quran Teacher', array( 'certificate' => '6+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Specialist in Kids Teaching' ) ),
	array( 'Maryam|Zahra', 'Quran Teacher', array( 'certificate' => '4+ Years Experience', 'graduation-cap' => 'Tajweed Certified', 'users' => 'Quran & Islamic Studies' ) ),
);
$teacher_cards = array();
foreach ( $teacher_data as $i => $t ) {
	if ( ! $teacher_ids[ $i ] ) {
		continue;
	}
	$teacher_cards[] = eqc_teacher_card( $teacher_ids[ $i ], $t[0], $t[1], $t[2] );
}
$teachers = eqc_section(
	'eqc-section eqc-section--teachers eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-teachers-split', 'flex_direction' => 'row' ),
					array(
						// Left column, 268u in the reference: label, three-line
						// heading, ornament, body, three feature tiles, controls.
						eqc_container(
							array( 'css_classes' => 'eqc-teachers-aside', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'users' ) . esc_html__( 'Our Qualified Teachers', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( 'Learn From <br>Dedicated <br><span style="color:var(--eqc-bronze-700)">Quran Teachers</span>', 'h2' ),
								eqc_html( '<div class="eqc-teachers-rule">' . eqc_divider_svg( 'accent' ) . '</div>' ),
								eqc_text( '<p>Our teachers are highly qualified, experienced, and passionate about teaching the Quran. They are here to guide you every step of the way with patience and care.</p>' ),
								eqc_html(
									'<div class="eqc-teachers-features">'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'graduation-cap' ) . '</span><div><p class="eqc-trust-tile-title">Qualified &amp; Experienced</p><p>Well-trained in Tajweed &amp; Quran teaching</p></div></div>'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'person' ) . '</span><div><p class="eqc-trust-tile-title">1-to-1 Personalized Classes</p><p>Focused learning for every student</p></div></div>'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'shield' ) . '</span><div><p class="eqc-trust-tile-title">Safe &amp; Supportive Environment</p><p>Your comfort and progress is our priority</p></div></div>'
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-teachers-controls', 'flex_direction' => 'row' ),
									array(
										eqc_html(
											'<div class="eqc-teachers-nav">'
											. '<button type="button" class="eqc-nav-btn" aria-label="' . esc_attr__( 'Previous teachers', 'easy-quran-classes' ) . '">' . eqc_icon_str( 'chevron-right' ) . '</button>'
											. '<button type="button" class="eqc-nav-btn" aria-label="' . esc_attr__( 'Next teachers', 'easy-quran-classes' ) . '">' . eqc_icon_str( 'chevron-right' ) . '</button>'
											. '</div>'
										),
										eqc_icon_button( 'arrow-right', __( 'View All Teachers', 'easy-quran-classes' ), home_url( '/teachers/' ), 'eqc-btn--green eqc-btn--icon-disc eqc-btn--sm' ),
									)
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-teachers-cards', 'flex_direction' => 'row' ),
							$teacher_cards
						),
					)
				),
			)
		),
	)
);

// ------------------------------------------------------- 7. PRICING
// Five features per plan in the reference, not four. Prices and class counts
// are unverified client claims - QA/PLACEHOLDER-REGISTER.md.
$plan_features = array( '30 Minutes Each Class', '%d Classes Per Month', 'Expert Tutors', 'Monthly Tracking', 'Personalized Focus' );
$plans = array(
	array( '2 Days/Week', 39, 8, false ),
	array( '3 Days/Week', 45, 12, false ),
	array( '4 Days/Week', 59, 16, false ),
	array( '5 Days/Week', 69, 20, true ),
);
$pricing_cards = array();
foreach ( $plans as $p ) {
	$features = $plan_features;
	$features[1] = sprintf( $plan_features[1], $p[2] );
	$pricing_cards[] = eqc_pricing_card( $p[0], $p[1], 'Month', $features, $trial_url, $p[3] );
}
$pricing_panel = eqc_container(
	array( 'css_classes' => 'eqc-pricing-panel', 'flex_direction' => 'column' ),
	array(
		eqc_section_ornaments( 'eqc-corner-motif--sm' ),
		eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--pricing', 'flex_direction' => 'row' ), $pricing_cards ),
	)
);
$pricing = eqc_section(
	'eqc-section eqc-section--pricing eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Pricing', 'Simple monthly pricing, <br>no hidden fees', true, 'eqc-eyebrow--rosette' ),
				$pricing_panel,
				// White strip of four benefits under the cards, hairline-separated.
				eqc_html(
					'<div class="eqc-benefits">'
					. eqc_benefit_tile( 'users', 'Qualified | Male & Female Tutors' )
					. eqc_benefit_tile( 'book-open', 'One-on-One | Live Classes' )
					. eqc_benefit_tile( 'clock', 'Flexible | Schedule' )
					. eqc_benefit_tile( 'shield', 'Safe & Supportive | Learning Environment' )
					. '</div>'
				),
			)
		),
	)
);

// ------------------------------------------------------- 8. TESTIMONIALS
$testi_data = array(
	array( 'Aisha Khan', 'Lahore, Pakistan', 'My daughter has improved so much in her Quran recitation and Tajweed. The teachers are patient and very supportive.' ),
	array( 'Mohammed Rizwan', 'Hyderabad, India', 'Very organized classes and flexible timings. My son looks forward to every session. JazakAllah for the amazing support!' ),
	array( 'Abduallah Omar', 'Nairobi, Kenya', 'The best online Quran academy we have found. My kids are learning with confidence and we can see real improvement.' ),
);
$testi_cards = array();
foreach ( $testi_data as $i => $t ) {
	if ( ! $testi_ids[ $i ] ) {
		continue;
	}
	$testi_cards[] = eqc_testimonial_card( $testi_ids[ $i ], $t[0], $t[1], $t[2], array( 'graduation-cap' => 'Expert Tutors', 'check' => 'Monthly Tracking', 'chart-up' => 'Personalised Focus' ) );
}
// Reference quotes are unverified staging content; see QA/PLACEHOLDER-REGISTER.md.
$testimonials = eqc_section(
	'eqc-section eqc-section--testimonials eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'What Our Families Say', 'Trusted by Families <br><span style="color:var(--eqc-gold-600)">Loved by Students</span>', true, 'eqc-eyebrow--plain', 'quote' ),
				eqc_text( '<p>We are honoured to be part of hundreds of families&rsquo; journey. <br>Here&rsquo;s what they have to say about their experience with us.</p>', 'eqc-testimonials-intro' ),
				eqc_carousel( $testi_cards, __( 'Testimonial slides', 'easy-quran-classes' ) ),
			)
		),
	)
);

// ------------------------------------------------------- 10. BLOG PREVIEW
$blog_section = eqc_section(
	'eqc-section--blog eqc-section eqc-section--surface',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Latest News', 'Directly From the <br><span style="color:var(--eqc-gold-600)">Latest News &amp; Articles</span>', false, 'eqc-eyebrow--plain', 'megaphone' ),
				eqc_widget( 'shortcode', array( 'shortcode' => '[eqc_latest_posts count="3"]' ) ),
			)
		),
	)
);

eqc_save_elementor_page(
	eqc_page_id( 'home' ),
	array( $hero, $trust, $about, $courses, $pricing, $teachers, $testimonials, $blog_section )
);
