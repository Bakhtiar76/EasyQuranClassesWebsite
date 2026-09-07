<?php
/**
 * One-time local site scaffolding: site identity, the 10-page sitemap,
 * front-page/posts-page assignment, primary + footer nav menus, custom
 * logo. Idempotent — safe to re-run (updates existing pages/menus by
 * slug/name instead of duplicating them).
 *
 * Run with: wp eval-file /tools/00-site-setup.php
 *
 * @package Easy_Quran_Classes
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp eval-file /tools/00-site-setup.php\n" );
}

/**
 * Create a page if it does not already exist (matched by slug), else
 * return the existing page's ID untouched.
 */
function eqc_ensure_page( $slug, $title ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	$id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	// Keep the default page template (NOT "Elementor Canvas" — canvas
	// skips get_header()/get_footer() entirely, and Elementor Free has no
	// Theme Builder to replace them). With the default template, Elementor
	// still renders the page's own builder content, wrapped by the child
	// theme's header.php/footer.php, which is exactly the shared shell we
	// want (TASK-WEBSITE.md Phase H).
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'wp-page' );
	WP_CLI::log( "Created page: {$title} ({$slug}) -> #{$id}" );
	return $id;
}

// 1. Site identity.
update_option( 'blogname', 'Easy Quran Classes' );
update_option( 'blogdescription', 'Learning the Quran step by step' );
update_option( 'blog_public', 0 ); // Local indexing disabled per TASK-WEBSITE.md.
update_option( 'timezone_string', 'UTC' );

// 2. The 10-page sitemap (TASK-WEBSITE.md §8).
$pages = array(
	'home'                             => eqc_ensure_page( 'home', 'Home' ),
	'about'                            => eqc_ensure_page( 'about', 'About' ),
	'courses'                          => eqc_ensure_page( 'courses', 'Courses' ),
	'teachers'                         => eqc_ensure_page( 'teachers', 'Teachers' ),
	'pricing'                          => eqc_ensure_page( 'pricing', 'Pricing' ),
	'contact'                          => eqc_ensure_page( 'contact', 'Contact' ),
	'faq'                              => eqc_ensure_page( 'faq', 'FAQ' ),
	'blog'                             => eqc_ensure_page( 'blog', 'Blog' ),
	'free-trial'                       => eqc_ensure_page( 'free-trial', 'Free Trial' ),
	'online-quran-classes-for-kids'    => eqc_ensure_page( 'online-quran-classes-for-kids', 'Online Quran Classes for Kids' ),
);

// 3. Static homepage + posts page.
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $pages['home'] );
update_option( 'page_for_posts', $pages['blog'] );

// 4. Retire the default scaffold page — not part of the approved sitemap.
$sample = get_page_by_path( 'sample-page' );
if ( $sample && 'trash' !== $sample->post_status ) {
	wp_trash_post( $sample->ID );
	WP_CLI::log( 'Trashed default Sample Page.' );
}

// 5. Primary navigation menu.
$primary_items = array(
	'Home'       => home_url( '/' ),
	'About'      => home_url( '/about/' ),
	'Courses'    => home_url( '/courses/' ),
	'Teachers'   => home_url( '/teachers/' ),
	'Pricing'    => home_url( '/pricing/' ),
	'Contact'    => home_url( '/contact/' ),
	'Free Trial' => home_url( '/free-trial/' ),
);
$primary_menu_id = eqc_ensure_menu( 'Primary Navigation', $primary_items );

// 6. Footer quick-links menu.
$footer_items = array(
	'About'                          => home_url( '/about/' ),
	'Courses'                        => home_url( '/courses/' ),
	'Teachers'                       => home_url( '/teachers/' ),
	'Pricing'                        => home_url( '/pricing/' ),
	'Blog'                           => home_url( '/blog/' ),
	'FAQ'                            => home_url( '/faq/' ),
	'Online Quran Classes for Kids'  => home_url( '/online-quran-classes-for-kids/' ),
);
$footer_menu_id = eqc_ensure_menu( 'Footer Quick Links', $footer_items );
$locations               = (array) get_theme_mod( 'nav_menu_locations', array() );
$locations['footer']     = $footer_menu_id;
$locations['primary']    = $primary_menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

/**
 * Create a nav menu with the given label => URL items if a menu with this
 * name does not already exist; otherwise return the existing menu's ID
 * (left untouched so manual admin edits are never clobbered by a re-run).
 */
function eqc_ensure_menu( $name, $items ) {
	$existing = wp_get_nav_menu_object( $name );
	if ( $existing ) {
		return $existing->term_id;
	}
	$menu_id = wp_create_nav_menu( $name );
	$position = 1;
	foreach ( $items as $label => $url ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => $label,
				'menu-item-url'      => $url,
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position++,
			)
		);
	}
	WP_CLI::log( "Created menu: {$name} (#{$menu_id})" );
	return $menu_id;
}

// 7. Custom logo. Resolved by filename fragment (not a hardcoded attachment
// ID) via the same eqc_media_id() helper the page-building scripts use, so
// re-importing the logo under a new attachment ID (e.g. after a WebP
// conversion) does not require editing this file. Fragment includes the
// extension ("eqc-logo.webp", not "eqc-logo") because eqc_media_id() does a
// substring LIKE match and "eqc-logo-mark.webp" also contains "eqc-logo".
require_once __DIR__ . '/elementor-helpers.php';
$eqc_logo_id = eqc_media_id( 'eqc-logo.webp' );
if ( $eqc_logo_id ) {
	set_theme_mod( 'custom_logo', $eqc_logo_id );
}

// 8. Seed the contact/social theme mods with real (placeholder) values.
// get_theme_mod( $name, $default ) ignores the Customizer setting's own
// registered default on normal front-end requests — only the value
// actually stored in the theme_mods option, or the $default argument
// passed at each call site, is used. Storing real rows here means the
// Customizer shows non-empty starting values instead of blank fields,
// and every template that reads these mods gets a value without having
// to duplicate the default string at each call site.
$contact_defaults = array(
	'eqc_contact_email'    => 'info@easyquranclasses.com',
	'eqc_whatsapp_number'  => '10000000000',
	'eqc_whatsapp_display' => '+1 (000) 000-0000',
	'eqc_phone_display'    => '+1 (000) 000-0000',
	'eqc_address'          => '',
	'eqc_footer_about'     => 'Online Quran classes for kids and adults with qualified teachers. Learn Quran, Tajweed, Hifz and Islamic Studies from the comfort of your home.',
);
foreach ( $contact_defaults as $mod => $value ) {
	if ( false === get_theme_mod( $mod, false ) ) {
		set_theme_mod( $mod, $value );
	}
}

WP_CLI::success( 'Site scaffolding complete: pages, menus, front page, logo.' );
