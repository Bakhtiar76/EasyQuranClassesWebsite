<?php
/**
 * Helpers for composing Elementor Free page content as plain PHP arrays and
 * committing them through Elementor's own Document::save() API — never by
 * writing _elementor_data directly (CLAUDE.md forbids that). Elementor
 * validates the structure, owns the postmeta, and regenerates its own CSS,
 * so pages stay 100% editable in the Elementor editor afterward.
 *
 * Every eqc_el_*() function returns a plain array in Elementor's own
 * element-tree shape: { id, elType, settings, elements[], widgetType? }.
 * eqc_save_elementor_page() is the only place that talks to Elementor.
 *
 * @package Easy_Quran_Classes
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI (wp eval-file).\n" );
}

/** Generate a short unique element id the way Elementor's own editor does. */
function eqc_el_id() {
	return substr( md5( uniqid( '', true ) ), 0, 7 );
}

/**
 * A top-level Container (Elementor Free's flexbox section replacement).
 *
 * Defaults `content_width` to 'full' unless the caller overrides it: when
 * left unset, Elementor treats the container as "boxed" and wraps the
 * real children one level deeper in an `.e-con-inner` div, so our own
 * `css_classes` (e.g. `.eqc-grid--courses { display: grid }`) land on an
 * element whose only child IS that wrapper — the actual card children
 * never become grid/flex items, and things silently degrade to
 * Elementor's own unconstrained flex-wrap (no minmax() floor), which lets
 * too many columns crowd into one row and clip long card titles. No call
 * site in this project ever wants the boxed wrapper.
 *
 * @param array $settings Container settings (background, padding, custom classes...).
 * @param array $children Child elements (containers or widgets).
 */
function eqc_container( $settings, $children = array() ) {
	return array(
		'id'       => eqc_el_id(),
		'elType'   => 'container',
		'settings' => array_merge( array( 'content_width' => 'full' ), $settings ),
		'elements' => $children,
	);
}

/** A leaf widget. */
function eqc_widget( $widget_type, $settings, $children = array() ) {
	return array(
		'id'         => eqc_el_id(),
		'elType'     => 'widget',
		'widgetType' => $widget_type,
		'settings'   => $settings,
		'elements'   => $children,
	);
}

/**
 * Shorthand: a full-width section container with our .eqc-section classes
 * applied via Elementor's own "CSS Classes" advanced control (`css_classes`),
 * so all section spacing/background comes from the child theme's tokens.css
 * instead of per-section inline styling.
 *
 * @param string $classes   e.g. 'eqc-section eqc-section--cream'.
 * @param array  $children  Inner content, typically one boxed inner container.
 * @param array  $extra     Extra settings to merge in (e.g. custom_id).
 */
function eqc_section( $classes, $children, $extra = array() ) {
	return eqc_container(
		array_merge(
			array(
				'css_classes'      => $classes,
				'content_width'    => 'full',
				'flex_direction'   => 'column',
			),
			$extra
		),
		$children
	);
}

/**
 * A boxed inner container (.eqc-container) — the standard content-width
 * wrapper used inside every eqc_section().
 */
function eqc_inner( $classes, $children, $extra = array() ) {
	return eqc_container(
		array_merge(
			array(
				'css_classes'    => trim( 'eqc-container ' . $classes ),
				'content_width'  => 'full',
				'flex_direction' => 'column',
			),
			$extra
		),
		$children
	);
}

/** Raw HTML widget — our escape hatch for markup components.css already styles. */
function eqc_html( $html, $classes = '' ) {
	return eqc_widget(
		'html',
		array(
			'html'         => $html,
			'_css_classes' => $classes,
		)
	);
}

/** Elementor "Heading" widget. */
function eqc_heading( $text, $tag = 'h2', $classes = '' ) {
	return eqc_widget(
		'heading',
		array(
			'title'        => $text,
			'header_size'  => $tag,
			'_css_classes' => $classes,
		)
	);
}

/** Elementor "Text Editor" widget (rich, admin-editable paragraph copy). */
function eqc_text( $html, $classes = '' ) {
	return eqc_widget(
		'text-editor',
		array(
			'editor'       => $html,
			'_css_classes' => $classes,
		)
	);
}

/** Elementor "Image" widget by attachment ID. */
function eqc_image( $attachment_id, $classes = '', $size = 'large' ) {
	return eqc_widget(
		'image',
		array(
			'image'       => array(
				'id'  => $attachment_id,
				'url' => wp_get_attachment_image_url( $attachment_id, $size ),
			),
			'image_size'   => $size,
			'_css_classes' => $classes,
		)
	);
}

/**
 * Elementor "Button" widget — plain text + link, both editable from the
 * normal Elementor panel (no code). CTAs that need an icon glyph use
 * eqc_html() with the same .eqc-btn markup the theme's own header/footer
 * buttons use (see header.php), since Elementor's native icon picker
 * expects Font Awesome/eicons, not our single custom SVG sprite.
 */
function eqc_button( $text, $url, $classes = '' ) {
	$settings = array(
		'text'         => $text,
		'link'         => array( 'url' => $url ),
		'_css_classes' => trim( 'eqc-btn ' . $classes ),
	);
	return eqc_widget( 'button', $settings );
}

/**
 * Look up a seeded Media Library attachment ID by its recognizable
 * source filename fragment (e.g. 'hero-online-quran-class', matching
 * local/media-staging/hero-online-quran-class.webp), so page-building
 * scripts never hardcode brittle numeric IDs.
 *
 * Matches on the attached file path (_wp_attached_file), not post_name —
 * `wp media import --title="..."` derives post_name from the given title,
 * not the source filename, so a post_name match would silently miss.
 *
 * Ordered by post_id DESC: if a fragment matches more than one attachment
 * (e.g. a legacy 'foo.jpg' and its re-imported 'foo.webp' replacement both
 * contain the fragment 'foo'), the most recently imported match wins
 * instead of an undefined LIMIT-1-with-no-ORDER-BY pick.
 */
function eqc_media_id( $slug_fragment ) {
	global $wpdb;
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			'%' . $wpdb->esc_like( $slug_fragment ) . '%'
		)
	);
	if ( ! $id ) {
		WP_CLI::warning( "eqc_media_id(): no attachment found for '{$slug_fragment}'." );
	}
	return $id ? (int) $id : 0;
}

/**
 * Look up a page's post ID by its slug, so `tools/pages/*.php` builders
 * never hardcode a numeric post ID that only happens to be correct on the
 * machine that first created the site. `00-site-setup.php` creates pages
 * with `wp_insert_post()`, whose returned ID depends on install order/
 * history — a literal ID baked into a builder script would resolve to a
 * different (or no) page on a second machine.
 *
 * Errors out (rather than silently creating anything) when the page is
 * missing, since page creation is `00-site-setup.php`'s job, not a page
 * builder's.
 */
function eqc_page_id( $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		WP_CLI::error( "eqc_page_id(): no page found for slug '{$slug}'. Run 'wp eval-file /tools/00-site-setup.php' first." );
	}
	return (int) $page->ID;
}

/**
 * Return one icon's markup as a string. The active theme's
 * inc/template-tags.php (loaded by WordPress before this file runs under
 * `wp eval-file`) already defines eqc_get_icon_html() — reuse it rather
 * than duplicating the sprite markup here.
 */
function eqc_icon_str( $name, $class = '' ) {
	return eqc_get_icon_html( $name, $class );
}

/**
 * A circular icon-only link (arrow/whatsapp/etc button used inside cards),
 * as a small HTML widget — there is no native Elementor equivalent and the
 * icon is decorative chrome, not editable copy.
 */
/**
 * Two faint corner-motif spans for a section's ornamented background — see
 * .eqc-section--ornamented in components.css. Pass as the FIRST element in
 * an eqc_section()'s children array; the CSS handles layering so real
 * content always paints above it regardless of what that content is.
 *
 * @param string $modifier Optional extra class (e.g. 'eqc-corner-motif--sm'
 *                          for the smaller pair used inside a nested panel
 *                          like .eqc-pricing-panel).
 * @param array  $corners  Which corners to draw: any of 'tl', 'tr', 'bl',
 *                          'br'. Defaults to the site-wide tr/bl diagonal.
 *                          The home About section overrides it because
 *                          Assests/Home2.jpeg puts its arabesque top-left.
 */
function eqc_section_ornaments( $modifier = '', $corners = array( 'tr', 'bl' ) ) {
	$extra = $modifier ? ' ' . $modifier : '';
	$html  = '';
	foreach ( $corners as $corner ) {
		if ( ! in_array( $corner, array( 'tl', 'tr', 'bl', 'br' ), true ) ) {
			// Caller-error guard, not user input: every call site passes a
			// hardcoded literal, so this only ever fires on a typo while
			// editing a page builder — warn instead of silently dropping
			// the corner, which previously looked identical to "on purpose".
			WP_CLI::warning( "eqc_section_ornaments(): ignoring unknown corner '{$corner}'." );
			continue;
		}
		$html .= '<span class="eqc-corner-motif eqc-corner-motif--' . esc_attr( $corner . $extra ) . '" aria-hidden="true"></span>';
	}
	return eqc_html( $html );
}

/**
 * Course card (DESIGN.md §16 Course Card): a .eqc-card--course container
 * with the numbered badge + arrow as decorative HTML, but title/level/
 * description as native Heading/Text-Editor widgets so an admin edits
 * them as plain text in Elementor — no code involved.
 *
 * The whole panel is the button (round-3 fix — round 2's full-width "Learn
 * More" bar doubled the button chrome; see the .eqc-card-link comment in
 * components.css): a stretched invisible anchor covers the entire card,
 * and the circular arrow is a purely decorative, non-nested affordance
 * matching the client reference.
 */
function eqc_course_card( $number, $title, $level, $description, $link, $reveal_index = 0, $anchor = '' ) {
	$label = sprintf(
		/* translators: %s: course title, read by screen readers only — the card has no other visible link text. */
		__( 'Learn more about %s', 'easy-quran-classes' ),
		wp_strip_all_tags( $title )
	);
	$settings = array(
		'css_classes'    => 'eqc-card eqc-card--course',
		'flex_direction' => 'column',
	);
	if ( $anchor ) {
		$settings['_element_id'] = $anchor;
	}
	return eqc_container(
		$settings,
		array(
			// The wrapper class is load-bearing, not decorative: Elementor puts
			// two divs between this anchor and the card, and .eqc-card--course's
			// own `> * { position: relative }` was making the outer one the
			// anchor's containing block — collapsing the stretched link to zero
			// height and killing the whole card as a CTA. See the
			// .eqc-card-link-widget note in components.css.
			eqc_html( '<a class="eqc-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $label ) . '"></a>', 'eqc-card-link-widget' ),
			eqc_html( '<span class="eqc-card-index"><span class="eqc-card-index-num">' . esc_html( $number ) . '</span></span>' ),
			eqc_heading( $title, 'h3' ),
			eqc_html( '<p class="eqc-card-level">' . esc_html( $level ) . '</p>' ),
			eqc_html( '<div class="eqc-card-divider">' . eqc_divider_svg( 'card' ) . '</div>' ),
			eqc_text( '<p>' . wp_kses_post( $description ) . '</p>' ),
			eqc_html( '<span class="eqc-arrow-btn" aria-hidden="true">' . eqc_icon_str( 'arrow-right' ) . '</span>', 'eqc-card__foot' ),
		)
	);
}

/**
 * Teacher card (DESIGN.md §16 Teacher Card). Photo via native Image
 * widget (admin can swap it from the Media Library); name/role/facts as
 * native Heading/Text-Editor.
 */
function eqc_teacher_card( $attachment_id, $name, $role, $facts ) {
	$facts_html = '<ul class="eqc-teacher-facts">';
	foreach ( $facts as $icon => $label ) {
		// Teachers.jpeg uses solid award, cap and people silhouettes.
		$icon = in_array( $icon, array( 'certificate', 'graduation-cap', 'users' ), true ) ? $icon . '-filled' : $icon;
		$facts_html .= '<li>' . eqc_icon_str( $icon ) . '<span>' . esc_html( $label ) . '</span></li>';
	}
	$facts_html .= '</ul>';

	return eqc_container(
		array(
			'css_classes'    => 'eqc-card eqc-card--teacher',
			'flex_direction' => 'column',
		),
		array(
			eqc_widget(
				'image',
				array(
					'image'        => array(
						'id'  => $attachment_id,
						'url' => wp_get_attachment_image_url( $attachment_id, 'eqc-teacher' ),
					),
					'image_size'   => 'eqc-teacher',
					'_css_classes' => 'eqc-teacher-photo-widget',
				)
			),
			eqc_html( '<span class="eqc-teacher-seal" aria-hidden="true">' . eqc_icon_str( 'rehal-quran' ) . '</span>' ),
			// "|" is an explicit line break: the reference sets every teacher
			// name on two lines, and the break is what keeps the four cards
			// the same height regardless of name length.
			eqc_heading( implode( ' <br>', array_map( 'esc_html', explode( '|', $name ) ) ), 'h3' ),
			eqc_html( '<p class="eqc-teacher-role">' . esc_html( $role ) . '</p><div class="eqc-teacher-divider">' . eqc_divider_svg( 'dot' ) . '</div>' ),
			eqc_html( $facts_html ),
			eqc_html(
				'<a class="eqc-btn eqc-btn--outline eqc-btn--sm eqc-teacher-profile" href="' . esc_url( home_url( '/teachers/' ) ) . '">'
				. '<span>' . esc_html__( 'View Profile', 'easy-quran-classes' ) . '</span>'
				. '</a>',
				'eqc-card__foot'
			),
		)
	);
}

/**
 * A clearly-marked placeholder teacher slot — used to round out the
 * roster to a fuller-feeling team page without fabricating a real
 * person's photo, name or credentials (CLAUDE.md Content Integrity: never
 * publish placeholders as facts). Dashed border + generic icon avatar +
 * an explicit "to be added" note make it unmistakable as a stub, and the
 * admin fills in a real photo/name/facts later via the normal Elementor
 * panel — no code involved.
 */
function eqc_teacher_card_stub( $slot_label = 'Teacher Name' ) {
	return eqc_container(
		array(
			'css_classes'    => 'eqc-card eqc-card--teacher eqc-card--teacher-stub',
			'flex_direction' => 'column',
		),
		array(
			eqc_html( '<div class="eqc-teacher-stub-avatar">' . eqc_icon_str( 'person' ) . '</div>' ),
			eqc_heading( $slot_label, 'h3' ),
			eqc_html( '<p class="eqc-teacher-role">Quran Teacher</p>' ),
			eqc_html( '<p class="eqc-teacher-stub-note">Photo, name and credentials to be added.</p>' ),
		)
	);
}

/**
 * Pricing card (DESIGN.md §16 Pricing Card). Whole panel is the CTA — same
 * stretched-link pattern as eqc_course_card() — closing in a decorative
 * circular arrow rather than a separate full "Choose Plan" pill, matching
 * the reference (Assests/…4.23.20 PM.jpeg).
 *
 * @param array $features Plain-text feature list.
 */
function eqc_pricing_card( $frequency, $price, $unit, $features, $link, $featured = false ) {
	$star           = eqc_get_svg_asset( 'pricing-bullet', 'eqc-pricing-bullet' );
	$features_html  = '<ul class="eqc-pricing-list">';
	foreach ( $features as $feature ) {
		$features_html .= '<li>' . $star . '<span>' . esc_html( $feature ) . '</span></li>';
	}
	$features_html .= '</ul>';

	$classes = 'eqc-card eqc-card--pricing' . ( $featured ? ' eqc-card--pricing--featured' : '' );
	$label   = sprintf(
		/* translators: %s: plan frequency, e.g. "3 Days/Week" — read by screen readers only. */
		__( 'Choose the %s plan', 'easy-quran-classes' ),
		wp_strip_all_tags( $frequency )
	);

	$children   = array();
	// See the eqc_course_card() note: the featured card's `> * { position:
	// relative }` had the same zero-height effect on this link.
	$children[] = eqc_html( '<a class="eqc-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $label ) . '"></a>', 'eqc-card-link-widget' );
	// The badge and the medallion are both anchored to the CARD's top edge, so
	// their widget wrappers must not become their containing block either — see
	// the eqc-card-link-widget note above. Left unmarked, the featured card's
	// medallion rendered 38px lower than its three siblings' and the badge sat
	// inside the card instead of astride its edge.
	if ( $featured ) {
		$children[] = eqc_html( '<span class="eqc-pricing-badge">' . esc_html__( 'Recommended', 'easy-quran-classes' ) . '</span>', 'eqc-pricing-anchor-widget' );
	}
	$children[] = eqc_html(
		'<span class="eqc-pricing-icon"><span class="eqc-pricing-icon-ring" aria-hidden="true">' . eqc_get_svg_asset( 'pricing-medallion-frame' ) . '</span>' . eqc_icon_str( 'calendar' ) . '</span>',
		'eqc-pricing-anchor-widget'
	);
	$children[] = eqc_html( '<span class="eqc-pricing-freq eqc-pricing-banner">' . esc_html( $frequency ) . '</span>' );
	$children[] = eqc_html( $features_html );
	$children[] = eqc_html( '<div class="eqc-pricing-price-divider">' . eqc_divider_svg( 'price' ) . '</div>' );
	$children[] = eqc_html(
		'<p class="eqc-pricing-price"><span class="eqc-pricing-price-figure">$' . esc_html( $price ) . '</span>'
		. '<span class="eqc-pricing-unit">' . esc_html( $unit ) . '</span>'
		. '<span class="eqc-arrow-btn" aria-hidden="true">' . eqc_icon_str( 'arrow-right' ) . '</span></p>',
		'eqc-card__foot'
	);

	return eqc_container(
		array(
			'css_classes'    => $classes,
			'flex_direction' => 'column',
		),
		$children
	);
}

/**
 * Testimonial card (DESIGN.md §16 Testimonial Card).
 *
 * @param array $tags Short trust-tag labels shown at the card's foot.
 */
function eqc_testimonial_card( $attachment_id, $name, $location, $quote, $tags = array() ) {
	$tags_html = '';
	if ( $tags ) {
		$tags_html = '<div class="eqc-testimonial-tags">';
		foreach ( $tags as $icon => $label ) {
			$tags_html .= '<span>' . eqc_icon_str( $icon ) . esc_html( $label ) . '</span>';
		}
		$tags_html .= '</div>';
	}

	return eqc_container(
		array(
			'css_classes'    => 'eqc-card eqc-card--testimonial',
			'flex_direction' => 'column',
		),
		array(
			eqc_html(
				'<div class="eqc-testimonial-avatar-wrap"><div class="eqc-testimonial-photo">' . wp_get_attachment_image( $attachment_id, 'eqc-testimonial' ) . '</div>'
				. '<div class="eqc-testimonial-quote-mark">' . eqc_icon_str( 'quote-filled' ) . '</div></div>'
			),
			eqc_heading( $name, 'h3' ),
			eqc_html( '<p class="eqc-testimonial-location">' . eqc_icon_str( 'map-pin-filled' ) . ' ' . esc_html( $location ) . '</p>' ),
			eqc_html( '<div class="eqc-testimonial-divider">' . eqc_divider_svg( 'dot' ) . '</div>' ),
			eqc_text( '<p>' . esc_html( $quote ) . '</p>' ),
			eqc_html( $tags_html, 'eqc-card__foot' ),
		)
	);
}

/**
 * Placeholder ("stub") testimonial slide — fills out the sliding carousel
 * to a full 3x3 grid without inventing fake reviewer names/quotes, which
 * CLAUDE.md's Content Integrity rule forbids. Mirrors
 * eqc_teacher_card_stub()'s dashed-border/muted treatment so it reads the
 * same way: clearly an editable empty slot, never mistaken for a real
 * review.
 */
function eqc_testimonial_card_stub() {
	return eqc_container(
		array(
			'css_classes'    => 'eqc-card eqc-card--testimonial eqc-card--testimonial-stub',
			'flex_direction' => 'column',
		),
		array(
			eqc_html( '<div class="eqc-testimonial-stub-avatar">' . eqc_icon_str( 'quote' ) . '</div>' ),
			eqc_heading( __( 'Add a Testimonial', 'easy-quran-classes' ), 'h3' ),
			eqc_html( '<p class="eqc-testimonial-stub-note">' . esc_html__( 'A real family review will go here once received.', 'easy-quran-classes' ) . '</p>' ),
		)
	);
}

/**
 * A one-card-at-a-time auto-advancing carousel (arrows + dots) — shared by
 * the homepage teacher row and the testimonials section rather than
 * duplicating the wiring twice. See initCarousel() in eqc.js and
 * .eqc-carousel* in components.css for the behavior/sizing this markup
 * contract expects.
 *
 * @param array  $cards      Card elements; each becomes one track item.
 * @param string $aria_label Accessible label for the dot tablist.
 */
function eqc_carousel( $cards, $aria_label ) {
	$dots = '';
	foreach ( $cards as $i => $card ) {
		$dots .= sprintf(
			'<button type="button" class="eqc-slider-dot%s" data-slide-index="%d" role="tab" aria-selected="%s" aria-label="%s"></button>',
			0 === $i ? ' is-active' : '',
			$i,
			0 === $i ? 'true' : 'false',
			/* translators: %d: slide number. */
			esc_attr( sprintf( __( 'Show slide %d', 'easy-quran-classes' ), $i + 1 ) )
		);
	}

	$track    = eqc_container( array( 'css_classes' => 'eqc-carousel-track', 'flex_direction' => 'row' ), $cards );
	$viewport = eqc_container( array( 'css_classes' => 'eqc-carousel-viewport', 'flex_direction' => 'column' ), array( $track ) );

	return eqc_container(
		array(
			'css_classes'    => 'eqc-carousel',
			'flex_direction' => 'column',
		),
		array(
			eqc_html( '<button type="button" class="eqc-carousel-arrow eqc-carousel-arrow--prev" aria-label="' . esc_attr__( 'Previous', 'easy-quran-classes' ) . '">' . eqc_icon_str( 'chevron-right' ) . '</button>' ),
			$viewport,
			eqc_html( '<button type="button" class="eqc-carousel-arrow eqc-carousel-arrow--next" aria-label="' . esc_attr__( 'Next', 'easy-quran-classes' ) . '">' . eqc_icon_str( 'chevron-right' ) . '</button>' ),
			eqc_html( '<div class="eqc-slider-dots" role="tablist" aria-label="' . esc_attr( $aria_label ) . '">' . $dots . '</div>' ),
		)
	);
}

/**
 * Standard inner-page hero: eyebrow + H1 + intro paragraph, centered, on
 * the cream background — the consistent "hero" every inner page opens
 * with (DESIGN.md §18 gives each page a hero; the homepage's own richer
 * split hero stays specific to tools/pages/10-home.php).
 */
function eqc_page_hero( $eyebrow, $title, $intro, $icon = 'rehal-quran' ) {
	return eqc_section(
		'eqc-section eqc-section--tight eqc-section--cream eqc-section--textured eqc-section--ornamented',
		array(
			eqc_section_ornaments(),
			eqc_inner(
				'eqc-container--narrow',
				array(
					eqc_html( '<div style="text-align:center"><span class="eqc-eyebrow">' . eqc_icon_str( $icon ) . ' ' . esc_html( $eyebrow ) . '</span></div>' ),
					eqc_heading( $title, 'h1', 'eqc-align-center' ),
					eqc_text( '<p class="eqc-body-l" style="text-align:center;">' . wp_kses_post( $intro ) . '</p>' ),
				)
			),
		)
	);
}

/**
 * Section heading block (eyebrow + H2 + gold rule) as an Elementor element
 * — builds the same eyebrow + H2 + gold-rule markup pattern used
 * site-wide, but as a plain element-tree array so it can be dropped into
 * an elements array passed to Document::save() (echoing PHP, as a
 * header.php-style template tag would, isn't usable there).
 *
 * The rule is the generated divider-section.svg (hairline + diamond
 * terminals + rosette medallion, see tools/graphics/gen-ornaments.mjs) as
 * one self-contained inline asset — fixes a real bug where this div
 * previously carried BOTH `eqc-heading-rule` and `eqc-heading-rule--ornate`,
 * so it inherited the plain rule's `height:1px; background:linear-gradient`
 * as well as the ornate modifier, painting as a full-width flat gold bar
 * with the old tiny flower glyph stranded near the left edge inside it.
 */
function eqc_section_heading_el( $eyebrow, $heading, $centered = false, $eyebrow_class = '', $eyebrow_icon = '', $divider = 'section' ) {
	$class = 'eqc-stack eqc-section-heading' . ( $centered ? ' eqc-section-heading--center' : '' );
	$html  = '<div class="' . esc_attr( $class ) . '" data-eqc-reveal data-eqc-reveal-index="0">';
	if ( $eyebrow ) {
		// courses.jpeg draws this label as a bare gold icon + caps, with no
		// pill; pricing and teachers keep the outlined pill. Hence the
		// variant rather than restyling the shared .eqc-eyebrow.
		$html .= '<span class="eqc-eyebrow ' . esc_attr( $eyebrow_class ) . '">'
			. ( $eyebrow_icon ? eqc_icon_str( $eyebrow_icon ) : '' )
			. esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h2>' . wp_kses_post( $heading ) . '</h2>';
	$html .= '<div class="eqc-heading-rule--ornate">' . eqc_divider_svg( $divider ) . '</div>';
	$html .= '</div>';
	return eqc_html( $html );
}

/**
 * Trust/benefit tile (small icon + heading + description).
 *
 * A "|" in either string is an explicit line break, transcribed from the
 * reference. The four tiles in Home.jpeg's trust strip break at points no
 * single max-width can reproduce - "Your child's safety is | our top
 * priority" wraps at ~105 native px while "Recognize your progress | with
 * achievement" runs to ~150 - so the break is content, not styling, exactly
 * as TASK-DESIGN-PARITY.md 2 describes. Callers that pass no "|" are
 * unaffected and wrap naturally.
 */
function eqc_trust_tile( $icon, $title, $description ) {
	$lines = static function ( $text ) {
		return implode( ' <br>', array_map( 'esc_html', array_map( 'trim', explode( '|', $text ) ) ) );
	};
	// A styled paragraph, not a heading: these tiles are minor benefit
	// labels, not real subsections, so making them headings would skip a
	// level wherever they sit between an H1/H2 and the page's next real
	// H2/H3 (DESIGN.md §22 wants no skipped heading levels).
	return eqc_html(
		'<div class="eqc-trust-tile">' . eqc_icon_str( $icon, 'eqc-icon' )
		. '<div><p class="eqc-trust-tile-title">' . $lines( $title ) . '</p><p>' . $lines( $description ) . '</p></div></div>'
	);
}

/**
 * Small chip used in the hero (icon + label).
 *
 * Pass $label_2 to get the reference's two-line card form (Home.jpeg shows
 * "1-to-1" over "Live Classes" in a white rounded card, not a one-line
 * pill) — extended here rather than adding a second card function beside
 * this one. Callers that pass one label keep the original pill.
 */
function eqc_chip( $icon, $label, $label_2 = '' ) {
	$class = $label_2 ? 'eqc-chip eqc-chip--card' : 'eqc-chip';
	$text  = '<span class="eqc-chip-label">' . esc_html( $label );
	$text .= $label_2 ? '<span>' . esc_html( $label_2 ) . '</span>' : '';
	$text .= '</span>';
	return '<span class="' . esc_attr( $class ) . '">' . eqc_icon_str( $icon ) . $text . '</span>';
}

/**
 * One About-section stat: a rosette-framed icon disc, the label, then the
 * value. Label ABOVE value, which is the order Home2.jpeg uses - the reverse
 * of the countup tiles this replaces. Kept as its own small helper rather
 * than inlined markup so the three calls stay readable and the unverified
 * values sit in one obvious place (QA/PLACEHOLDER-REGISTER.md).
 */
function eqc_about_stat( $icon, $label, $value ) {
	return '<div class="eqc-about-stat">'
		. '<span class="eqc-about-stat__disc">' . eqc_icon_str( $icon ) . '</span>'
		. '<span class="eqc-about-stat__label">' . esc_html( $label ) . '</span>'
		. '<span class="eqc-about-stat__value">' . esc_html( $value ) . '</span>'
		. '</div>';
}

/**
 * One tile in the pricing benefits strip: a rosette-framed icon disc and a
 * two-line label. "|" is an explicit line break, as in eqc_trust_tile().
 */
function eqc_benefit_tile( $icon, $label ) {
	$lines = implode( ' <br>', array_map( 'esc_html', array_map( 'trim', explode( '|', $label ) ) ) );
	return '<div class="eqc-benefit">'
		. '<span class="eqc-benefit__disc">' . eqc_icon_str( $icon ) . '</span>'
		. '<span class="eqc-benefit__label">' . $lines . '</span>'
		. '</div>';
}

/** A raw icon-labeled anchor matching the .eqc-btn pattern used in header/footer (for icon CTAs). */
function eqc_icon_button( $icon, $text, $url, $variant = 'eqc-btn--primary' ) {
	return eqc_html(
		sprintf(
			'<a class="eqc-btn %s" href="%s">%s %s</a>',
			esc_attr( $variant ),
			esc_url( $url ),
			eqc_icon_str( $icon ),
			esc_html( $text )
		)
	);
}

/** One FAQ accordion item (question/answer), matching assets/js/eqc.js's expected markup. */
/**
 * The site's full FAQ content, grouped by topic — the single source both
 * 16-faq.php (the full page) and 10-home.php (a 6-item teaser) build from.
 * Previously each file hardcoded its own copy of the six questions they
 * share; identical English strings duplicated between two files meant an
 * edit to one answer's wording could silently drift from the other.
 *
 * @return array<string,array<array{0:string,1:string}>> group title => list
 *         of [ question, answer ] pairs.
 */
function eqc_faq_data() {
	return array(
		'Classes & Teaching'      => array(
			array( 'How are classes conducted?', 'Every class is a live, 1-to-1 video session with a qualified teacher. Classes are never pre-recorded.' ),
			array( 'How long is each class?', 'Standard classes are 30 minutes, matching the plans on our Pricing page.' ),
			array( 'Can I choose a male or female teacher?', 'Yes, families can request a male or female teacher based on their preference.' ),
		),
		'Beginners'               => array(
			array( 'Can beginners start from zero?', 'Yes. Noorani Qaida starts from the Arabic alphabet itself, with no prior reading ability assumed.' ),
			array( 'What if I struggle with the alphabet as an adult?', 'That\'s exactly what Noorani Qaida is for, at any age. Teachers are patient with adult beginners.' ),
		),
		'Children'                => array(
			array( 'What age can children start?', 'Children of school age can typically begin; a teacher can advise on readiness during the free trial.' ),
			array( 'Will my child have the same teacher each week?', 'We aim to keep the same teacher and time slot consistent wherever possible.' ),
		),
		'Courses'                 => array(
			array( 'Which course should I choose?', 'See the guidance on our Courses page, or tell us your level during the free trial and we will recommend one.' ),
			array( 'Can I combine Islamic Studies with a Quran course?', 'Yes, many students take Islamic Studies alongside a Quran course.' ),
		),
		'Scheduling & Free Trial' => array(
			array( 'How does the free trial work?', 'Tell us the student\'s age, level and availability, and we match a suitable teacher for one trial class before any commitment.' ),
			array( 'Are timings flexible?', 'Yes. Classes are scheduled around the times that work for your family, not a fixed institutional timetable.' ),
			array( 'Can I reschedule a class?', 'Contact your teacher or us directly in advance and we will help adjust the timing.' ),
		),
		'Devices & Access'        => array(
			array( 'What device do I need?', 'A computer, tablet or smartphone with a camera, microphone and stable internet connection is enough.' ),
			array( 'Which video platform is used?', 'Classes run over standard video-call software; your teacher will confirm the exact link when your trial is booked.' ),
		),
	);
}

function eqc_faq_item_html( $question, $answer ) {
	return '<div class="eqc-faq-item" data-open="false">'
		. '<button type="button" class="eqc-faq-question" aria-expanded="false">'
		. '<span>' . esc_html( $question ) . '</span>'
		. '<span class="eqc-faq-icon">' . eqc_icon_str( 'plus' ) . '</span>'
		. '</button>'
		. '<div class="eqc-faq-answer"><div class="eqc-faq-answer-inner"><p>' . wp_kses_post( $answer ) . '</p></div></div>'
		. '</div>';
}

/**
 * A titled FAQ group: heading + a stack of eqc_faq_item_html() items, all
 * inside one HTML widget so the accordion's JS/CSS contract (siblings
 * inside one .eqc-faq-group) stays intact.
 *
 * @param array $items [ [question, answer], ... ].
 */
function eqc_faq_group( $title, $items ) {
	$html = '<div class="eqc-faq-group">';
	if ( $title ) {
		$html .= '<h3>' . esc_html( $title ) . '</h3>';
	}
	foreach ( $items as $item ) {
		$html .= eqc_faq_item_html( $item[0], $item[1] );
	}
	$html .= '</div>';
	return eqc_html( $html );
}

/**
 * Commit an Elementor element tree to a page through Elementor's own
 * Document API (Plugin::$instance->documents->get()->save()) — the exact
 * call the editor itself makes. Never touches _elementor_data directly.
 *
 * @param int   $post_id  Target page ID.
 * @param array $elements Top-level array of container elements.
 */
function eqc_save_elementor_page( $post_id, $elements ) {
	if ( 'local' !== wp_get_environment_type() || 'localhost' !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		WP_CLI::error( 'Page builders require the approved localhost environment.' );
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		WP_CLI::error( 'Page builders require an editor user; run with --user=1.' );
	}
	if ( ! did_action( 'elementor/loaded' ) ) {
		WP_CLI::error( 'Elementor is not loaded.' );
	}

	$document = \Elementor\Plugin::$instance->documents->get( $post_id );
	if ( ! $document ) {
		WP_CLI::error( "No Elementor document for post #{$post_id}." );
	}

	$document->save(
		array(
			'elements' => $elements,
			'settings' => array(),
		)
	);

	// Re-query persisted markup: Elementor can report success after a no-op.
	// Fresh element IDs are generated every build, so the root ID proves this save landed.
	$saved = json_decode( get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( empty( $elements[0]['id'] ) || ( $saved[0]['id'] ?? null ) !== $elements[0]['id'] ) {
		WP_CLI::error( "Elementor save verification failed for post #{$post_id}." );
	}

	// Force the page to be recognized as Elementor-built and regenerate CSS.
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );

	\Elementor\Plugin::$instance->files_manager->clear_cache();

	WP_CLI::success( "Saved Elementor content for post #{$post_id}." );
}
