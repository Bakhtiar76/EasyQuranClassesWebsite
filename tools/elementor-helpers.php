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
 * @param array $settings Container settings (background, padding, custom classes...).
 * @param array $children Child elements (containers or widgets).
 */
function eqc_container( $settings, $children = array() ) {
	return array(
		'id'       => eqc_el_id(),
		'elType'   => 'container',
		'settings' => $settings,
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
 * local/media-staging/hero-online-quran-class.jpg), so page-building
 * scripts never hardcode brittle numeric IDs.
 *
 * Matches on the attached file path (_wp_attached_file), not post_name —
 * `wp media import --title="..."` derives post_name from the given title,
 * not the source filename, so a post_name match would silently miss.
 */
function eqc_media_id( $slug_fragment ) {
	global $wpdb;
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
			'%' . $wpdb->esc_like( $slug_fragment ) . '%'
		)
	);
	if ( ! $id ) {
		WP_CLI::warning( "eqc_media_id(): no attachment found for '{$slug_fragment}'." );
	}
	return $id ? (int) $id : 0;
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
function eqc_icon_link( $icon, $url, $classes = 'eqc-arrow-btn' ) {
	return eqc_html( sprintf( '<a class="%s" href="%s">%s</a>', esc_attr( $classes ), esc_url( $url ), eqc_icon_str( $icon ) ) );
}

/**
 * Course card (DESIGN.md §16 Course Card): a .eqc-card--course container
 * with the numbered badge + arrow as decorative HTML, but title/level/
 * description as native Heading/Text-Editor widgets so an admin edits
 * them as plain text in Elementor — no code involved.
 */
function eqc_course_card( $number, $title, $level, $description, $link, $reveal_index = 0 ) {
	return eqc_container(
		array(
			'css_classes'    => 'eqc-card eqc-card--course',
			'flex_direction' => 'column',
		),
		array(
			eqc_html( '<span class="eqc-card-index">' . esc_html( $number ) . '</span>' ),
			eqc_heading( $title, 'h3' ),
			eqc_html( '<p class="eqc-card-level">' . esc_html( $level ) . '</p>' ),
			eqc_text( '<p>' . wp_kses_post( $description ) . '</p>' ),
			eqc_icon_link( 'arrow-right', $link ),
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
			eqc_heading( $name, 'h3' ),
			eqc_html( '<p class="eqc-teacher-role">' . esc_html( $role ) . '</p>' ),
			eqc_html( $facts_html ),
		)
	);
}

/**
 * Pricing card (DESIGN.md §16 Pricing Card).
 *
 * @param array $features Plain-text feature list.
 */
function eqc_pricing_card( $frequency, $price, $unit, $features, $link, $featured = false ) {
	$features_html = '<ul class="eqc-pricing-list">';
	foreach ( $features as $feature ) {
		$features_html .= '<li>' . eqc_icon_str( 'check' ) . '<span>' . esc_html( $feature ) . '</span></li>';
	}
	$features_html .= '</ul>';

	$classes = 'eqc-card eqc-card--pricing' . ( $featured ? ' eqc-card--pricing--featured' : '' );

	$children = array();
	if ( $featured ) {
		$children[] = eqc_html( '<span class="eqc-pricing-badge">' . esc_html__( 'Recommended', 'easy-quran-classes' ) . '</span>' );
	}
	$children[] = eqc_html( '<span class="eqc-pricing-freq">' . esc_html( $frequency ) . '</span>' );
	$children[] = eqc_html( $features_html );
	$children[] = eqc_html( '<p class="eqc-pricing-price">$' . esc_html( $price ) . '<small>/ ' . esc_html( $unit ) . '</small></p>' );
	$children[] = eqc_button( __( 'Choose Plan', 'easy-quran-classes' ), $link, $featured ? 'eqc-btn--bronze' : 'eqc-btn--primary' );

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
				'<div class="eqc-testimonial-photo">' . wp_get_attachment_image( $attachment_id, 'eqc-testimonial' ) . '</div>'
				. '<div class="eqc-testimonial-quote-mark">' . eqc_icon_str( 'quote' ) . '</div>',
				'eqc-static-wrap'
			),
			eqc_heading( $name, 'h3' ),
			eqc_html( '<p class="eqc-testimonial-location">' . eqc_icon_str( 'map-pin' ) . ' ' . esc_html( $location ) . '</p>' ),
			eqc_text( '<p>' . esc_html( $quote ) . '</p>' ),
			eqc_html( $tags_html ),
		)
	);
}

/**
 * Standard inner-page hero: eyebrow + H1 + intro paragraph, centered, on
 * the cream background — the consistent "hero" every inner page opens
 * with (DESIGN.md §18 gives each page a hero; the homepage's own richer
 * split hero stays specific to tools/pages/10-home.php).
 */
function eqc_page_hero( $eyebrow, $title, $intro, $icon = 'book-open' ) {
	return eqc_section(
		'eqc-section eqc-section--tight eqc-section--cream',
		array(
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
 * — the page-building equivalent of the theme's eqc_section_heading()
 * template tag (which echoes PHP for header.php-style templates and can't
 * be used inside an elements array passed to Document::save()).
 */
function eqc_section_heading_el( $eyebrow, $heading, $centered = false ) {
	$class = 'eqc-stack eqc-section-heading' . ( $centered ? ' eqc-section-heading--center' : '' );
	$html  = '<div class="' . esc_attr( $class ) . '" data-eqc-reveal data-eqc-reveal-index="0">';
	if ( $eyebrow ) {
		$html .= '<span class="eqc-eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h2>' . wp_kses_post( $heading ) . '</h2>';
	$html .= '<hr class="eqc-heading-rule eqc-heading-rule--draw" />';
	$html .= '</div>';
	return eqc_html( $html );
}

/** Trust/benefit tile (small icon + heading + description). */
function eqc_trust_tile( $icon, $title, $description ) {
	// A styled paragraph, not a heading: these tiles are minor benefit
	// labels, not real subsections, so making them headings would skip a
	// level wherever they sit between an H1/H2 and the page's next real
	// H2/H3 (DESIGN.md §22 wants no skipped heading levels).
	return eqc_html(
		'<div class="eqc-trust-tile">' . eqc_icon_str( $icon, 'eqc-icon' )
		. '<div><p class="eqc-trust-tile-title">' . esc_html( $title ) . '</p><p>' . esc_html( $description ) . '</p></div></div>'
	);
}

/** Small pill chip used in the hero (icon + short label). */
function eqc_chip( $icon, $label ) {
	return '<span class="eqc-chip">' . eqc_icon_str( $icon ) . '<span>' . esc_html( $label ) . '</span></span>';
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

	// Force the page to be recognized as Elementor-built and regenerate CSS.
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );

	\Elementor\Plugin::$instance->files_manager->clear_cache();

	WP_CLI::success( "Saved Elementor content for post #{$post_id}." );
}
