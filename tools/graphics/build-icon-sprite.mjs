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
import { readFileSync, writeFileSync, mkdirSync, readdirSync, rmSync } from 'node:fs';
import { optimize } from 'svgo';
import { starPolygonPath, spikedRosettePath } from './lib/geometry.mjs';
import {
	bust, bustBehind, headWithBand, SHIELD, star5, sparkle4, lobedRosettePath,
	mortarboard, bookOpen, quotePair,
} from './lib/icon-shapes.mjs';

// existing id -> Lucide export name. Every id already used by tools/*.php
// and the theme templates (see inc/icon-sprite.php's current 27 symbols).
//
// Icons LEAVE this map when the audit against the client's screenshots showed
// Lucide's drawing is not the drawing the design uses — see ORIGINAL_OUTLINE
// below and tools/graphics/reference/icons/README.md for the evidence. What
// stays here was checked side-by-side and matched.
const LUCIDE_MAP = {
	person: 'User',
	shield: 'ShieldCheck',
	'map-pin': 'MapPin',
	'arrow-right': 'ArrowRight',
	check: 'Check',
	quote: 'Quote',
	plus: 'Plus',
	'chevron-down': 'ChevronDown',
	menu: 'Menu',
	close: 'X',
	star: 'Star',
	sparkle: 'Sparkle',
	'chevron-right': 'ChevronRight',
	'monitor-play': 'MonitorPlay',
	gift: 'Gift',
	'chevrons-right': 'ChevronsRight',
};

// Project-owned OUTLINE geometry, drawn from the client's screenshots where
// Lucide's icon of the same name is a different drawing. Same 24px grid and
// the same stroke weight as the Lucide set, so the family still reads as one.
//
// Each entry notes what specifically differs, so none of this looks like
// gratuitous redrawing of a perfectly good library icon.
const ORIGINAL_OUTLINE = {
	// Lucide's calendar has an empty body; the design's carries 2x3 date dots.
	calendar: '<rect x="3" y="4.6" width="18" height="16.4" rx="2.8"/>'
		+ '<path d="M3 9.6h18M8.2 2.6v3.8M15.8 2.6v3.8"/>'
		+ '<g fill="currentColor" stroke-width="0">'
		+ '<rect x="6.9" y="12.2" width="2.2" height="2.2" rx=".5"/>'
		+ '<rect x="10.9" y="12.2" width="2.2" height="2.2" rx=".5"/>'
		+ '<rect x="14.9" y="12.2" width="2.2" height="2.2" rx=".5"/>'
		+ '<rect x="6.9" y="16.4" width="2.2" height="2.2" rx=".5"/>'
		+ '<rect x="10.9" y="16.4" width="2.2" height="2.2" rx=".5"/>'
		+ '<rect x="14.9" y="16.4" width="2.2" height="2.2" rx=".5"/>'
		+ '</g>',

	// Lucide's TrendingUp is the arrow alone; the design sets it over bars.
	'chart-up': '<g fill="currentColor" stroke-width="0">'
		+ '<rect x="3" y="16.8" width="3.2" height="4.4" rx=".6"/>'
		+ '<rect x="8" y="14.4" width="3.2" height="6.8" rx=".6"/>'
		+ '<rect x="13" y="12" width="3.2" height="9.2" rx=".6"/>'
		+ '</g>'
		+ '<path d="m2.8 11 4.8-4.4 3.8 2.4 6.2-5.8"/><path d="M13.8 2.8h4.2V7"/>',

	// Lucide's Headset has no boom; the design's support icon does.
	headset: '<path d="M3.8 12.9v-1.3a8.2 8.2 0 0 1 16.4 0v1.3"/>'
		+ '<g fill="currentColor" stroke-width="0">'
		+ '<rect x="1.2" y="12.2" width="4.8" height="6.6" rx="2.2"/>'
		+ '<rect x="18" y="12.2" width="4.8" height="6.6" rx="2.2"/>'
		+ '<circle cx="14" cy="21.9" r="1.5"/></g>'
		+ '<path d="M20.4 19v.7a2.4 2.4 0 0 1-2.4 2.2h-2.5"/>',

	// Lucide's Globe carries one latitude; the design's carries three.
	globe: '<circle cx="12" cy="12" r="9.5"/><ellipse cx="12" cy="12" rx="4.1" ry="9.5"/>'
		+ '<path d="M2.5 12h19M4.3 6.7h15.4M4.3 17.3h15.4"/>',

	// The design's certificate is a written document with an award medal;
	// Lucide's FileBadge2 has no text lines and a ribbon, not a medal.
	certificate: '<path d="M13.2 2.6H6.3A2.3 2.3 0 0 0 4 4.9v14.2a2.3 2.3 0 0 0 2.3 2.3h4.4"/>'
		+ '<path d="M13.2 2.6 19.6 9v2.4"/>'
		+ '<path d="M13.2 2.6v4.8a1.6 1.6 0 0 0 1.6 1.6h4.8"/>'
		+ '<path d="M7.3 10.6h6.2M7.3 13.4h5M7.3 16.2h3.4"/>'
		+ '<circle cx="17.8" cy="16.2" r="3.4"/>'
		+ '<path d="M17.8 14.5v3.2M16.4 16.4l1.4 1.3 1.4-1.3"/>'
		+ '<path d="m15.6 19-.5 2.9 2.7-1.3 2.7 1.3-.5-2.9"/>',

	// The design's megaphone has a squared driver, a grip below it and a
	// spark at the mouth; Lucide's is a plain cone.
	megaphone: '<rect x="2.2" y="9.2" width="4.8" height="5.6" rx="1.2"/>'
		+ '<path d="M7 9 17.6 4.2v15.6L7 15Z"/>'
		+ '<path d="M4.6 14.8v3.4a2.2 2.2 0 0 0 4.4 0v-2.2"/>'
		+ '<path d="M20 12h2M21 11v2"/>',

	// Lucide's Clock has no dial ticks; the design's does.
	clock: '<circle cx="12" cy="12" r="9.4"/><path d="M12 6.8V12l3.9 2.4"/>'
		+ '<path d="M12 2.8v1.7M12 19.5v1.7M2.8 12h1.7M19.5 12h1.7"/>',

	// Squarer pages and a flat base; Lucide's BookOpen is rounder and its
	// spine is a straight bar rather than a gap.
	'book-open': (() => {
		const b = bookOpen();
		return `<path d="${b.left}"/><path d="${b.right}"/>`
			+ '<path d="M12 8.2v10.2"/>';
	})(),

	// A mortarboard without its crown reads as a paper dart at 20px, which is
	// why the design draws the band and Lucide does not.
	'graduation-cap': '<path d="M12 3.4 22.4 7.2 12 11 1.6 7.2Z"/>'
		+ '<path d="M6.6 10.6v3.2q0 2 5.4 2t5.4-2v-3.2"/>'
		+ '<path d="M19.4 8.4v4.2"/>'
		+ '<circle cx="19.4" cy="13.9" r="1.2" fill="currentColor" stroke-width="0"/>',

	// The trust-bar "Safe & Secure" mark is a shield inside a shield with the
	// inner one halved -- NOT the shield-with-a-check that shares its slot in
	// most icon sets, which is why it needed its own symbol.
	'shield-halved': `<path d="${SHIELD}"/>`
		+ '<g transform="translate(4.2 4.9) scale(.65)">'
		+ `<path d="${SHIELD}"/>`
		+ `<path fill="currentColor" stroke-width="0" d="M12 1.5C9 3.6 5.9 4.5 3 4.9v6.2c0 5 3.7 9 9 11.4Z"/>`
		+ '</g>',

	// Testimonial tag "Monthly Tracking".
	'clipboard-check': '<rect x="3.8" y="4.4" width="16.4" height="17.2" rx="2.6"/>'
		+ '<path d="M8.4 2.6v3.6M15.6 2.6v3.6"/>'
		+ '<path d="m7.4 10 1.5 1.5 2.9-2.9M7.4 15.2l1.5 1.5 2.9-2.9"/>'
		+ '<path d="M14.2 10.2h2.6M14.2 15.4h2.6"/>',

	// Testimonial tag "Personalised Focus".
	// Testimonial tag "Personalised Focus". The reference has NO arrowhead at
	// the bullseye -- the filled centre dot is the impact point and the solid
	// shape is the flight at the far end of the shaft.
	'target-arrow': '<circle cx="10.6" cy="13.4" r="8.1"/><circle cx="10.6" cy="13.4" r="4.5"/>'
		+ '<circle cx="10.6" cy="13.4" r="1.7" fill="currentColor" stroke-width="0"/>'
		+ '<path d="m11.9 12.1 7.5-7.5"/>'
		+ '<path d="M21.9 2.1 18.4 3l2.7 2.7Z" fill="currentColor" stroke-width="0"/>',

	// Pricing "One-on-One Live Classes": a Quran open on a rehal, the folding
	// stand the design draws under it. No icon library has this.
	// Pricing "One-on-One Live Classes": a Quran open on a rehal. The stand's
	// legs cross beneath the book and finish outside its width; the previous
	// version had them meeting inside it, which read as wings.
	'rehal-quran': '<path d="M6.4 12.2 17.9 21.3M17.6 12.2 6.1 21.3"/>'
		+ '<path d="M11.2 6.4 3.5 4.6v8.1l7.7 2.2Z"/>'
		+ '<path d="M12.8 6.4l7.7-1.8v8.1l-7.7 2.2Z"/>',

	// Testimonials eyebrow: the quote marks sit inside a speech bubble.
	'quote-bubble': '<path d="M12 3.1a8.9 8.9 0 1 1-6.6 14.9l-2.3 3.4.7-4.2A8.9 8.9 0 0 1 12 3.1Z"/>'
		+ quotePair(0.42, 7, 6.4).replace('<path', '<path fill="currentColor" stroke-width="0"'),

	// Testimonial tag "Expert Tutors": a cap over a figure, not a bare cap.
	// Testimonial tag "Expert Tutors". The reference draws a mortarboard with
	// the tassel on its LEFT corner, and beneath it a hood that meets the
	// board's underside -- not a cap floating over a separate circular head.
	graduate: '<path d="M12 2.6 20.6 5.6 12 8.6 3.4 5.6Z"/>'
		+ '<path d="M5 6.6v2.9"/>'
		+ '<circle cx="5" cy="10.6" r="1.05" fill="currentColor" stroke-width="0"/>'
		+ '<path d="M8.9 8.4v2.3a3.1 3.1 0 0 0 6.2 0V8.4"/>'
		+ '<path d="M5.8 21.4v-2.5a6.2 4.3 0 0 1 12.4 0v2.5"/>',

	// About stat "Students Taught": a learner in front of a teaching board.
	presenter: '<rect x="10.6" y="2.8" width="11.4" height="10" rx="1.7"/>'
		+ '<rect x="12.6" y="9" width="2.8" height="3.8" rx=".5" fill="currentColor" stroke-width="0"/>'
		+ '<g fill="currentColor" stroke-width="0">'
		+ bust(5.2, 12.2, 2.8, 4.4, 15.8, 21.2)
		+ '</g>',
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
	// The reference lays the tassel cord ACROSS the board as a light line --
	// a knockout, not a cord hanging off the edge with a bead on it.
	'graduation-cap-filled':
		'<path fill-rule="evenodd" d="M12 3.4 22.4 7.2 12 11 1.6 7.2Z'
		+ 'M11.7 6.2 17.3 8.1l-.1.8-5.6-1.9Z"/>'
		+ '<path d="M6.4 10.5h11.2v3.3q0 2.1-5.6 2.1t-5.6-2.1Z"/>',
	// The design's award medal knocks a five-point STAR out of the badge; the
	// hollow ring this carried before is the one thing that separated it from
	// the teacher-card "years experience" mark it is meant to be.
	'certificate-filled': `<path d="m7.6 12.8-2.4 9.5 3.6-1.5 2.4 1.7.8-8.6.8 8.6 2.4-1.7 3.6 1.5-2.4-9.5Z"/><path fill-rule="evenodd" d="${spikedRosettePath(12, 8.2, 6.7, 5.6, 12)} ${star5(12, 8.4, 3.7, 0.46)}"/>`,
	'quote-filled': '<path d="M10.3 3.3C4.7 4.8 2 8.7 2 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Zm11 0C15.7 4.8 13 8.7 13 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Z"/>',
	'gift-filled': '<path fill-rule="evenodd" d="M12 7.3C10.4 3.3 8.2 1.2 5.9 2 2.9 3 3.6 7.9 7.3 7.9h9.4c3.7 0 4.4-4.9 1.4-5.9-2.3-.8-4.5 1.3-6.1 5.3ZM9.5 6.2H7.3c-1.5 0-1.8-2.1-.7-2.4 1-.3 2.1.8 2.9 2.4Zm5 0h2.2c1.5 0 1.8-2.1.7-2.4-1-.3-2.1.8-2.9 2.4Z"/><path d="M2 9h9v3H2Zm11 0h9v3h-9ZM4 13.5h7V22H5a1 1 0 0 1-1-1Zm9 0h7V21a1 1 0 0 1-1 1h-6Z"/>',
	'map-pin-filled': '<path fill-rule="evenodd" d="M12 1.5A8 8 0 0 0 4 9.5c0 5 8 13 8 13s8-8 8-13a8 8 0 0 0-8-8Zm3 8a3 3 0 1 0-6 0 3 3 0 1 0 6 0Z"/>',

	// The hero chip and the "trusted by families" pill draw a group of THREE:
	// one figure in front, two set back behind it. `users-filled` above keeps
	// the two-figure pair the teachers eyebrow uses -- both exist in the
	// design and they are not the same mark. Figures are spaced so the
	// silhouettes never merge, which is how the reference keeps them legible.
	users: bust(12, 7.4, 3.7, 5.6, 12.8, 20.4)
		+ bustBehind(3.4, 9.8, 2.4, 2.4, 15.2, 20.4, 'left')
		+ bustBehind(20.6, 9.8, 2.4, 2.4, 15.2, 20.4, 'right'),

	// Pricing "Qualified Male & Female Tutors": two figures of EQUAL size,
	// each with a head covering, drawn as a slot knocked out of the head.
	// Pricing "Qualified Male & Female Tutors". Each figure is three stacked
	// shapes with hairline gaps -- headwear, face, body -- which is how the
	// reference distinguishes the cap and the scarf. Knocking a slot out of a
	// single head circle instead read as a blindfold.
	// Pricing "Qualified Male & Female Tutors". Each figure is stacked shapes
	// with hairline gaps -- headwear, face, body -- which is how the reference
	// distinguishes the cap from the scarf. Knocking a slot out of one head
	// circle instead read as a blindfold.
	// Pricing "Qualified Male & Female Tutors". Both figures are the same
	// three stacked shapes with hairline gaps -- headwear, face, body -- and
	// differ only in how much of the head the covering takes: a shallow
	// taqiyah on the left, a fuller scarf on the right. That is exactly how
	// the reference separates them, and it keeps the pair the same weight.
	'people-pair':
		'<path d="M3.5 6a3.1 3.1 0 0 1 6.2 0Z"/>'
		+ '<circle cx="6.6" cy="9.4" r="2.75"/>'
		+ '<path d="M1.7 21.3v-3.5a4.9 4.9 0 0 1 9.8 0v3.5Z"/>'
		+ '<path d="M13.2 6.6a4.2 4.2 0 0 1 8.4 0Z"/>'
		+ '<circle cx="17.4" cy="9.6" r="2.65"/>'
		+ '<path d="M12.5 21.3v-3.5a4.9 4.9 0 0 1 9.8 0v3.5Z"/>',

	// Pricing "Safe & Supportive": a star inside the shield, drawn as a ring
	// (outer star knocked out, inner star filled back in) exactly as the
	// reference draws it. Shares SHIELD with shield-filled and shield-halved.
	'shield-star': `<path fill-rule="evenodd" d="${SHIELD} ${star5(12, 11.2, 5.6, 0.46)} ${star5(12, 11.2, 3.6, 0.46)}"/>`,

	// Courses eyebrow and the teacher medallion draw the book solid.
	'book-open-filled': (() => {
		const b = bookOpen();
		return `<path d="${b.left}"/><path d="${b.right}"/>`;
	})(),

	'arrow-right-filled': '<path d="M3 10.5h10.6L9.5 6.4l1.7-1.8 7.4 7.4-7.4 7.4-1.7-1.8 4.1-4.1H3Z"/>',

	// Footer and "Call Any Time" draw a solid classic receiver with signal
	// arcs; the outline `phone` above stays for anywhere drawn as a line.
	phone: '<path d="M6.6 2.9a1.9 1.9 0 0 0-2.5.4L2.6 5.1c-.8.9-1 2.2-.5 3.3a24 24 0 0 0 13.5 13.5c1.1.5 2.4.3 3.3-.5l1.8-1.5a1.9 1.9 0 0 0 .4-2.5l-2.4-3.8a1.9 1.9 0 0 0-2.4-.7l-2 1a13 13 0 0 1-5.3-5.3l1-2a1.9 1.9 0 0 0-.7-2.4Z"/>'
		+ '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">'
		+ '<path d="M14.6 7.2A3.6 3.6 0 0 1 16.9 10"/>'
		+ '<path d="M15.2 5.5A5.4 5.4 0 0 1 18.7 9.7"/>'
		+ '<path d="M15.9 3.8A7.2 7.2 0 0 1 20.5 9.4"/></g>',

	mail: '<path d="M2.4 6.8a2.6 2.6 0 0 1 2.6-2.6h14a2.6 2.6 0 0 1 2.6 2.6v.4L12 13.6 2.4 7.2Z"/>'
		+ '<path d="M2.4 9.7v7.5a2.6 2.6 0 0 0 2.6 2.6h14a2.6 2.6 0 0 0 2.6-2.6V9.7l-8.8 5.9a1.5 1.5 0 0 1-1.6 0Z"/>',

	// Featured pricing card: a big four-point sparkle with a small companion.
	'sparkle-filled': `<path d="${sparkle4(12, 11.4, 9.4, 9.4)}"/><path d="${sparkle4(4.8, 19.4, 2.7, 2.7)}"/>`,

	// The RECOMMENDED badge: an eight-lobed rosette with a star knocked out.
	'rosette-star': `<path fill-rule="evenodd" d="${spikedRosettePath(12, 12, 11.2, 8.3, 8)} ${star5(12, 12.1, 5, 0.46)}"/>`,

	// The footer draws Facebook's bare "f", not the f-in-a-circle that
	// Simple Icons ships as the brand mark -- the circle in the design is the
	// social ring around it, drawn in CSS.
	'facebook-f': '<path d="M13.9 23v-9.8h3.3l.5-3.8h-3.8V7c0-1.1.3-1.8 1.9-1.8h2V1.8c-.4-.1-1.5-.2-2.9-.2-2.9 0-4.8 1.7-4.8 4.9v2.9H6.8v3.8h3.3V23Z"/>',
};

function extractInner(svgString) {
	const match = svgString.match(/<svg[^>]*>([\s\S]*)<\/svg>/);
	if (!match) throw new Error('could not parse svg: ' + svgString.slice(0, 80));
	return match[1].trim();
}

const OUTLINE_ATTRS = 'fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"';

function optimizeFragment(inner, viewBox, floatPrecision) {
	// Wrap, run svgo (cleans precision/whitespace), unwrap.
	const wrapped = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">${inner}</svg>`;
	const { data } = optimize(wrapped, {
		multipass: true,
		floatPrecision,
		plugins: [{
			name: 'preset-default',
			params: { overrides: { removeUselessStrokeAndFill: false } },
		}],
	});
	return data.replace(/^<svg[^>]*>/, '').replace(/<\/svg>$/, '');
}

// Every symbol, collected as { id, attrs, inner }, so the identical geometry
// can be written out twice -- into the runtime sprite AND as a standalone
// .svg per icon -- without a second source of truth to drift from.
const ICONS = [];
const add = (id, attrs, inner, viewBox = '0 0 24 24') =>
	ICONS.push({ id, attrs, inner, viewBox });

for (const [id, lucideName] of Object.entries(LUCIDE_MAP)) {
	const src = lucide[lucideName];
	if (!src) throw new Error(`Lucide icon not found: ${lucideName} (for eqc-icon-${id})`);
	const inner = extractInner(src);
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	// Preserve the established outline weight; filled reference roles use
	// explicit FILLED_UI variants instead of thickening these outlines.
	add(id, OUTLINE_ATTRS, cleaned);
}

for (const [id, inner] of Object.entries(ORIGINAL_OUTLINE)) {
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	add(id, OUTLINE_ATTRS, cleaned);
}

for (const [id, inner] of Object.entries(FILLED_UI)) {
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	add(id, 'fill="currentColor" stroke-width="0"', cleaned);
}

for (const [id, siName] of Object.entries(BRAND_MAP)) {
	const icon = siName ? simpleIcons[siName] : { svg: readFileSync(new URL('./twitter-reference.svg', import.meta.url), 'utf8') };
	if (!icon) throw new Error(`Simple Icons icon not found: ${siName} (for eqc-icon-${id})`);
	const inner = extractInner(icon.svg);
	const cleaned = optimizeFragment(inner, '0 0 24 24');
	// Filled, currentColor — brand marks are a deliberate exception to the
	// outline family (see file header comment).
	add(id, 'fill="currentColor"', cleaned);
}

// star-8 reuses the same exact geometry as the ornament system's
// star-8-filled.svg (tools/graphics/gen-ornaments.mjs), so the pricing
// list bullet and any decorative star elsewhere are visually identical —
// computed inline here rather than fetched from the ornaments folder so
// the icon sprite has no build-order dependency on gen-ornaments.mjs.
const star8d = starPolygonPath(12, 12, 10, 8, 3);
add('star-8', 'fill="currentColor"', `<path d="${star8d}"/>`);

// star-filled: the solid five-point rating star the hero trust row and the
// testimonial cards need. Lucide's `star` is an OUTLINE glyph carrying
// fill="none" on its own path — and because icons render through <use>, that
// presentation attribute cannot be overridden by CSS from the page (the
// referenced content is in a shadow tree, and an attribute beats an inherited
// fill anyway). So the filled star is a symbol in its own right, built from
// the same starPolygonPath construction as star-8 above rather than a second
// traced asset. See QA/design-review/home.md finding 9.
const star5d = star5(12, 12.2, 10.6, 0.48);
add('star-filled', 'fill="currentColor"', `<path d="${star5d}"/>`);

// ---------------------------------------------------------------------------
// Hand-supplied artwork (tools/graphics/source-icons/), normalised to
// currentColor with a tight square viewBox. These six replace icons I had
// drawn from primitives; the supplied drawings are simply better, and the
// reference crops confirm they are the shapes the design actually uses.
//
// The art is traced, so it carries far more precision than a 24px icon can
// show -- svgo is run at 2 decimal places here rather than the default, which
// is the difference between a 62KB path and a usable one.
// ---------------------------------------------------------------------------
const SUPPLIED = {
	'graduation-cap-filled': 'graduation-cap-filled.svg',
	// The design's "Expert Tutors" tag is a graduate figure, not a bare cap.
	graduate: 'graduation-cap-outline.svg',
	'people-pair': 'people-pair.svg',
	'rehal-quran': 'rehal-quran.svg',
	presenter: 'stat-students.svg',
	'target-arrow': 'target-arrow.svg',
};

for (const [id, file] of Object.entries(SUPPLIED)) {
	const raw = readFileSync(new URL(`./source-icons/${file}`, import.meta.url), 'utf8');
	const vb = raw.match(/viewBox="([^"]+)"/);
	if (!vb) throw new Error(`source icon has no viewBox: ${file}`);
	const inner = extractInner(raw);
	const cleaned = optimizeFragment(inner, vb[1], 2);
	// Drop any symbol these replace, so the supplied art wins rather than
	// silently ending up as a duplicate id in the sprite.
	const dup = ICONS.findIndex((i) => i.id === id);
	if (dup !== -1) ICONS.splice(dup, 1);
	add(id, 'fill="currentColor" stroke-width="0"', cleaned, vb[1]);
}

ICONS.sort((a, b) => a.id.localeCompare(b.id));

const symbols = ICONS
	.map((i) => `		<symbol id="eqc-icon-${i.id}" viewBox="${i.viewBox}" ${i.attrs}>${i.inner}</symbol>

`)
	.join('');

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

// ---------------------------------------------------------------------------
// Standalone SVG per icon.
//
// The sprite is what the site renders, but a sprite is not something anyone
// can open, hand to a designer, drop into Figma or diff meaningfully. These
// files are written from the SAME `ICONS` array as the sprite above, so they
// cannot drift from it -- regenerating is the only way either changes.
//
// `currentColor` is kept so a file dropped into a page still inherits colour;
// each also carries an explicit `color` so it renders in the brand green when
// opened on its own, where there is nothing to inherit from.
// ---------------------------------------------------------------------------
const iconsDir = new URL('../../wp-content/themes/easy-quran-classes-child/assets/svg/icons/', import.meta.url);
mkdirSync(iconsDir, { recursive: true });

// Remove files for icons that no longer exist, so a renamed icon does not
// leave its old file behind looking current.
const wanted = new Set(ICONS.map((i) => `${i.id}.svg`));
for (const f of readdirSync(iconsDir)) {
	if (f.endsWith('.svg') && !wanted.has(f)) {
		rmSync(new URL(f, iconsDir));
		console.log(`  removed stale ${f}`);
	}
}

for (const { id, attrs, inner, viewBox } of ICONS) {
	const file = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}" width="24" height="24"`
		+ ` color="#14231B" ${attrs} role="img" aria-label="${id}">${inner}</svg>
`;
	writeFileSync(new URL(`${id}.svg`, iconsDir), file);
}
console.log(`Wrote ${ICONS.length} standalone SVGs to assets/svg/icons/`);
console.log(`Wrote ${Object.keys(LUCIDE_MAP).length + Object.keys(ORIGINAL_OUTLINE).length + Object.keys(BRAND_MAP).length + Object.keys(FILLED_UI).length + 2} symbols to inc/icon-sprite.php`);
