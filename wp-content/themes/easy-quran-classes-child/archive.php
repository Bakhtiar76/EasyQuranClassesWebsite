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
			<span class="eqc-eyebrow"><?php eqc_icon( 'book-open' ); ?> <?php esc_html_e( 'Latest News', 'easy-quran-classes' ); ?></span>
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
			<div class="eqc-grid eqc-grid--blog">
				<?php
				$i = 0;
				while ( have_posts() ) :
					the_post();
					$i++;
					?>
					<article <?php post_class( 'eqc-card eqc-card--blog' ); ?> <?php eqc_reveal_attrs( min( $i, 3 ) ); ?>>
						<a class="eqc-blog-media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'eqc-blog-card' ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/svg/corner-motif.svg' ); ?>" alt="" loading="lazy" style="background:var(--eqc-cream-100);object-fit:contain;padding:2rem;" />
							<?php endif; ?>
						</a>
						<div class="eqc-blog-body">
							<div class="eqc-blog-meta">
								<?php
								$cats = get_the_category();
								if ( ! empty( $cats ) ) {
									echo esc_html( $cats[0]->name );
									echo ' &middot; ';
								}
								echo esc_html( get_the_date() );
								?>
							</div>
							<h3><a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit;"><?php the_title(); ?></a></h3>
							<p class="eqc-blog-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
							<a class="eqc-read-more" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Read More', 'easy-quran-classes' ); ?> <?php eqc_icon( 'arrow-right' ); ?>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

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
