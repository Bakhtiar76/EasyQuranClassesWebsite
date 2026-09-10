// Compose the final logo SVG variants from the traced mark path.
//
// The mark (headset + open book + mihrab arch + thuluth calligraphy
// medallion) is potrace's exact vectorisation of the client-approved
// Assests/Logo/Logo2.jpeg (see trace-logo.mjs + DESIGN.md §5) — never
// redrawn. The wordmark/tagline are real <text> using the site's own
// loaded webfonts (DM Serif Display / Manrope), not traced bitmap text —
// crisper, translatable, and consistent with every other heading on the
// site, at the cost of needing those fonts loaded wherever the SVG is
// rendered (true on every page of this site; noted in README.md for any
// other use).
//
// Usage: node build-logo.mjs
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { optimize } from 'svgo';

// scratch/ is gitignored, so this input does not exist on a fresh clone until
// the trace step has run. Fail with the command to run, not a bare ENOENT.
let MARK_D;
try {
	// Resolved from this file, not the CWD: the previous repo-root-relative
	// literal meant the script only worked when run from the repo root and
	// failed confusingly from tools/graphics, where its sibling scripts run.
	MARK_D = readFileSync(new URL('./scratch/mark-path.txt', import.meta.url), 'utf8').trim();
} catch (err) {
	if (err.code !== 'ENOENT') throw err;
	throw new Error(`Missing build input tools/graphics/scratch/mark-path.txt.
It is generated, not committed. Run this first, from tools/graphics:

    node trace-logo.mjs

See tools/graphics/README.md for the full regeneration sequence.`);
}

const MARK_W = 770;
const MARK_H = 692;
// Resolved from this file, not the CWD — matching gen-ornaments.mjs. As a
// repo-root-relative literal it silently created a whole stray
// tools/graphics/wp-content/... tree when run from tools/graphics, where the
// sibling generators are run from, and the real theme assets never changed.
const OUT_DIR = new URL('../../wp-content/themes/easy-quran-classes-child/assets/svg/logo/', import.meta.url);
mkdirSync(OUT_DIR, { recursive: true });

// eqc-logo-horizontal.svg and eqc-logo.svg have no current runtime
// reference — every page composes the theme's own mark + live text
// instead — but their own future uses (social avatar, print, schema.org
// Organization logo; email signatures, social previews) are real, so they
// stay generated rather than deleted. Kept out of the production theme
// (CLAUDE.md: production ships only what's actually used) alongside this
// project's other dev-only generator output (reference/, scratch/,
// source-icons/).
const OUT_DIR_UNUSED = new URL('./output/logo/', import.meta.url);
mkdirSync(OUT_DIR_UNUSED, { recursive: true });

const FONT_DISPLAY = "'DM Serif Display', Georgia, 'Times New Roman', serif";
const FONT_BODY = "'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";

function write(name, svg, dir = OUT_DIR) {
	const { data } = optimize(svg, { multipass: true, plugins: ['preset-default'] });
	writeFileSync(new URL(name, dir), data);
	console.log(`${name.padEnd(28)} ${data.length} bytes`);
}

const markPath = (x, y, scale) =>
	`<g transform="translate(${x} ${y}) scale(${scale})"><path fill-rule="evenodd" d="${MARK_D}"/></g>`;

// ---------------------------------------------------------------------
// 1. eqc-logo-mark.svg — the mark alone, currentColor. Primary building
//    block: used in the header/footer next to a live text wordmark, and
//    as the base for the favicon.
// ---------------------------------------------------------------------
write(
	'eqc-logo-mark.svg',
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${MARK_W} ${MARK_H}" fill="currentColor" role="img" aria-label="Easy Quran Classes">${markPath(0, 0, 1)}</svg>`
);

// ---------------------------------------------------------------------
// 2. eqc-logo-horizontal.svg — mark + wordmark side by side. A
//    self-contained lockup asset for contexts that can't compose the
//    theme's own mark + live-text (email signatures, social previews,
//    print). currentColor throughout, so one file serves both light and
//    dark backgrounds via the consumer's CSS `color`. No current runtime
//    reference, so it is written to output/logo/ (dev-only) rather than
//    the production theme — see OUT_DIR_UNUSED above.
// ---------------------------------------------------------------------
{
	const markH = 120;
	const markScale = markH / MARK_H;
	const markW = MARK_W * markScale;
	const textX = markW + 32;
	const viewW = textX + 560;
	const viewH = markH;
	write(
		'eqc-logo-horizontal.svg',
		`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${viewW} ${viewH}" fill="currentColor" role="img" aria-label="Easy Quran Classes — Learning the Quran step by step">` +
			markPath(0, (viewH - MARK_H * markScale) / 2, markScale) +
			`<text x="${textX}" y="${viewH * 0.46}" font-family="${FONT_DISPLAY}" font-size="42" letter-spacing="1.5">EASY QURAN CLASSES</text>` +
			`<text x="${textX}" y="${viewH * 0.46 + 34}" font-family="${FONT_BODY}" font-size="17" font-weight="500" letter-spacing="0.5" fill-opacity="0.82">Learning the Quran step by step</text>` +
			`</svg>`,
		OUT_DIR_UNUSED
	);
}

// ---------------------------------------------------------------------
// 3. eqc-logo.svg — full stacked lockup (mark above, wordmark + tagline
//    below), matching Logo2.jpeg's own layout. For square contexts:
//    social avatar, print, schema.org Organization logo. No current
//    runtime reference — written to output/logo/, same as #2 above.
// ---------------------------------------------------------------------
{
	const markScale = 1;
	const gap1 = 40;
	const titleH = 60;
	const gap2 = 30;
	const tagH = 30;
	const viewW = MARK_W;
	const viewH = MARK_H + gap1 + titleH + gap2 + tagH;
	const cx = viewW / 2;
	write(
		'eqc-logo.svg',
		`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${viewW} ${viewH}" fill="currentColor" role="img" aria-label="Easy Quran Classes — Learning the Quran step by step">` +
			markPath(0, 0, markScale) +
			`<text x="${cx}" y="${MARK_H + gap1 + titleH * 0.7}" text-anchor="middle" font-family="${FONT_DISPLAY}" font-size="52" letter-spacing="2">EASY QURAN CLASSES</text>` +
			`<text x="${cx}" y="${MARK_H + gap1 + titleH + gap2 + tagH * 0.6}" text-anchor="middle" font-family="${FONT_BODY}" font-size="22" font-weight="500" letter-spacing="0.5" fill-opacity="0.82">Learning the Quran step by step</text>` +
			`</svg>`,
		OUT_DIR_UNUSED
	);
}

// ---------------------------------------------------------------------
// 4. favicon.svg — the mark alone with an explicit fill (favicons render
//    outside any page CSS context, so currentColor has nothing to
//    inherit). Same geometry as eqc-logo-mark.svg — at 16-32px the fine
//    calligraphy/book-line detail recedes but the arch+headset silhouette
//    (the part that actually reads at that size) stays intact, same as
//    how the mark already reads in the browser tests above.
// ---------------------------------------------------------------------
// A favicon canvas must be SQUARE: browsers scale it into a square slot, so a
// 770x692 viewBox renders the mark stretched ~11% vertically in the tab. The
// mark is centred in a 770x770 box instead, with the cream ground filling it.
const FAV = Math.max(MARK_W, MARK_H);
write(
	'favicon.svg',
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${FAV} ${FAV}">` +
	`<rect width="${FAV}" height="${FAV}" fill="#F7F3EC"/>` +
	`<g fill="#1B3A2D" transform="translate(${(FAV - MARK_W) / 2} ${(FAV - MARK_H) / 2})">${markPath(0, 0, 1)}</g>` +
	`</svg>`
);

console.log('\nDone. Mono dark-background variant is NOT a separate file:');
console.log('every asset above uses currentColor, so header.php sets no');
console.log('color and footer.php sets `color: var(--eqc-gold-300)`.');
