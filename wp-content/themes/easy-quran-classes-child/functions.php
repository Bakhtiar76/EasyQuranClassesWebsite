<?php
/**
 * Easy Quran Classes child theme.
 *
 * Presentation-only: design tokens, reusable classes, the site shell
 * (header/footer, since Elementor Free has no Theme Builder), blog
 * templates and Customizer settings — everything Elementor Free cannot
 * express cleanly (see DESIGN.md). Never edit the Hello Elementor parent
 * theme; add customizations here instead.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/template-tags.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/icon-sprite.php';

/**
 * Theme setup: menus, thumbnails, image sizes, title tag, HTML5 markup.
 */
function eqc_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'easy-quran-classes' ),
			'footer'  => __( 'Footer Quick Links', 'easy-quran-classes' ),
		)
	);

	// Consistent crops for card/teacher/testimonial imagery; Elementor's
	// own image widgets can still pick "Full"/custom sizes as needed.
	add_image_size( 'eqc-card', 640, 480, true );
	add_image_size( 'eqc-teacher', 480, 480, true );
	add_image_size( 'eqc-testimonial', 200, 200, true );
	add_image_size( 'eqc-blog-card', 720, 450, true );
}
add_action( 'after_setup_theme', 'eqc_setup' );

/**
 * Asset cache-buster.
 *
 * Production keeps the theme version, so a release ships one stable URL per
 * asset. Locally that is actively harmful: the theme version only changes on
 * a release, so every CSS edit during a design iteration is served from the
 * browser cache under an unchanged `?ver=`. That produced a genuinely
 * misleading half-applied page during the Home parity pass — tokens.css had
 * refreshed while components.css had not, so new custom properties were live
 * but the rules consuming them were not, which reads exactly like a
 * specificity bug and is not one. Locally, fall back to the file's own mtime.
 */
function eqc_asset_version( $relative_path ) {
	$version = wp_get_theme()->get( 'Version' );
	if ( 'local' !== wp_get_environment_type() ) {
		return $version;
	}
	$file = get_stylesheet_directory() . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : $version;
}

/**
 * Enqueue parent stylesheet, self-hosted fonts, then the child's tokens ->
 * components -> motion -> style.css cascade (each layer can safely
 * override the one before it), then the shared vanilla JS.
 */
function eqc_enqueue_assets() {
	wp_enqueue_style(
		'easy-quran-classes-fonts',
		get_stylesheet_directory_uri() . '/assets/css/fonts.css',
		array(),
		eqc_asset_version( '/assets/css/fonts.css' )
	);

	// Hello Elementor registers/enqueues its own 'hello-elementor' style
	// handle; we only depend on it here, never re-register it ourselves.
	wp_enqueue_style(
		'eqc-tokens',
		get_stylesheet_directory_uri() . '/assets/css/tokens.css',
		array( 'hello-elementor' ),
		eqc_asset_version( '/assets/css/tokens.css' )
	);
	wp_enqueue_style(
		'eqc-components',
		get_stylesheet_directory_uri() . '/assets/css/components.css',
		array( 'eqc-tokens' ),
		eqc_asset_version( '/assets/css/components.css' )
	);
	wp_enqueue_style(
		'eqc-shell',
		get_stylesheet_directory_uri() . '/assets/css/shell.css',
		array( 'eqc-components' ),
		eqc_asset_version( '/assets/css/shell.css' )
	);
	wp_enqueue_style(
		'eqc-motion',
		get_stylesheet_directory_uri() . '/assets/css/motion.css',
		array( 'eqc-shell' ),
		eqc_asset_version( '/assets/css/motion.css' )
	);
	wp_enqueue_style(
		'easy-quran-classes-style',
		get_stylesheet_uri(),
		array( 'eqc-motion' ),
		eqc_asset_version( '/style.css' )
	);

	wp_enqueue_script(
		'eqc-scripts',
		get_stylesheet_directory_uri() . '/assets/js/eqc.js',
		array(),
		eqc_asset_version( '/assets/js/eqc.js' ),
		true
	);

	// Contact / Free Trial only: field icons, real-time validation, success
	// modal. Additive to Fluent Forms — see assets/js/eqc-forms.js.
	if ( is_page( array( 'contact', 'free-trial' ) ) ) {
		wp_enqueue_script(
			'eqc-forms',
			get_stylesheet_directory_uri() . '/assets/js/eqc-forms.js',
			array( 'eqc-scripts' ),
			eqc_asset_version( '/assets/js/eqc-forms.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'eqc_enqueue_assets', 20 );

/** Discover the two above-the-fold font files before stylesheets finish loading. */
function eqc_preload_fonts() {
	foreach ( array( 'dm-serif-display-latin-400.woff2', 'manrope-latin-variable.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>', esc_url( get_stylesheet_directory_uri() . '/assets/fonts/' . $font ) );
	}
}
add_action( 'wp_head', 'eqc_preload_fonts', 2 );

/**
 * Mark the document as JS-capable before first paint, so motion.css can
 * keep [data-eqc-reveal] elements visible-by-default until JS proves it
 * can reveal them again (progressive enhancement, no FOUC either way).
 */
function eqc_js_class_inline() {
	echo "<script>document.documentElement.classList.add('eqc-js');</script>\n";
}
add_action( 'wp_head', 'eqc_js_class_inline', 1 );

/**
 * Print the icon sprite once, right after <body> opens, via wp_body_open.
 */
function eqc_print_sprite_in_body() {
	eqc_print_icon_sprite();
}
add_action( 'wp_body_open', 'eqc_print_sprite_in_body' );

/**
 * Trim a handful of default WordPress front-end requests/markup that add
 * weight without value on a marketing site (performance pass, DESIGN.md §23).
 */
function eqc_trim_head_bloat() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'eqc_trim_head_bloat' );

/**
 * Sensible excerpt length/ellipsis for blog cards.
 */
function eqc_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'eqc_excerpt_length' );

function eqc_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'eqc_excerpt_more' );

/**
 * Register the small footer/legal widget-less nav fallback area is not
 * needed — footer.php renders the "footer" nav menu with wp_nav_menu()
 * directly, matching the "reuse WordPress menus" requirement.
 */
