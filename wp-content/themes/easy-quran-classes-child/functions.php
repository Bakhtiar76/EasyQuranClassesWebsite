<?php
/**
 * Easy Quran Classes child theme.
 *
 * Presentation-only: design tokens and reusable classes that Elementor Free
 * cannot express cleanly (see DESIGN.md). Never edit the Hello Elementor
 * parent theme; add customizations here instead.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the parent theme's stylesheet, then the child's, so child rules
 * load after (and can safely override) the parent's.
 */
function eqc_enqueue_styles() {
	wp_enqueue_style(
		'easy-quran-classes-style',
		get_stylesheet_uri(),
		array( 'hello-elementor' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'eqc_enqueue_styles', 20 );
