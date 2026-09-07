<?php
/**
 * Configure Elementor's Global Colors and Global Fonts (the active Kit) to
 * match DESIGN.md's token palette/type system, via Elementor's own
 * Document API — the same save() mechanism used for page content. This is
 * the native-Elementor-first way to brand default widget output (DESIGN.md
 * §19: "Prefer Site Settings for global colors/typography where
 * available"), so any element an admin adds later through the visual
 * editor already matches the brand without per-widget CSS overrides.
 *
 * Run with: wp eval-file /tools/01-elementor-kit.php --user=1
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp --user=1 eval-file /tools/01-elementor-kit.php\n" );
}

if ( ! did_action( 'elementor/loaded' ) ) {
	WP_CLI::error( 'Elementor is not loaded.' );
}

$kit_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();
if ( ! $kit_id ) {
	WP_CLI::error( 'No active Elementor Kit found.' );
}

$document = \Elementor\Plugin::$instance->documents->get( $kit_id );
if ( ! $document ) {
	WP_CLI::error( "No Elementor document for Kit #{$kit_id}." );
}

// Elementor's four default Global Colors, remapped to DESIGN.md §5 tokens.
$system_colors = array(
	array(
		'_id'   => 'primary',
		'title' => 'Primary',
		'color' => '#1B3A2D', // --eqc-green-800
	),
	array(
		'_id'   => 'secondary',
		'title' => 'Secondary',
		'color' => '#7A432A', // --eqc-bronze-700
	),
	array(
		'_id'   => 'text',
		'title' => 'Text',
		'color' => '#40483F', // --eqc-text
	),
	array(
		'_id'   => 'accent',
		'title' => 'Accent',
		'color' => '#B9974C', // --eqc-gold-500
	),
);

// Elementor's four default Global Fonts, remapped to DESIGN.md §6 tokens.
$system_typography = array(
	array(
		'_id'                          => 'primary',
		'title'                        => 'Primary',
		'typography_typography'        => 'custom',
		'typography_font_family'       => 'DM Serif Display',
		'typography_font_weight'       => '400',
	),
	array(
		'_id'                    => 'secondary',
		'title'                  => 'Secondary',
		'typography_typography'  => 'custom',
		'typography_font_family' => 'DM Serif Display',
		'typography_font_weight' => '400',
	),
	array(
		'_id'                    => 'text',
		'title'                  => 'Text',
		'typography_typography'  => 'custom',
		'typography_font_family' => 'Manrope',
		'typography_font_weight' => '400',
	),
	array(
		'_id'                    => 'accent',
		'title'                  => 'Accent',
		'typography_typography'  => 'custom',
		'typography_font_family' => 'Manrope',
		'typography_font_weight' => '600',
	),
);

$document->save(
	array(
		'settings' => array(
			'system_colors'          => $system_colors,
			'system_typography'      => $system_typography,
			'default_generic_fonts'  => 'sans-serif',
			'container_width'        => array( 'unit' => 'px', 'size' => 1240 ),
			'space_between_widgets'  => array( 'unit' => 'px', 'size' => 20 ),
		),
	)
);

\Elementor\Plugin::$instance->files_manager->clear_cache();

WP_CLI::success( "Elementor Kit #{$kit_id} global colors/typography updated to match DESIGN.md tokens." );
