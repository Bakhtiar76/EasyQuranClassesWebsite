<?php
/**
 * 404 template.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="eqc-section eqc-section--cream">
	<div class="eqc-container eqc-container--narrow" style="text-align:center;">
		<span class="eqc-eyebrow">404</span>
		<h1><?php esc_html_e( 'Page not found', 'easy-quran-classes' ); ?></h1>
		<p style="max-width:52ch;margin-inline:auto;color:var(--eqc-muted);">
			<?php esc_html_e( "The page you're looking for may have moved or no longer exists.", 'easy-quran-classes' ); ?>
		</p>
		<div class="eqc-btn-group" style="justify-content:center;margin-top:1.5rem;">
			<a class="eqc-btn eqc-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'easy-quran-classes' ); ?></a>
			<a class="eqc-btn eqc-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'easy-quran-classes' ); ?></a>
		</div>
	</div>
</section>
<?php get_footer(); ?>
