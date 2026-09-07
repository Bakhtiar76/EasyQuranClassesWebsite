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
	<div class="eqc-container eqc-header-bar">
		<div class="eqc-logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="eqc-logo-text"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
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
			<a class="eqc-btn eqc-btn--bronze eqc-header-cta" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
				<?php eqc_icon( 'calendar' ); ?>
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
<div class="eqc-nav-drawer" id="eqc-nav-drawer" aria-hidden="true">
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
		<?php eqc_icon( 'calendar' ); ?> <?php esc_html_e( 'Free Trial', 'easy-quran-classes' ); ?>
	</a>
	<a class="eqc-btn eqc-btn--secondary" href="<?php echo eqc_whatsapp_url(); ?>">
		<?php eqc_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Chat on WhatsApp', 'easy-quran-classes' ); ?>
	</a>
</div>

<main id="eqc-content">
