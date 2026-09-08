<?php
/**
 * Shared site footer: Free Trial CTA panel, cream footer panel, dark
 * copyright bar, closing tags.
 *
 * Anatomy follows the client reference (Assests/End.jpeg) as reviewed in
 * QA/design-review/global-chrome.md rows 5-11: one white action capsule
 * inside the dark CTA panel; a single rounded cream panel holding three
 * ruled columns; and a dark rounded copyright bar with the logo medallion
 * straddling its top edge.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="eqc-footer">
	<div class="eqc-container eqc-container--chrome">
		<section class="eqc-footer-cta" aria-labelledby="eqc-footer-cta-title" <?php eqc_reveal_attrs( 0 ); ?>>
			<div class="eqc-footer-cta-text">
				<?php
				/*
				 * The reference's CTA eyebrow is not the site's pill
				 * `.eqc-eyebrow` — it is a circled icon beside a gold
				 * label that carries its own underline rule. Kept as a
				 * distinct class rather than overriding every property of
				 * the pill, so neither treatment fights the other.
				 */
				?>
				<span class="eqc-cta-eyebrow">
					<span class="eqc-cta-eyebrow-icon"><?php eqc_icon( 'book-open' ); ?></span>
					<span class="eqc-cta-eyebrow-label"><?php esc_html_e( 'Start Your Journey', 'easy-quran-classes' ); ?></span>
				</span>
				<h2 id="eqc-footer-cta-title">
					<?php esc_html_e( 'Your First Class Is Free.', 'easy-quran-classes' ); ?>
					<span class="eqc-cta-title-alt"><?php esc_html_e( 'Start This Week.', 'easy-quran-classes' ); ?></span>
				</h2>
				<span class="eqc-cta-rule" aria-hidden="true"></span>
				<?php
				/*
				 * The reference breaks this paragraph across three lines.
				 * Each line is its own block so the break points hold at
				 * desktop, and each still wraps naturally when the column
				 * is narrower — no fixed width, no <br> that can't reflow.
				 */
				?>
				<p class="eqc-cta-body">
					<span><?php esc_html_e( 'Tell us the student’s age, level, and the times that suit you.', 'easy-quran-classes' ); ?></span>
					<span><?php esc_html_e( 'We’ll match a teacher and confirm your trial –', 'easy-quran-classes' ); ?></span>
					<span><?php esc_html_e( 'usually within a day.', 'easy-quran-classes' ); ?></span>
				</p>
			</div>

			<?php
			// One white capsule holding the avatar stack, the student-count
			// circle and the primary CTA button, as the reference draws it.
			$eqc_footer_avatars = array( 'student-avatar-boy', 'testimonial-aisha-khan', 'testimonial-abduallah-omar', 'testimonial-mohammed-rizwan' );
			?>
			<div class="eqc-cta-capsule">
				<div class="eqc-avatar-stack">
					<?php
					foreach ( $eqc_footer_avatars as $eqc_avatar_slug ) :
						list( $eqc_avatar_url ) = eqc_seed_image( $eqc_avatar_slug );
						if ( ! $eqc_avatar_url ) {
							continue;
						}
						?>
						<img src="<?php echo esc_url( $eqc_avatar_url ); ?>" alt="" width="112" height="112" loading="lazy" decoding="async" />
					<?php endforeach; ?>
				</div>
				<p class="eqc-cta-stat">
					<?php // TEMP staging figure taken from the reference image; see QA/PLACEHOLDER-REGISTER.md. ?>
					<strong>+1.5K</strong>
					<span><?php esc_html_e( 'Happy Students', 'easy-quran-classes' ); ?></span>
				</p>
				<a class="eqc-btn eqc-cta-button" href="<?php echo esc_url( home_url( '/free-trial/' ) ); ?>">
					<span class="eqc-cta-button-icon"><?php eqc_icon( 'chevrons-right' ); ?></span>
					<?php esc_html_e( 'Book My Free Trial Class', 'easy-quran-classes' ); ?>
				</a>
			</div>
		</section>

		<div class="eqc-footer-panel">
			<div class="eqc-footer-grid">
				<div class="eqc-footer-col eqc-footer-about">
					<div class="eqc-logo eqc-logo--footer">
						<?php echo eqc_logo_lockup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped internally. ?>
					</div>
					<p><?php echo esc_html( get_theme_mod( 'eqc_footer_about', '' ) ); ?></p>
					<div class="eqc-footer-social">
						<?php
						$eqc_socials = array(
							'facebook'  => __( 'Facebook', 'easy-quran-classes' ),
							'twitter'   => __( 'Twitter', 'easy-quran-classes' ),
							'instagram' => __( 'Instagram', 'easy-quran-classes' ),
							'youtube'   => __( 'YouTube', 'easy-quran-classes' ),
						);
						foreach ( $eqc_socials as $eqc_network => $eqc_network_label ) :
							$eqc_social_href = eqc_social_url( 'eqc_social_' . $eqc_network );
							if ( $eqc_social_href ) :
								?>
								<a class="eqc-social-icon" href="<?php echo esc_url( $eqc_social_href ); ?>" target="_blank" rel="noopener noreferrer">
									<?php eqc_icon( $eqc_network ); ?>
									<span class="eqc-visually-hidden"><?php echo esc_html( $eqc_network_label ); ?></span>
								</a>
							<?php else : ?>
								<?php
								/*
								 * No account URL has been supplied yet. The
								 * reference shows four marks here, so the row
								 * is drawn — but as a non-interactive,
								 * visibly-muted span, never a link to an
								 * invented profile. Screen readers get the
								 * network name and its pending state.
								 */
								?>
								<span class="eqc-social-icon eqc-social-icon--pending">
									<?php eqc_icon( $eqc_network ); ?>
									<span class="eqc-visually-hidden">
										<?php
										/* translators: %s: social network name. */
										printf( esc_html__( '%s — profile coming soon', 'easy-quran-classes' ), esc_html( $eqc_network_label ) );
										?>
									</span>
								</span>
								<?php
							endif;
						endforeach;
						?>
					</div>
				</div>

				<div class="eqc-footer-col eqc-footer-col--contact">
					<h3 class="eqc-footer-heading"><?php esc_html_e( 'Get In Touch!', 'easy-quran-classes' ); ?></h3>
					<ul class="eqc-footer-contact">
						<?php
						$eqc_address = get_theme_mod( 'eqc_address', '' );
						$eqc_phone   = get_theme_mod( 'eqc_phone_display', '' );
						$eqc_email   = get_theme_mod( 'eqc_contact_email', '' );
						?>
						<?php if ( $eqc_address ) : ?>
							<li>
								<span class="eqc-contact-icon"><?php eqc_icon( 'map-pin' ); ?></span>
								<span><?php echo esc_html( $eqc_address ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $eqc_phone ) : ?>
							<li>
								<span class="eqc-contact-icon"><?php eqc_icon( 'phone' ); ?></span>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $eqc_phone ) ); ?>"><?php echo esc_html( $eqc_phone ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $eqc_email ) : ?>
							<li>
								<span class="eqc-contact-icon"><?php eqc_icon( 'mail' ); ?></span>
								<a href="mailto:<?php echo esc_attr( $eqc_email ); ?>"><?php echo esc_html( $eqc_email ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="eqc-footer-col eqc-footer-col--links">
					<h3 class="eqc-footer-heading"><?php esc_html_e( 'Quick Links', 'easy-quran-classes' ); ?></h3>
					<?php
					// `link_before` puts the reference's gold chevron bullet
					// inside each menu link straight from the shared sprite,
					// so no custom Walker is needed to decorate the list.
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'eqc-footer-links',
							'fallback_cb'    => false,
							'link_before'    => eqc_get_icon_html( 'chevron-right', 'eqc-footer-link-chevron' ),
						)
					);
					?>
				</div>
			</div>

			<div class="eqc-footer-bottom">
				<span class="eqc-footer-medallion" aria-hidden="true">
					<?php echo eqc_logo_mark_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped internally, static local SVG file. ?>
				</span>
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'easy-quran-classes' ); ?></p>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
