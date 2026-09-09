// Regenerate inc/icon-sprite.php from Lucide (outline UI, ISC license),
// Simple Icons (brand marks, CC0), and original project-owned filled UI.
//
// Every existing `eqc-icon-*` id is preserved exactly, so no call site in
// tools/pages/*.php, tools/elementor-helpers.php, header.php or footer.php
// needs to change — only what each symbol draws changes. Brand marks are
// shipped FILLED (their real logos) rather than outline-traced, which is
// a deliberate, documented exception to DESIGN.md §11's one-family rule —
// see tools/graphics/README.md.
//
// Usage: node build-icon-sprite.mjs
import * as lucide from 'lucide-static';
import * as simpleIcons from 'simple-icons';
import { readFileSync, writeFileSync } from 'node:fs';
import { optimize } from 'svgo';
import { starPolygonPath, spikedRosettePath } from './lib/geometry.mjs';

// existing id -> Lucide export name. Every id already used by tools/*.php
// and the theme templates (see inc/icon-sprite.php's current 27 symbols).
const LUCIDE_MAP = {
	person: 'User',
	users: 'Users',
	calendar: 'Calendar',
	'chart-up': 'TrendingUp',
	shield: 'ShieldCheck',
	headset: 'Headset',
	globe: 'Globe',
	certificate: 'FileBadge2',
	'book-open': 'BookOpen',
	mail: 'Mail',
	'map-pin': 'MapPin',
	phone: 'Phone',
	'arrow-right': 'ArrowRight',
	check: 'Check',
	'graduation-cap': 'GraduationCap',
	quote: 'Quote',
	plus: 'Plus',
	'chevron-down': 'ChevronDown',
	menu: 'Menu',
	close: 'X',
	star: 'Star',
	clock: 'Clock',
	// New icons the reference design needs (course/video content, list
	// bullets, pricing "recommended" sparkle) — not replacing anything.
	sparkle: 'Sparkle',
	'chevron-right': 'ChevronRight',
	'monitor-play': 'MonitorPlay',
	megaphone: 'Megaphone',
	// Retain outline variants for roles drawn that way in the reference.
	gift: 'Gift',
	'chevrons-right': 'ChevronsRight',
};

// existing id -> simple-icons export name. Shipped filled (their real
// mark), not outline-traced — outline-tracing a brand glyph is exactly
// what made the WhatsApp icon read as broken.
const BRAND_MAP = {
	whatsapp: 'siWhatsapp',
	facebook: 'siFacebook',
	twitter: null, // Reference uses the bird; vendored CC0 Simple Icons 9.21.0 asset.
	instagram: 'siInstagram',
	youtube: 'siYoutube',
};

// Original project-owned silhouettes, composed here from circles, ribbons,
// and simple curves on the same 24px grid. No third-party icon is traced or
// filled over. Separate ids let each reference role select its own anatomy
// without changing existing outline call sites. Even-odd holes stay truly
// transparent on both cream and dark surfaces (no painted background color).
const FILLED_UI = {
	'person-filled': '<circle cx="12" cy="6.6" r="4.4"/><path d="M3.2 21v-2.2c0-4.1 3.9-6.1 8.8-6.1s8.8 2 8.8 6.1V21Z"/>',
	'users-filled': '<circle cx="9" cy="7" r="3.8"/><circle cx="17.6" cy="8.4" r="2.9"/><path d="M1.5 20v-2c0-3.5 3.3-5.3 7.5-5.3s7.5 1.8 7.5 5.3v2Zm16.8 0v-2c0-2.1-.9-3.7-2.4-4.8 3.8-.8 6.6 1.4 6.6 4.3V20Z"/>',
	'shield-filled': '<path fill-rule="evenodd" d="M12 1.5C9 3.6 5.9 4.5 3 4.9v6.2c0 5 3.7 9 9 11.4 5.3-2.4 9-6.4 9-11.4V4.9c-2.9-.4-6-1.3-9-3.4Zm-5.6 10 1.7-1.7 2.7 2.7 5.3-5.3 1.7 1.7-7 7Z"/>',
	'graduation-cap-filled': '<path d="m12 2-11 5 11 5L23 7Zm-6.8 9v5.2c0 1.5 3 3.3 6.8 3.3s6.8-1.8 6.8-3.3V11L12 14.1Z"/><path d="M21 9.4h1.6v6.3H21Z"/><circle cx="21.8" cy="17.2" r="1.6"/>',
	'certificate-filled': `<path d="m7.6 12.8-2.4 9.5 3.6-1.5 2.4 1.7.8-8.6.8 8.6 2.4-1.7 3.6 1.5-2.4-9.5Z"/><path fill-rule="evenodd" d="${spikedRosettePath(12, 8.2, 6.7, 5.6, 12)} M14.5 8.2a2.5 2.5 0 1 0-5 0 2.5 2.5 0 1 0 5 0Z"/>`,
	'quote-filled': '<path d="M10.3 3.3C4.7 4.8 2 8.7 2 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Zm11 0C15.7 4.8 13 8.7 13 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Z"/>',
	'gift-filled': '<path fill-rule="evenodd" d="M12 7.3C10.4 3.3 8.2 1.2 5.9 2 2.9 3 3.6 7.9 7.3 7.9h9.4c3.7 0 4.4-4.9 1.4-5.9-2.3-.8-4.5 1.3-6.1 5.3ZM9.5 6.2H7.3c-1.5 0-1.8-2.1-.7-2.4 1-.3 2.1.8 2.9 2.4Zm5 0h2.2c1.5 0 1.8-2.1.7-2.4-1-.3-2.1.8-2.9 2.4Z"/><path d="M2 9h9v3H2Zm11 0h9v3h-9ZM4 13.5h7V22H5a1 1 0 0 1-1-1Zm9 0h7V21a1 1 0 0 1-1 1h-6Z"/>',
	'map-pin-filled': '<path fill-rule="evenodd" d="M12 1.5A8 8 0 0 0 4 9.5c0 5 8 13 8 13s8-8 8-13a8 8 0 0 0-8-8Zm3 8a3 3 0 1 0-6 0 3 3 0 1 0 6 0Z"/>',
};

function extractInner(svgString) {
	const match = svgString.match(/<svg[^>]*>([\s\S]*)<\/svg>/);
	if (!match) throw new Error('could not parse svg: ' + svgString.slice(0, 80));
	return match[1].trim();
}

function optimizeFragment(inner, viewBox) {
	// Wrap, run svgo (cleans precision/whitespace), unwrap.
	const wrapped = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">${inner}</svg>`;
	const { data } = optimize(wrapped, { multipass: true, plugins: ['preset-default'] });
	return data.replace(/^<svg[^>]*>/, '').replace(/<\/svg>$/, '');
}

let symbols = '';

for (const [id, lucideName] of Object.entries(LUCIDE_MAP)) {
	const src = lucide[lucideName];
	if (!src) throw new Error(`Lucide icon not found: ${lucideName} (for eqc-icon-${id})`);
	const inner = extractInner(src);
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	// Preserve the established outline weight; filled reference roles use
	// explicit FILLED_UI variants instead of thickening these outlines.
	symbols += `\t\t<symbol id="eqc-icon-${id}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">${cleaned}</symbol>\n\n`;
}

for (const [id, inner] of Object.entries(FILLED_UI)) {
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	symbols += `\t\t<symbol id="eqc-icon-${id}" viewBox="0 0 24 24" fill="currentColor" stroke="none">${cleaned}</symbol>\n\n`;
}

for (const [id, siName] of Object.entries(BRAND_MAP)) {
	const icon = siName ? simpleIcons[siName] : { svg: readFileSync(new URL('./twitter-reference.svg', import.meta.url), 'utf8') };
	if (!icon) throw new Error(`Simple Icons icon not found: ${siName} (for eqc-icon-${id})`);
	const inner = extractInner(icon.svg);
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	// Filled, currentColor — brand marks are a deliberate exception to the
	// outline family (see file header comment).
	symbols += `\t\t<symbol id="eqc-icon-${id}" viewBox="0 0 24 24" fill="currentColor">${cleaned}</symbol>\n\n`;
}

// star-8 reuses the same exact geometry as the ornament system's
// star-8-filled.svg (tools/graphics/gen-ornaments.mjs), so the pricing
// list bullet and any decorative star elsewhere are visually identical —
// computed inline here rather than fetched from the ornaments folder so
// the icon sprite has no build-order dependency on gen-ornaments.mjs.
const star8d = starPolygonPath(12, 12, 10, 8, 3);
symbols += `\t\t<symbol id="eqc-icon-star-8" viewBox="0 0 24 24" fill="currentColor"><path d="${star8d}"/></symbol>\n\n`;

// star-filled: the solid five-point rating star the hero trust row and the
// testimonial cards need. Lucide's `star` is an OUTLINE glyph carrying
// fill="none" on its own path — and because icons render through <use>, that
// presentation attribute cannot be overridden by CSS from the page (the
// referenced content is in a shadow tree, and an attribute beats an inherited
// fill anyway). So the filled star is a symbol in its own right, built from
// the same starPolygonPath construction as star-8 above rather than a second
// traced asset. See QA/design-review/home.md finding 9.
const star5d = starPolygonPath(12, 12, 10.5, 5, 2);
symbols += `\t\t<symbol id="eqc-icon-star-filled" viewBox="0 0 24 24" fill="currentColor"><path d="${star5d}"/></symbol>\n\n`;

const php = `<?php
/**
 * Inline SVG icon sprite — DESIGN.md §11.
 *
 * Generated by tools/graphics/build-icon-sprite.mjs from Lucide (outline UI,
 * ISC), Simple Icons (brand marks, CC0), and original project-owned filled
 * UI geometry (the explicit *-filled variants) — do not hand-edit;
 * re-run the generator instead. Every symbol id matches the previous
 * hand-drawn sprite exactly, so no call site elsewhere in the theme or
 * tools/ needed to change. Brand marks (whatsapp/facebook/twitter/
 * instagram/youtube) are shipped filled as their real logos — a
 * deliberate exception to the UI icon family (see tools/graphics/README.md).
 *
 * Printed once in the footer as hidden <symbol> definitions; every icon on
 * the site is then a tiny <svg><use></use></svg> reference via eqc_icon()
 * in inc/template-tags.php.
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

${symbols}\t\t</defs>
	</svg>
	<?php
}
`;

writeFileSync(new URL('../../wp-content/themes/easy-quran-classes-child/inc/icon-sprite.php', import.meta.url), php);
console.log(`Wrote ${Object.keys(LUCIDE_MAP).length + Object.keys(BRAND_MAP).length + Object.keys(FILLED_UI).length + 2} symbols to inc/icon-sprite.php`);
