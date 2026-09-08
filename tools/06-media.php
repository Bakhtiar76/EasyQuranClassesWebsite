<?php
/** Local media sync. Run with --user=1; optional argument `prepare` writes WebP exports to /backups/design-media. */
if ( ! defined( 'WP_CLI' ) ) { exit; }
if ( 'local' !== wp_get_environment_type() || 'localhost' !== wp_parse_url( home_url(), PHP_URL_HOST ) || ! current_user_can( 'upload_files' ) ) {
	WP_CLI::error( 'Media sync requires localhost, the local environment and --user=1.' );
}
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
$prepare = isset( $args[0] ) && 'prepare' === $args[0];
if ( $prepare ) {
	$out = '/backups/design-media';
	if ( ! wp_mkdir_p( $out ) ) { WP_CLI::error( 'Cannot create media export directory.' ); }
	foreach ( glob( '/media-staging/*-source.*' ) as $source ) {
		$slug = preg_replace( '/-source\.[^.]+$/', '', basename( $source ) );
		$editor = wp_get_image_editor( $source );
		if ( is_wp_error( $editor ) ) { WP_CLI::error( $editor->get_error_message() ); }
		// Preserve the complete photograph. Circular/arch treatments belong in CSS.
		$limit = preg_match( '/^(teacher-|testimonial-|student-avatar-)/', $slug ) ? 800 : 1800;
		$size = $editor->get_size();
		if ( $size['width'] > $limit || $size['height'] > $limit ) {
			$result = $editor->resize( $limit, $limit, false );
			if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
		}
		$editor->set_quality( 86 );
		$result = $editor->save( $out . '/' . $slug . '.webp', 'image/webp' );
		if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
		WP_CLI::log( $slug . ': ' . $result['width'] . 'x' . $result['height'] . ', ' . filesize( $result['path'] ) . ' bytes' );
	}
	WP_CLI::success( 'Prepared WebP exports. Review and copy them into local/media-staging before syncing.' );
	return;
}

foreach ( glob( '/media-staging/*.webp' ) as $source ) {
	$name = basename( $source );
	// Exact basename matching avoids confusing the complete logo with its mark.
	$matches = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1,
		'fields' => 'ids', 'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => $name, 'compare' => 'LIKE' ) ) ) );
	$id = 0;
	foreach ( $matches as $candidate ) {
		if ( $name === basename( get_attached_file( $candidate ) ) ) { $id = $candidate; break; }
	}
	if ( ! $id ) {
		$temp = wp_tempnam( $name );
		if ( ! $temp || ! copy( $source, $temp ) ) { WP_CLI::error( 'Cannot stage ' . $name ); }
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $temp ), 0 );
		if ( is_wp_error( $id ) ) { wp_delete_file( $temp ); WP_CLI::error( $id->get_error_message() ); }
		WP_CLI::log( 'Imported: ' . $name );
	} elseif ( ! is_file( get_attached_file( $id ) ) || hash_file( 'sha256', $source ) !== hash_file( 'sha256', get_attached_file( $id ) ) ) {
		$destination = get_attached_file( $id );
		if ( ! copy( $source, $destination ) ) { WP_CLI::error( 'Cannot replace ' . $name ); }
		$metadata = wp_generate_attachment_metadata( $id, $destination );
		if ( empty( $metadata['width'] ) ) { WP_CLI::error( 'Failed to regenerate image sizes for ' . $name ); }
		wp_update_attachment_metadata( $id, $metadata );
		WP_CLI::log( 'Updated in place: ' . $name );
	} else {
		WP_CLI::log( 'Unchanged: ' . $name );
	}
}
WP_CLI::success( 'Local media synchronized; attachment IDs and original URLs preserved.' );
