<?php
/**
 * Single blog post template — readable typography, DESIGN.md §16/§18.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'eqc-single-post' ); ?>>
		<header class="eqc-section eqc-section--tight eqc-section--cream">
			<div class="eqc-container eqc-container--narrow">
				<div class="eqc-blog-meta" style="justify-content:center;">
					<?php
					$cats = get_the_category();
					if ( ! empty( $cats ) ) {
						echo esc_html( $cats[0]->name ) . ' &middot; ';
					}
					echo esc_html( get_the_date() );
					?>
				</div>
				<h1 style="text-align:center;"><?php the_title(); ?></h1>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="eqc-container" style="margin-bottom:var(--eqc-section-space);">
				<div style="max-width:var(--eqc-content-max);margin-inline:auto;border-radius:var(--eqc-radius-panel);overflow:hidden;aspect-ratio:16/8;">
					<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="eqc-container eqc-container--text eqc-single-content" style="padding-bottom:var(--eqc-section-space);font-size:var(--eqc-fs-body-l);">
			<?php the_content(); ?>
		</div>

		<div class="eqc-container eqc-container--narrow" style="padding-bottom:var(--eqc-section-space);">
			<div class="eqc-card" style="display:flex;flex-wrap:wrap;gap:1.25rem;align-items:center;justify-content:space-between;">
				<div>
					<h3 style="margin:0 0 0.25em;"><?php esc_html_e( 'Ready to start your own Quran learning journey?', 'easy-quran-classes' ); ?></h3>
					<p style="margin:0;color:var(--eqc-muted);"><?php esc_html_e( 'Book a free trial class and get matched with a qualified teacher.', 'easy-quran-classes' ); ?></p>
				</div>
				<a class="eqc-btn eqc-btn--bronze" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
					<?php eqc_icon( 'calendar' ); ?> <?php esc_html_e( 'Book Free Trial', 'easy-quran-classes' ); ?>
				</a>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
