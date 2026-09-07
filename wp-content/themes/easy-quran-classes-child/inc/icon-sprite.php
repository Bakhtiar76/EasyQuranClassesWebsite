<?php
/**
 * Inline SVG icon sprite — one consistent outline icon family (DESIGN.md §11).
 *
 * Printed once in the footer as hidden <symbol> definitions; every icon on
 * the site is then a tiny `<svg><use></use></svg>` reference via
 * eqc_icon() in inc/template-tags.php. This avoids an icon font/library
 * and keeps every icon visually consistent (24x24, 1.75px round stroke).
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the hidden SVG sprite containing every icon symbol used on the site.
 */
function eqc_print_icon_sprite() {
	?>
	<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
		<defs>

		<symbol id="eqc-icon-person" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="8" r="3.4"/>
			<path d="M5 20c0-3.6 3.2-6 7-6s7 2.4 7 6"/>
		</symbol>

		<symbol id="eqc-icon-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="9" cy="8" r="3"/>
			<path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/>
			<circle cx="17.5" cy="9" r="2.4"/>
			<path d="M15.8 14.8c2.6.3 4.7 2.3 4.7 5.2"/>
		</symbol>

		<symbol id="eqc-icon-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<rect x="3.5" y="5" width="17" height="15" rx="2.5"/>
			<path d="M3.5 9.5h17M8 3v3.6M16 3v3.6"/>
		</symbol>

		<symbol id="eqc-icon-chart-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M4 19h16"/>
			<path d="M5 15l4.5-5 3.5 3 5.5-6.5"/>
			<path d="M14.5 5.8h4.5v4.5"/>
		</symbol>

		<symbol id="eqc-icon-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M12 3.5l7 2.6v5.4c0 4.6-3 7.6-7 9-4-1.4-7-4.4-7-9V6.1z"/>
			<path d="M9 12.2l2.1 2.1 4-4.3"/>
		</symbol>

		<symbol id="eqc-icon-headset" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M4 13v-1a8 8 0 0116 0v1"/>
			<rect x="3" y="13" width="4" height="6" rx="1.6"/>
			<rect x="17" y="13" width="4" height="6" rx="1.6"/>
			<path d="M19 19v1a3 3 0 01-3 3h-2.5"/>
		</symbol>

		<symbol id="eqc-icon-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="12" r="8.5"/>
			<path d="M3.5 12h17M12 3.5c2.6 2.3 4 5.3 4 8.5s-1.4 6.2-4 8.5c-2.6-2.3-4-5.3-4-8.5s1.4-6.2 4-8.5z"/>
		</symbol>

		<symbol id="eqc-icon-certificate" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="8.5" r="5"/>
			<path d="M9 12.8l-1.4 7 4.4-2.4 4.4 2.4-1.4-7"/>
		</symbol>

		<symbol id="eqc-icon-book-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M12 6.5c-1.6-1.3-4-2-6.5-2-1 0-1.5.2-1.5.2v12.8s.5-.2 1.5-.2c2.5 0 4.9.7 6.5 2 1.6-1.3 4-2 6.5-2 1 0 1.5.2 1.5.2V4.7s-.5-.2-1.5-.2c-2.5 0-4.9.7-6.5 2z"/>
			<path d="M12 6.5v13"/>
		</symbol>

		<symbol id="eqc-icon-whatsapp" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M6.4 17.5L4 20l2.6-.7A8 8 0 1012 20a7.9 7.9 0 01-5.6-2.5z"/>
			<path d="M9 9.3c0-.6.5-1.1 1-1.1s.8.2 1 .6c.3.6.9 1.8 1 2 .1.3 0 .5-.2.8l-.5.6c-.2.2-.2.4 0 .7.3.6 1.6 2 3 2.5.3.1.5 0 .6-.1l.6-.7c.2-.3.5-.3.8-.2l1.8.9c.3.1.5.3.5.7 0 1-1.3 1.7-2.2 1.7-1.9 0-4.6-1.1-6.3-3.3C8.6 13 9 11 9 9.3z"/>
		</symbol>

		<symbol id="eqc-icon-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<rect x="3" y="5.5" width="18" height="13" rx="2.2"/>
			<path d="M4 7l8 6 8-6"/>
		</symbol>

		<symbol id="eqc-icon-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M12 21s-6.8-6-6.8-11.2A6.8 6.8 0 0112 3a6.8 6.8 0 016.8 6.8C18.8 15 12 21 12 21z"/>
			<circle cx="12" cy="9.8" r="2.4"/>
		</symbol>

		<symbol id="eqc-icon-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M6 3.5h3l1.4 4-2 1.6a12 12 0 006.5 6.5l1.6-2 4 1.4v3a1.6 1.6 0 01-1.7 1.6A16.5 16.5 0 014.4 5.2 1.6 1.6 0 016 3.5z"/>
		</symbol>

		<symbol id="eqc-icon-facebook" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M14.5 21v-7.5H17l.4-3H14.5V8.4c0-1 .3-1.7 1.8-1.7H17.5V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.2H8.3v3h2.5V21z"/>
		</symbol>

		<symbol id="eqc-icon-twitter" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M20.5 6.6c-.6.3-1.3.5-2 .6a3.5 3.5 0 001.5-1.9c-.7.4-1.4.7-2.2.9a3.4 3.4 0 00-5.9 3.1A9.7 9.7 0 014.3 5.6a3.4 3.4 0 001.1 4.6c-.6 0-1.1-.2-1.6-.4v.1c0 1.7 1.2 3 2.8 3.4-.5.1-1 .2-1.6.1a3.5 3.5 0 003.3 2.4A6.9 6.9 0 013 17.2 9.7 9.7 0 008.3 19c6.3 0 9.8-5.2 9.8-9.8v-.4c.7-.5 1.3-1.1 1.7-1.9z"/>
		</symbol>

		<symbol id="eqc-icon-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<rect x="3.5" y="3.5" width="17" height="17" rx="5"/>
			<circle cx="12" cy="12" r="4"/>
			<circle cx="17" cy="7" r="0.9" fill="currentColor" stroke="none"/>
		</symbol>

		<symbol id="eqc-icon-youtube" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<rect x="2.5" y="6" width="19" height="12" rx="3.5"/>
			<path d="M10.5 9.7l5 2.3-5 2.3z" fill="currentColor" stroke="none"/>
		</symbol>

		<symbol id="eqc-icon-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M4 12h15.5M14 6.5l5.5 5.5-5.5 5.5"/>
		</symbol>

		<symbol id="eqc-icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<path d="M4.5 12.5l5 5 10-11"/>
		</symbol>

		<symbol id="eqc-icon-graduation-cap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="M2.5 9L12 5l9.5 4-9.5 4-9.5-4z"/>
			<path d="M6.5 11v4.5c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5V11"/>
			<path d="M21.5 9v5.5"/>
		</symbol>

		<symbol id="eqc-icon-quote" viewBox="0 0 24 24" fill="currentColor" stroke="none">
			<path d="M9.5 6.5C6.4 7.7 4.5 10 4.5 13.2c0 2.4 1.7 4.3 3.9 4.3 1.9 0 3.3-1.4 3.3-3.2 0-1.7-1.2-3-2.8-3.1.4-1.6 1.7-3 3.3-3.7zM18 6.5c-3.1 1.2-5 3.5-5 6.7 0 2.4 1.7 4.3 3.9 4.3 1.9 0 3.3-1.4 3.3-3.2 0-1.7-1.2-3-2.8-3.1.4-1.6 1.7-3 3.3-3.7z"/>
		</symbol>

		<symbol id="eqc-icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M12 4.5v15M4.5 12h15"/>
		</symbol>

		<symbol id="eqc-icon-chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M5.5 9l6.5 6.5L18.5 9"/>
		</symbol>

		<symbol id="eqc-icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M4 7h16M4 12h16M4 17h16"/>
		</symbol>

		<symbol id="eqc-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M5.5 5.5l13 13M18.5 5.5l-13 13"/>
		</symbol>

		<symbol id="eqc-icon-star" viewBox="0 0 24 24" fill="currentColor" stroke="none">
			<path d="M12 3.5l2.6 5.4 5.9.7-4.3 4.1 1.1 5.9L12 16.7l-5.3 2.9 1.1-5.9-4.3-4.1 5.9-.7z"/>
		</symbol>

		<symbol id="eqc-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="12" r="8.5"/>
			<path d="M12 7.5V12l3.2 2"/>
		</symbol>

		</defs>
	</svg>
	<?php
}
