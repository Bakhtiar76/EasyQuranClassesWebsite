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
								eqc_html( '<span class="eqc-eyebrow eqc-eyebrow--hero"><span class="eqc-eyebrow-chip">' . eqc_icon_str( 'users-filled' ) . '</span>' . esc_html__( 'Trusted by Families Worldwide', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( 'Learn Quran Online <br>with <span style="color:var(--eqc-bronze-700)">Personal Guidance</span>', 'h1' ),
								eqc_html( '<div class="eqc-hero-rule">' . eqc_divider_svg( 'rule' ) . '</div>' ),
								eqc_text( '<p class="eqc-body-l">1-to-1 live classes with qualified male &amp; female teachers. <br>Flexible timing, personalized learning, and real progress.</p>' ),
								eqc_html(
									'<div class="eqc-chip-row eqc-chip-row--cards">'
									. eqc_chip( 'person-filled', '1-to-1', 'Live Classes' )
									. eqc_chip( 'users-filled', 'Male & Female', 'Teachers' )
									. eqc_chip( 'calendar', 'Flexible', 'Schedule' )
									. eqc_chip( 'chart-up', 'Progress', 'Tracking' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row' ),
									array(
										eqc_icon_button( 'calendar', __( 'Book Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--bronze' ),
										eqc_icon_button( 'whatsapp', __( 'Chat on WhatsApp', 'easy-quran-classes' ), eqc_whatsapp_url(), 'eqc-btn--secondary eqc-btn--whatsapp' ),
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
						eqc_trust_tile( 'shield-halved', 'Safe & Secure | Learning', "Your child's safety is | our top priority" ),
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
$alphabet_letters = array( 'ا', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي' );
$alphabet_chart = '<div class="eqc-alphabet-panel" role="img" aria-label="Arabic alphabet learning chart"><div class="eqc-alphabet-grid" lang="ar" dir="rtl" aria-hidden="true">';
foreach ( $alphabet_letters as $letter ) {
	$alphabet_chart .= '<span>' . esc_html( $letter ) . '</span>';
}
$alphabet_chart .= '</div></div>';
$about = eqc_section(
	'eqc-section eqc-section--about eqc-section--cream eqc-section--ornamented',
	array(
		// The section already carried --ornamented but never emitted the motifs,
		// so its background was bare. Home2.jpeg puts the arabesque top-left,
		// above the collage, with a quieter answer bottom-right.
		eqc_section_ornaments( '', array( 'tl', 'br' ) ),
		eqc_inner(
			'',
			array(
				eqc_container(
					array( 'css_classes' => 'eqc-about-grid', 'flex_direction' => 'row' ),
					array(
						eqc_container(
							array( 'css_classes' => 'eqc-about-media', 'flex_direction' => 'column' ),
							array(
								// Original alphabet typesetting stays crisp at every size.
								eqc_html(
									'<div class="eqc-about-collage">'
									// --keel-round: the About collage closes the arch's foot with a
									// curve (client review, QA/qa-10092026/14.png). The hero above
									// keeps the reference's square jambs.
									. '<figure class="eqc-about-collage__main eqc-arch-media eqc-arch-media--keel eqc-arch-media--keel-round">'
									. wp_get_attachment_image( $about_img, 'large', false, array( 'alt' => 'An open Quran beside a sunlit window' ) )
									. '</figure>'
									. '<div class="eqc-about-collage__alphabet">' . $alphabet_chart . '</div>'
									. '<figure class="eqc-about-collage__child eqc-arch-media eqc-arch-media--cartouche">'
									. wp_get_attachment_image( eqc_media_id( 'about-child-reading-quran' ), 'medium_large', false, array( 'alt' => 'A young student reading from the Quran' ) )
									. '</figure>'
									. '</div>'
								),
							)
						),
						eqc_container(
							array( 'css_classes' => 'eqc-about-text eqc-align-start', 'flex_direction' => 'column' ),
							array(
								eqc_html( '<div class="eqc-ornament-rail eqc-about-rail" aria-hidden="true">' . eqc_get_svg_asset( 'rosette-simple' ) . '</div>' ),
								eqc_heading( "Learning the Quran <br>shouldn't depend on <br>where you live", 'h2', 'eqc-display-heading' ),
								eqc_html( '<div class="eqc-about-rule">' . eqc_divider_svg( 'about' ) . '</div>' ),
								// Copy transcribed verbatim from the reference, including its
								// em dash and its "Nobody else is in the room" closing pair.
								eqc_text(
									'<p>Most Muslim families want the same thing. They want children who can read the Quran properly, and adults who can finally correct the recitation they half-learned as children. What gets in the way is rarely motivation. It is distance to the nearest qualified teacher, a school run that ends at six, shift work, or the quiet embarrassment of being a grown adult who still struggles with the alphabet.</p>'
									. '<p>Easy Quran Classes removes those obstacles. Your teacher comes to your screen, at the hour you choose, and works at the pace you set. Nobody else is in the room. Nobody is watching you make mistakes.</p>',
									'eqc-body-l'
								),
								// Stat tiles: label ABOVE value in the reference, each icon in
								// a rosette-framed disc, hairlines between. "5,000" and "10"
								// are unverified client claims - QA/PLACEHOLDER-REGISTER.md.
								eqc_html(
									'<div class="eqc-about-stats">'
									. eqc_about_stat( 'presenter', 'Students Taught', '5,000' )
									. eqc_about_stat( 'globe', 'Countries Served', '10' )
									. eqc_about_stat( 'rehal-quran', 'Private Quran Lessons', '1-To-1' )
									. '</div>'
								),
								eqc_container(
									array( 'css_classes' => 'eqc-btn-group eqc-align-start', 'flex_direction' => 'row' ),
									array(
										// Was an arrow-icon "More About Us" link to /about/; client
										// asked for it to become a Free Trial CTA instead (BugDrop
										// re-report, 2026-09-14). Built with eqc_icon_button() (not
										// eqc_button()) so it shares the exact same widget shape as
										// "Call Any Time" below — the client separately asked for a
										// leading icon here to match that button, and matching
										// wrapper shapes is what lets one CSS rule size both equally.
										eqc_icon_button( 'gift-filled', __( 'Book a Free Trial', 'easy-quran-classes' ), $trial_url, 'eqc-btn--green' ),
										// Routed to WhatsApp on client instruction (round 6). The old
										// `tel:` target was built from the eqc_phone_display theme mod,
										// which is an unset placeholder on this install — so the link
										// resolved to a bare "tel:" and went nowhere.
										// Client asked for this to read WhatsApp-brand green (#1EBE5A)
										// at rest with a hover effect (BugDrop re-report, 2026-09-14) —
										// --secondary + --whatsapp is the site's existing "always-on
										// WhatsApp green" skin (also used by "Chat on WhatsApp"), and
										// this button already links to WhatsApp, so it's the correct
										// variant rather than --primary (which stays dark green for
										// the site's other default buttons, e.g. "View All FAQs").
										eqc_icon_button( 'phone', __( 'Call Any Time', 'easy-quran-classes' ), eqc_whatsapp_url(), 'eqc-btn--secondary eqc-btn--whatsapp' ),
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
	array( '04', 'Quran Tafseer', 'Advanced', 'Move from reciting the words to understanding them: context, meaning and how each passage applies now.' ),
	array( '05', 'Quran Memorization', 'Intermediate', 'A structured Hifz plan with daily new lesson, recent revision and long-term revision paced to your capacity.' ),
	array( '06', 'Islamic Studies', 'All Levels', 'The essentials every Muslim needs: how to pray correctly, daily Duas, the life of the Prophet (Peace Be Upon Him) and the manners that go with the knowledge.' ),
);
$course_cards = array();
foreach ( $course_data as $i => $c ) {
	$course_cards[] = eqc_course_card( $c[0], $c[1], '(' . $c[2] . ')', $c[3], $trial_url, $i );
}
$courses = eqc_section(
	'eqc-section eqc-section--courses eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array_merge(
				array( eqc_section_heading_el( 'Our Courses', 'Choose the course that <br>matches where <span style="color:var(--eqc-bronze-700)">you are today</span>', false, 'eqc-eyebrow--plain', 'rehal-quran' ) ),
				array( eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--courses', 'flex_direction' => 'row' ), $course_cards ) ),
				array( eqc_html( '<div class="eqc-ornament-rail eqc-courses-closing" aria-hidden="true">' . eqc_get_svg_asset( 'rosette-reviews' ) . '</div>' ) )
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
	'eqc-section eqc-section--teachers eqc-section--surface',
	array(
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
								eqc_html( '<span class="eqc-eyebrow">' . eqc_icon_str( 'users-filled' ) . esc_html__( 'Our Qualified Teachers', 'easy-quran-classes' ) . '</span>' ),
								eqc_heading( 'Learn From <br>Dedicated <br><span style="color:var(--eqc-bronze-700)">Quran Teachers</span>', 'h2' ),
								eqc_html( '<div class="eqc-teachers-rule">' . eqc_divider_svg( 'teacher' ) . '</div>' ),
								eqc_text( '<p>Our teachers are highly qualified, experienced, and passionate about teaching the Quran. They are here to guide you every step of the way with patience and care.</p>' ),
								eqc_html(
									'<div class="eqc-teachers-features">'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'graduation-cap-filled' ) . '</span><div><p class="eqc-trust-tile-title">Qualified &amp; Experienced</p><p>Well-trained in Tajweed &amp; Quran teaching</p></div></div>'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'person-filled' ) . '</span><div><p class="eqc-trust-tile-title">1-to-1 Personalized Classes</p><p>Focused learning for every student</p></div></div>'
									. '<div class="eqc-trust-tile"><span class="eqc-trust-tile__icon">' . eqc_icon_str( 'shield-filled' ) . '</span><div><p class="eqc-trust-tile-title">Safe &amp; Supportive Environment</p><p>Your comfort and progress is our priority</p></div></div>'
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
		eqc_container( array( 'css_classes' => 'eqc-grid eqc-grid--pricing', 'flex_direction' => 'row' ), $pricing_cards ),
	)
);
$pricing = eqc_section(
	'eqc-section eqc-section--pricing eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Pricing', 'Simple monthly pricing, <br>no hidden fees', true, 'eqc-eyebrow--rosette', '', 'diamond' ),
				$pricing_panel,
				// White strip of four benefits under the cards, hairline-separated.
				eqc_html(
					'<div class="eqc-benefits">'
					. eqc_benefit_tile( 'people-pair', 'Qualified | Male & Female Tutors' )
					. eqc_benefit_tile( 'rehal-quran', 'One-on-One | Live Classes' )
					. eqc_benefit_tile( 'clock', 'Flexible | Schedule' )
					. eqc_benefit_tile( 'shield-star', 'Safe & Supportive | Learning Environment' )
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
	$testi_cards[] = eqc_testimonial_card( $testi_ids[ $i ], $t[0], $t[1], $t[2], array( 'graduate' => 'Expert Tutors', 'clipboard-check' => 'Monthly Tracking', 'target-arrow' => 'Personalised Focus' ) );
}
// Reference quotes are unverified staging content; see QA/PLACEHOLDER-REGISTER.md.
$testimonials = eqc_section(
	'eqc-section eqc-section--testimonials eqc-section--cream',
	array(
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'What Our Families Say', 'Trusted by Families <br><span style="color:var(--eqc-gold-600)">Loved by Students</span>', true, 'eqc-eyebrow--plain', 'quote', 'reviews' ),
				eqc_text( '<p>We are honoured to be part of hundreds of families&rsquo; journey. <br>Here&rsquo;s what they have to say about their experience with us.</p>', 'eqc-testimonials-intro' ),
				eqc_carousel( $testi_cards, __( 'Testimonial slides', 'easy-quran-classes' ) ),
			)
		),
	)
);

// ------------------------------------------------------- 10. BLOG PREVIEW
$blog_section = eqc_section(
	'eqc-section--blog eqc-section eqc-section--surface eqc-section--ornamented',
	array(
		eqc_section_ornaments(),
		eqc_inner(
			'',
			array(
				eqc_section_heading_el( 'Latest News', 'Directly From the <br><span style="color:var(--eqc-gold-600)">Latest News &amp; Articles</span>', false, 'eqc-eyebrow--plain', 'megaphone' ),
				eqc_widget( 'shortcode', array( 'shortcode' => '[eqc_latest_posts count="3"]' ) ),
			)
		),
	)
);

// ------------------------------------------------------- 11. FAQ
// Six general questions on the home page; the full set lives on /faq/
// (client request, QA/qa-9-10.md task 9). Pulled from eqc_faq_data()
// (elementor-helpers.php) — the same canonical strings 16-faq.php builds
// its full grouped page from — rather than a second hardcoded copy, so an
// edit to a shared answer's wording can't drift between the two pages.
$faq_data       = eqc_faq_data();
$home_faq_items = array(
	$faq_data['Classes & Teaching'][0],      // How are classes conducted?
	$faq_data['Classes & Teaching'][1],      // How long is each class?
	$faq_data['Beginners'][0],               // Can beginners start from zero?
	$faq_data['Classes & Teaching'][2],      // Can I choose a male or female teacher?
	$faq_data['Scheduling & Free Trial'][0], // How does the free trial work?
	$faq_data['Scheduling & Free Trial'][1], // Are timings flexible?
);
$home_faq = eqc_section(
	'eqc-section eqc-section--cream',
	array(
		eqc_inner(
			'eqc-container--narrow',
			array(
				eqc_section_heading_el( 'FAQ', 'Questions families ask before starting', true, 'eqc-eyebrow--plain', 'quote' ),
				eqc_faq_group( '', $home_faq_items ),
				eqc_container(
					array( 'css_classes' => 'eqc-btn-group', 'flex_direction' => 'row', 'content_position' => 'center' ),
					array( eqc_button( __( 'View All FAQs', 'easy-quran-classes' ), home_url( '/faq/' ), 'eqc-btn--primary' ) )
				),
			)
		),
	)
);

eqc_save_elementor_page(
	eqc_page_id( 'home' ),
	array( $hero, $trust, $about, $courses, $pricing, $teachers, $testimonials, $blog_section, $home_faq )
);
