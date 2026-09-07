<?php
/**
 * Small reusable template helpers shared by header/footer/page templates.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Echo one icon from the sprite in inc/icon-sprite.php.
 *
 * @param string $name  Icon id without the "eqc-icon-" prefix.
 * @param string $class Extra classes appended to "eqc-icon".
 */
function eqc_icon( $name, $class = '' ) {
	printf(
		'<svg class="eqc-icon %s" aria-hidden="true" focusable="false"><use href="#eqc-icon-%s"></use></svg>',
		esc_attr( $class ),
		esc_attr( $name )
	);
}

/**
 * Build a wa.me link from the Customizer WhatsApp number.
 *
 * @param string $prefill Optional prefilled message text.
 * @return string
 */
function eqc_whatsapp_url( $prefill = '' ) {
	$number = preg_replace( '/[^0-9]/', '', get_theme_mod( 'eqc_whatsapp_number', '10000000000' ) );
	$url    = 'https://wa.me/' . $number;
	if ( $prefill ) {
		$url .= '?text=' . rawurlencode( $prefill );
	}
	return esc_url( $url );
}

/**
 * Print the reveal-on-scroll data attributes used by assets/js/eqc.js.
 *
 * @param int $index Stagger index within the current group (0-6).
 */
function eqc_reveal_attrs( $index = 0 ) {
	$index = max( 0, min( 6, (int) $index ) );
	printf( ' data-eqc-reveal data-eqc-reveal-index="%d" ', $index );
}

/**
 * Render a small Islamic ornament (rosette | corner-motif | divider-flower).
 *
 * @param string $which One of 'rosette', 'corner-motif', 'divider-flower', 'arch-outline'.
 * @param string $class Extra classes.
 */
function eqc_ornament( $which, $class = '' ) {
	$allowed = array( 'rosette', 'corner-motif', 'divider-flower', 'arch-outline' );
	if ( ! in_array( $which, $allowed, true ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/svg/' . $which . '.svg';
	if ( ! file_exists( $path ) ) {
		return;
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local static theme asset.
	$svg = file_get_contents( $path );
	// Namespace the class onto the root <svg> without a full DOM parse.
	$svg = preg_replace( '/<svg /', '<svg class="eqc-ornament ' . esc_attr( $class ) . '" aria-hidden="true" ', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static local SVG file, not user input.
}

/**
 * Output a heading eyebrow + H2 + gold rule block shared by every section.
 *
 * @param string $eyebrow Small label above the heading.
 * @param string $heading Heading HTML (may include a <span> for emphasis).
 * @param bool   $centered Center the block.
 */
function eqc_section_heading( $eyebrow, $heading, $centered = false ) {
	?>
	<div class="eqc-stack eqc-section-heading<?php echo $centered ? ' eqc-section-heading--center' : ''; ?>" <?php eqc_reveal_attrs( 0 ); ?>>
		<?php if ( $eyebrow ) : ?>
			<span class="eqc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>
		<h2><?php echo wp_kses_post( $heading ); ?></h2>
		<hr class="eqc-heading-rule eqc-heading-rule--draw" />
	</div>
	<?php
}

/**
 * Fetch the Media Library URL for one of the placeholder images seeded
 * during the build, by its recognizable filename fragment. Falls back to
 * an empty string (never a hardcoded absolute path) if not found, so
 * templates degrade gracefully if media is re-imported with new IDs.
 *
 * @param string $slug_fragment e.g. 'hero-online-quran-class'.
 * @return array{0:string,1:string} [url, alt]
 */
function eqc_seed_image( $slug_fragment ) {
	static $cache = array();
	if ( isset( $cache[ $slug_fragment ] ) ) {
		return $cache[ $slug_fragment ];
	}
	$query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'name'           => $slug_fragment,
			'posts_per_page' => 1,
		)
	);
	$result = array( '', '' );
	if ( $query->have_posts() ) {
		$id     = $query->posts[0]->ID;
		$result = array( wp_get_attachment_url( $id ), get_post_meta( $id, '_wp_attachment_image_alt', true ) );
	}
	wp_reset_postdata();
	$cache[ $slug_fragment ] = $result;
	return $result;
}
