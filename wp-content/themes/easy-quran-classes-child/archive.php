<?php
/**
 * Blog archive / category / tag archive template.
 * Native WordPress Posts, per DESIGN.md §16 and TASK-WEBSITE.md Phase J.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="eqc-section eqc-section--cream">
	<div class="eqc-container">
		<div class="eqc-stack eqc-section-heading eqc-section-heading--center" <?php eqc_reveal_attrs( 0 ); ?>>
			<span class="eqc-eyebrow"><?php eqc_icon( 'megaphone' ); ?> <?php esc_html_e( 'Latest News', 'easy-quran-classes' ); ?></span>
			<h1>
				<?php
				if ( is_category() ) {
					single_cat_title();
				} elseif ( is_tag() ) {
					single_tag_title();
				} elseif ( is_search() ) {
					printf(
						/* translators: %s: search query */
						esc_html__( 'Search results for "%s"', 'easy-quran-classes' ),
						esc_html( get_search_query() )
					);
				} else {
					esc_html_e( 'From the Blog', 'easy-quran-classes' );
				}
				?>
			</h1>
			<hr class="eqc-heading-rule eqc-heading-rule--draw" />
		</div>

		<?php if ( have_posts() ) : ?>
			<?php
			// Shared with the [eqc_latest_posts] shortcode (inc/template-tags.php)
			// so the Blog page and any homepage/section preview never drift apart.
			echo eqc_render_blog_cards( $wp_query->posts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped internally.
			?>

			<nav class="eqc-pagination" aria-label="<?php esc_attr_e( 'Blog pagination', 'easy-quran-classes' ); ?>" style="margin-top:var(--eqc-section-space);display:flex;justify-content:center;gap:0.75rem;">
				<?php
				echo paginate_links(
					array(
						'prev_text' => __( '&larr; Newer', 'easy-quran-classes' ),
						'next_text' => __( 'Older &rarr;', 'easy-quran-classes' ),
					)
				);
				?>
			</nav>

		<?php else : ?>
			<div class="eqc-card" style="max-width:var(--eqc-content-narrow);margin-inline:auto;text-align:center;">
				<p><?php esc_html_e( 'No posts have been published yet. Please check back soon.', 'easy-quran-classes' ); ?></p>
				<a class="eqc-btn eqc-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'easy-quran-classes' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
