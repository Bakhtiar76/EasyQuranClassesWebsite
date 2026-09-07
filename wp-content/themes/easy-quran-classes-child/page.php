<?php
/**
 * Default page template. Neither this child theme nor Hello Elementor
 * ships a page.php, so without this file WordPress falls back all the way
 * to index.php — which prints its own generic `<h1 class="entry-title">`
 * before the_content(), producing a second, duplicate H1 on every
 * Elementor-built page (each of which already opens with its own H1 in
 * the Hero section). One H1 per page is an accessibility requirement
 * (DESIGN.md §22), so this template intentionally omits the title.
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
