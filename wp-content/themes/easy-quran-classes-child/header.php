<?php
/**
 * Shared site header: full <head>, opening <body>, and the Easy Quran
 * Classes header (logo, primary nav, Free Trial CTA, mobile drawer).
 *
 * Elementor Free has no Theme Builder, so the child theme owns this shell
 * directly (DESIGN.md §14, TASK-WEBSITE.md Phase H) instead of the parent
 * Hello Elementor header/footer template parts.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="eqc-skip-link" href="#eqc-content"><?php esc_html_e( 'Skip to content', 'easy-quran-classes' ); ?></a>

<header class="eqc-header" id="eqc-header">
	<div class="eqc-container eqc-container--chrome eqc-header-bar">
		<div class="eqc-logo">
			<?php echo eqc_logo_lockup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped internally. ?>
		</div>

		<nav class="eqc-nav-desktop" aria-label="<?php esc_attr_e( 'Primary', 'easy-quran-classes' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'eqc-nav-list',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<div class="eqc-header-actions">
			<?php // Uppercasing is presentational (shell.css) so the source string stays naturally cased and translatable. ?>
			<a class="eqc-btn eqc-btn--bronze eqc-header-cta" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
				<?php eqc_icon( 'gift' ); ?>
				<?php esc_html_e( 'Free Trial', 'easy-quran-classes' ); ?>
			</a>
			<button type="button" class="eqc-nav-toggle" aria-expanded="false" aria-controls="eqc-nav-drawer">
				<?php eqc_icon( 'menu', 'eqc-icon-open' ); ?>
				<?php eqc_icon( 'close', 'eqc-icon-close' ); ?>
				<span class="eqc-visually-hidden"><?php esc_html_e( 'Menu', 'easy-quran-classes' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div class="eqc-nav-scrim" id="eqc-nav-scrim"></div>
<?php
// Starts `inert` so the off-screen drawer's links are not tabbable (and
// stay out of the accessibility tree) before JS opens it — including when
// JS never runs, in which case the drawer can't be opened at all.
?>
<div class="eqc-nav-drawer" id="eqc-nav-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site menu', 'easy-quran-classes' ); ?>" aria-hidden="true" inert>
	<?php // A close control inside the overlay: the toggle that opened it sits behind the scrim and is made inert while the drawer is open (assets/js/eqc.js). ?>
	<button type="button" class="eqc-nav-close" data-eqc-nav-close>
		<?php eqc_icon( 'close' ); ?>
		<span class="eqc-visually-hidden"><?php esc_html_e( 'Close menu', 'easy-quran-classes' ); ?></span>
	</button>
	<nav aria-label="<?php esc_attr_e( 'Mobile', 'easy-quran-classes' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'eqc-nav-drawer-list',
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>
	<a class="eqc-btn eqc-btn--bronze" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
		<?php eqc_icon( 'gift' ); ?> <?php esc_html_e( 'Free Trial', 'easy-quran-classes' ); ?>
	</a>
	<a class="eqc-btn eqc-btn--secondary" href="<?php echo eqc_whatsapp_url(); ?>">
		<?php eqc_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Chat on WhatsApp', 'easy-quran-classes' ); ?>
	</a>
</div>

<main id="eqc-content">
