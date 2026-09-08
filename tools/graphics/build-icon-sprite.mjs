// Regenerate inc/icon-sprite.php from Lucide (UI icons, ISC license) and
// Simple Icons (brand marks, CC0), replacing the 27 hand-drawn symbols.
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
import { starPolygonPath } from './lib/geometry.mjs';

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
	certificate: 'Award',
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
	// Global chrome parity (QA/design-review/global-chrome.md #4, #6): the
	// header CTA is a gift + FREE TRIAL, and the footer CTA button carries a
	// circled double chevron. Both come from Lucide as outline glyphs so they
	// sit on the same 24px grid and 1.75px stroke as the rest of the family
	// (DESIGN.md §11) — the reference draws the gift filled, which would be a
	// second icon style for one button.
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
	// Lucide ships 2px stroke on a 24x24 grid; DESIGN.md §11 specifies
	// 1.75px for this project's icon family — override at the symbol
	// level so every eqc-icon-* stays visually consistent regardless of
	// source library, exactly as the hand-drawn sprite did.
	symbols += `\t\t<symbol id="eqc-icon-${id}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">${cleaned}</symbol>\n\n`;
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

const php = `<?php
/**
 * Inline SVG icon sprite — DESIGN.md §11.
 *
 * Generated by tools/graphics/build-icon-sprite.mjs from Lucide (UI icons,
 * ISC license) and Simple Icons (brand marks, CC0) — do not hand-edit;
 * re-run the generator instead. Every symbol id matches the previous
 * hand-drawn sprite exactly, so no call site elsewhere in the theme or
 * tools/ needed to change. Brand marks (whatsapp/facebook/twitter/
 * instagram/youtube) are shipped filled as their real logos — a
 * deliberate, documented exception to the outline family for everything
 * else (see tools/graphics/README.md).
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

writeFileSync('wp-content/themes/easy-quran-classes-child/inc/icon-sprite.php', php);
console.log(`Wrote ${Object.keys(LUCIDE_MAP).length + Object.keys(BRAND_MAP).length + 1} symbols to inc/icon-sprite.php`);
