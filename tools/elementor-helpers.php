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
 * filename fragment (set during the media import in this session), so
 * page-building scripts never hardcode brittle numeric IDs.
 */
function eqc_media_id( $slug_fragment ) {
	global $wpdb;
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name = %s LIMIT 1",
			$slug_fragment
		)
	);
	return $id ? (int) $id : 0;
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
