<?php
/**
 * Shared site footer: Free Trial CTA panel, dark forest footer, closing tags.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="eqc-footer">
	<div class="eqc-container">
		<div class="eqc-footer-cta" <?php eqc_reveal_attrs( 0 ); ?>>
			<div class="eqc-footer-cta-text">
				<span class="eqc-eyebrow"><?php eqc_icon( 'book-open' ); ?> <?php esc_html_e( 'Start Your Journey', 'easy-quran-classes' ); ?></span>
				<h2><?php esc_html_e( 'Your First Class Is Free. Start This Week.', 'easy-quran-classes' ); ?></h2>
				<p><?php esc_html_e( "Tell us the student's age, level, and the times that suit you. We'll match a teacher and confirm your trial.", 'easy-quran-classes' ); ?></p>
			</div>
			<div class="eqc-footer-cta-actions">
				<a class="eqc-btn eqc-btn--bronze" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
					<?php eqc_icon( 'calendar' ); ?> <?php esc_html_e( 'Book My Free Trial Class', 'easy-quran-classes' ); ?>
				</a>
				<a class="eqc-btn eqc-btn--secondary" href="<?php echo eqc_whatsapp_url( __( 'Assalamu alaikum, I would like to know more about Easy Quran Classes.', 'easy-quran-classes' ) ); ?>">
					<?php eqc_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Chat on WhatsApp', 'easy-quran-classes' ); ?>
				</a>
			</div>
		</div>

		<div class="eqc-footer-grid">
			<div class="eqc-footer-col eqc-footer-about">
				<?php if ( has_custom_logo() ) : ?>
					<div class="eqc-logo eqc-logo--footer"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<p class="eqc-logo-text"><?php bloginfo( 'name' ); ?></p>
				<?php endif; ?>
				<p><?php echo esc_html( get_theme_mod( 'eqc_footer_about', '' ) ); ?></p>
				<div class="eqc-footer-social">
					<?php
					$socials = array(
						'facebook'  => eqc_social_url( 'eqc_social_facebook' ),
						'twitter'   => eqc_social_url( 'eqc_social_twitter' ),
						'instagram' => eqc_social_url( 'eqc_social_instagram' ),
						'youtube'   => eqc_social_url( 'eqc_social_youtube' ),
					);
					foreach ( $socials as $network => $url ) :
						if ( ! $url ) {
							continue;
						}
						?>
						<a class="eqc-social-icon" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php eqc_icon( $network ); ?>
							<span class="eqc-visually-hidden"><?php echo esc_html( ucfirst( $network ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="eqc-footer-col">
				<h3 class="eqc-footer-heading"><?php esc_html_e( 'Get In Touch', 'easy-quran-classes' ); ?></h3>
				<ul class="eqc-footer-contact">
					<?php $address = get_theme_mod( 'eqc_address', '' ); ?>
					<?php if ( $address ) : ?>
						<li><?php eqc_icon( 'map-pin' ); ?> <span><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
					<li>
						<?php eqc_icon( 'phone' ); ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', get_theme_mod( 'eqc_phone_display', '' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'eqc_phone_display', '' ) ); ?></a>
					</li>
					<li>
						<?php eqc_icon( 'mail' ); ?>
						<a href="mailto:<?php echo esc_attr( get_theme_mod( 'eqc_contact_email', '' ) ); ?>"><?php echo esc_html( get_theme_mod( 'eqc_contact_email', '' ) ); ?></a>
					</li>
				</ul>
			</div>

			<div class="eqc-footer-col">
				<h3 class="eqc-footer-heading"><?php esc_html_e( 'Quick Links', 'easy-quran-classes' ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'eqc-footer-links',
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</div>

		<div class="eqc-footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'easy-quran-classes' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
