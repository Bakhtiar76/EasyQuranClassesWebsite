<?php
/**
 * One-command first-run bootstrap for a fresh local WordPress install:
 * plugins, parent theme, media, then every other tools/*.php script in
 * order. This is the script that makes "clone the repo, run one command"
 * actually true — see README-SETUP.md §3.
 *
 * Idempotent throughout: safe to re-run on a site that already has some
 * or all of this in place (e.g. after `docker compose down -v` and back
 * up, or to pick up new media/pages added since the last run).
 *
 * Assumes `wp core install` has already run — that step needs WordPress
 * NOT to be installed yet, so it lives in the OS-level wrapper
 * (local/bootstrap.ps1 / local/bootstrap.sh), not here.
 *
 * Does NOT touch: Novamira (local-only MCP connector, manual per
 * README-SETUP.md §5 — it needs an Application Password that only exists
 * after the site is up), or vibe-ai/akismet (installed-but-inactive by
 * deliberate choice, see CLAUDE.md "Local Environment").
 *
 * Run with: wp --user=1 eval-file /tools/05-bootstrap.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp --user=1 eval-file /tools/05-bootstrap.php\n" );
}

/**
 * Run one of this project's own tools/*.php scripts as a fresh WP-CLI
 * subprocess rather than a PHP `require` in the current process.
 *
 * This matters specifically for scripts that depend on Elementor (or any
 * plugin activated earlier in this same run): WordPress loads active
 * plugins exactly once, at the very start of a process's bootstrap.
 * Activating a plugin mid-run (the "plugin install --activate" calls
 * below are themselves already subprocesses) updates the `active_plugins`
 * option in the database, but never `include`s the plugin's code into
 * *this* still-running process — so a subsequent `require` of a script
 * that checks `did_action( 'elementor/loaded' )` would still see it as
 * not loaded, even though a brand new `wp` invocation would. Confirmed
 * while testing this script against a fresh install: 01-elementor-kit.php
 * failed with "Elementor is not loaded" via `require`, and succeeded once
 * changed to spawn a fresh subprocess here.
 *
 * Aborts the whole bootstrap on the child's first failure, matching every
 * other tools/*.php script's own WP_CLI::error() behaviour.
 */
function eqc_run_step( $relative_path, $user = null ) {
	$command = 'eval-file /tools/' . ltrim( $relative_path, '/' );
	if ( null !== $user ) {
		$command .= ' --user=' . $user;
	}
	$result = WP_CLI::runcommand( $command, array( 'exit_error' => false, 'return' => 'all' ) );
	if ( trim( $result->stdout ) ) {
		WP_CLI::log( trim( $result->stdout ) );
	}
	if ( 0 !== $result->return_code ) {
		WP_CLI::error( "Step '{$relative_path}' failed (exit {$result->return_code}): " . trim( $result->stderr ) );
	}
}

WP_CLI::log( '=== 1/6: Plugins ===' );
$plugins = array( 'elementor', 'fluentform', 'seo-by-rank-math' );
foreach ( $plugins as $slug ) {
	if ( eqc_is_plugin_active_for_slug( $slug ) ) {
		WP_CLI::log( "Already active: {$slug}" );
		continue;
	}
	WP_CLI::runcommand( "plugin install {$slug} --activate", array( 'exit_error' => false ) );
}

/** True if any active plugin's file path starts with "{$slug}/". */
function eqc_is_plugin_active_for_slug( $slug ) {
	foreach ( (array) get_option( 'active_plugins', array() ) as $file ) {
		if ( 0 === strpos( $file, $slug . '/' ) ) {
			return true;
		}
	}
	return false;
}

WP_CLI::log( '=== 2/6: Parent theme ===' );
// The child theme (bind-mounted from the repo, already the active theme in
// a fresh WordPress image only in the sense that it's the only theme
// present under wp-content/themes/ that Docker mounts read-write — the
// parent it declares via `Template: hello-elementor` in style.css must be
// installed separately from wordpress.org) and activation of the child.
if ( ! wp_get_theme( 'hello-elementor' )->exists() ) {
	WP_CLI::runcommand( 'theme install hello-elementor', array( 'exit_error' => false ) );
}
WP_CLI::runcommand( 'theme activate easy-quran-classes-child' );

WP_CLI::log( '=== 3/6: Media (from local/media-staging/, mounted read-only at /media-staging) ===' );
require_once __DIR__ . '/elementor-helpers.php';
$staging_dir = '/media-staging';
$webp_files  = glob( $staging_dir . '/*.webp' );
if ( ! $webp_files ) {
	WP_CLI::warning( "No .webp files found in {$staging_dir} — is local/media-staging/ populated and the wpcli service's bind mount present?" );
}
foreach ( $webp_files as $path ) {
	// Match on the FULL filename including extension, not the bare
	// basename — 00-site-setup.php's own eqc_media_id() call documents
	// why: eqc_media_id() does a substring LIKE match, so the bare
	// fragment "eqc-logo" also matches the already-imported
	// "eqc-logo-mark.webp" and would wrongly skip importing
	// "eqc-logo.webp" itself. Confirmed while testing this script.
	$fragment = basename( $path );
	if ( eqc_media_id( $fragment ) ) {
		WP_CLI::log( "Already imported: {$fragment}" );
		continue;
	}
	WP_CLI::runcommand( 'media import ' . escapeshellarg( $path ), array( 'exit_error' => false ) );
}

WP_CLI::log( '=== 4/6: Site scaffolding (pages, menus, front page, logo) ===' );
eqc_run_step( '00-site-setup.php' );

WP_CLI::log( '=== 5/6: Elementor kit, demo posts, forms, SEO meta ===' );
eqc_run_step( '01-elementor-kit.php', 1 );
eqc_run_step( '02-demo-posts.php' );
eqc_run_step( '03-fluentforms.php', 1 );
eqc_run_step( '04-seo-meta.php' );

WP_CLI::log( '=== 6/6: Elementor page content ===' );
foreach ( glob( __DIR__ . '/pages/*.php' ) as $page_script ) {
	$relative = 'pages/' . basename( $page_script );
	WP_CLI::log( "Building: {$relative}" );
	eqc_run_step( $relative, 1 );
}

WP_CLI::success( 'Bootstrap complete: plugins, theme, media, pages, and content are in place.' );
