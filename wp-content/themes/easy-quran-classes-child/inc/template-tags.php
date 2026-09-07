<?php
/**
 * Small reusable template helpers shared by header/footer/page templates.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * One icon's markup from the sprite in inc/icon-sprite.php, as a string.
 *
 * @param string $name  Icon id without the "eqc-icon-" prefix.
 * @param string $class Extra classes appended to "eqc-icon".
 */
function eqc_get_icon_html( $name, $class = '' ) {
	return sprintf(
		'<svg class="eqc-icon %s" aria-hidden="true" focusable="false"><use href="#eqc-icon-%s"></use></svg>',
		esc_attr( $class ),
		esc_attr( $name )
	);
}

/** Echo one icon — see eqc_get_icon_html(). */
function eqc_icon( $name, $class = '' ) {
	echo eqc_get_icon_html( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped internally.
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
 * Render the shared blog-card markup for a list of WP_Post objects.
 * Used by archive.php (the real Blog page) and by the [eqc_latest_posts]
 * shortcode below, so the two never drift apart.
 *
 * @param WP_Post[] $posts
 * @return string
 */
function eqc_render_blog_cards( $posts ) {
	if ( empty( $posts ) ) {
		return '<div class="eqc-card" style="max-width:var(--eqc-content-narrow);margin-inline:auto;text-align:center;"><p>' . esc_html__( 'No posts have been published yet. Please check back soon.', 'easy-quran-classes' ) . '</p></div>';
	}

	$html = '<div class="eqc-grid eqc-grid--blog">';
	$i    = 0;
	foreach ( $posts as $post ) {
		$i++;
		$permalink = get_permalink( $post );
		$cats      = get_the_category( $post->ID );
		$cat_name  = ! empty( $cats ) ? esc_html( $cats[0]->name ) . ' &middot; ' : '';
		$ribbon    = ! empty( $cats ) ? '<span class="eqc-blog-ribbon">' . esc_html( $cats[0]->name ) . '</span>' : '';
		$thumb     = has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'eqc-blog-card' ) : '';

		$html .= '<article class="eqc-card eqc-card--blog" data-eqc-reveal data-eqc-reveal-index="' . min( $i, 3 ) . '">';
		$html .= '<a class="eqc-blog-media" href="' . esc_url( $permalink ) . '">' . $thumb . $ribbon . '</a>';
		$html .= '<div class="eqc-blog-body">';
		$html .= '<div class="eqc-blog-meta">' . $cat_name . esc_html( get_the_date( '', $post ) ) . '</div>';
		$html .= '<h3><a href="' . esc_url( $permalink ) . '" style="text-decoration:none;color:inherit;">' . esc_html( get_the_title( $post ) ) . '</a></h3>';
		$html .= '<p class="eqc-blog-excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $post ), 18 ) ) . '</p>';
		/* translators: %s: post title, read by screen readers only — the visible link text stays the short "Read More". */
		$read_more_label = sprintf( __( 'Read more: %s', 'easy-quran-classes' ), get_the_title( $post ) );
		$html           .= '<a class="eqc-read-more" href="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( $read_more_label ) . '">' . esc_html__( 'Read More', 'easy-quran-classes' ) . ' ' . eqc_get_icon_html( 'arrow-right' ) . '</a>';
		$html .= '</div></article>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * [eqc_latest_posts count="3"] — the site's only "dynamic Posts" mechanism
 * for Elementor Free (which ships no query/Posts widget). Registered here
 * instead of a plugin: it is a three-line WP_Query wrapped around the same
 * card markup archive.php already uses, always reflects the live Posts
 * table, and an admin drops it anywhere via Elementor's Shortcode widget.
 */
function eqc_latest_posts_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 3 ), $atts );
	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['count'],
			'ignore_sticky_posts' => true,
		)
	);
	$html = eqc_render_blog_cards( $query->posts );
	wp_reset_postdata();
	return $html;
}
add_shortcode( 'eqc_latest_posts', 'eqc_latest_posts_shortcode' );

/**
 * Fetch the Media Library URL for one of the placeholder images seeded
 * during the build, by its recognizable filename fragment. Falls back to
 * an empty string (never a hardcoded absolute path) if not found, so
 * templates degrade gracefully if media is re-imported with new IDs.
 *
 * Matches the `_wp_attached_file` postmeta (the real source filename), not
 * `post_name` — `wp media import --title="..."` derives post_name from the
 * given title, not the filename, so a post_name match silently misses (see
 * eqc_media_id() in tools/elementor-helpers.php, which hit the exact same
 * bug; this function went uncalled until the footer avatar-stack needed
 * it, so it was never caught until now).
 *
 * @param string $slug_fragment e.g. 'hero-online-quran-class'.
 * @return array{0:string,1:string} [url, alt]
 */
function eqc_seed_image( $slug_fragment ) {
	global $wpdb;
	static $cache = array();
	if ( isset( $cache[ $slug_fragment ] ) ) {
		return $cache[ $slug_fragment ];
	}
	$id     = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1", '%' . $wpdb->esc_like( $slug_fragment ) . '%' ) );
	$result = array( '', '' );
	if ( $id ) {
		$result = array( wp_get_attachment_url( (int) $id ), get_post_meta( (int) $id, '_wp_attachment_image_alt', true ) );
	}
	$cache[ $slug_fragment ] = $result;
	return $result;
}
