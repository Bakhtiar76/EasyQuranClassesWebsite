<?php
/**
 * Customizer: the one place non-technical admins edit contact/social details
 * that appear in the header, footer and Contact page. Keeping these here
 * (instead of hardcoded in Elementor text) means one edit updates every page.
 *
 * Current values are clearly-marked placeholders pending client confirmation
 * (see CLAUDE.md Content Integrity / TASK-WEBSITE.md Phase C) — safe dummy
 * data, never invented as fact on the front end without a "confirm" step
 * in the admin help text.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the "Easy Quran Classes — Contact & Social" Customizer panel.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function eqc_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'eqc_contact_section',
		array(
			'title'       => __( 'Easy Quran Classes — Contact & Social', 'easy-quran-classes' ),
			'priority'    => 30,
			'description' => __( 'Header/footer contact details and social links. Placeholder values are marked TEMP and must be replaced with confirmed client details before launch.', 'easy-quran-classes' ),
		)
	);

	$fields = array(
		'eqc_contact_email'   => array( 'label' => __( 'Contact email', 'easy-quran-classes' ), 'sanitize' => 'sanitize_email' ),
		'eqc_whatsapp_number' => array( 'label' => __( 'WhatsApp number (digits only, with country code, TEMP placeholder)', 'easy-quran-classes' ), 'sanitize' => 'sanitize_text_field' ),
		'eqc_whatsapp_display'=> array( 'label' => __( 'WhatsApp number (displayed, TEMP placeholder)', 'easy-quran-classes' ), 'sanitize' => 'sanitize_text_field' ),
		'eqc_phone_display'   => array( 'label' => __( 'Phone number (displayed, TEMP placeholder)', 'easy-quran-classes' ), 'sanitize' => 'sanitize_text_field' ),
		'eqc_address'         => array( 'label' => __( 'Postal address (TEMP placeholder, remove if none)', 'easy-quran-classes' ), 'sanitize' => 'sanitize_text_field' ),
		'eqc_footer_about'    => array( 'label' => __( 'Footer about text', 'easy-quran-classes' ), 'sanitize' => 'sanitize_textarea_field' ),
		'eqc_social_facebook' => array( 'label' => __( 'Facebook URL (TEMP, leave blank to hide)', 'easy-quran-classes' ), 'sanitize' => 'esc_url_raw' ),
		'eqc_social_twitter'  => array( 'label' => __( 'X / Twitter URL (TEMP, leave blank to hide)', 'easy-quran-classes' ), 'sanitize' => 'esc_url_raw' ),
		'eqc_social_instagram'=> array( 'label' => __( 'Instagram URL (TEMP, leave blank to hide)', 'easy-quran-classes' ), 'sanitize' => 'esc_url_raw' ),
		'eqc_social_youtube'  => array( 'label' => __( 'YouTube URL (TEMP, leave blank to hide)', 'easy-quran-classes' ), 'sanitize' => 'esc_url_raw' ),
	);

	foreach ( $fields as $id => $args ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '', // Local seed values live only in tools/00-site-setup.php.
				'sanitize_callback' => $args['sanitize'],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $args['label'],
					'section' => 'eqc_contact_section',
					'type'    => ( 'eqc_footer_about' === $id ) ? 'textarea' : 'text',
				)
			)
		);
	}
}
add_action( 'customize_register', 'eqc_customize_register' );

/**
 * Helper: has a social URL actually been filled in (vs. the empty default)?
 *
 * @param string $mod_name Theme mod key.
 * @return string Escaped URL, or '' if unset.
 */
function eqc_social_url( $mod_name ) {
	$value = get_theme_mod( $mod_name, '' );
	return $value ? esc_url( $value ) : '';
}
