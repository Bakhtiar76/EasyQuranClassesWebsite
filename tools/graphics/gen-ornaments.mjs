// Generate the Islamic ornament/arch/divider asset system from exact
// parametric geometry (regular-polygon and arc trigonometry — see
// lib/geometry.mjs), replacing the previous hand-eyeballed 271-1444 byte
// placeholder SVGs. See tools/graphics/README.md for the construction
// notes behind each shape and DESIGN.md's asset-library section for how
// each is consumed (mask-image vs inline currentColor).
//
// The 8-fold rosette is the one exception to "parametric geometry": it's
// traced from a client-supplied reference bitmap (reference/rosette.png)
// by trace-rosette.mjs, which must be re-run first if that reference or
// its trace parameters change — see that script and README.md. The arch
// (ogeeArchPanel, lib/arches.mjs) IS parametric, but its default control
// points are measured off the client's own approved page mockup rather
// than guessed — see the function's own doc comment and README.md's
// "hero/photo arch" section for the measurement + bezier-fit derivation.
//
// Usage: node trace-rosette.mjs && node gen-ornaments.mjs
import { writeFileSync, mkdirSync, readFileSync } from 'node:fs';
import { optimize } from 'svgo';
import {
	fmt,
	starPolygonPath,
	spikedRosettePath,
	regularPolygonPath,
	girihRosettePath,
	polar,
} from './lib/geometry.mjs';
import {
	arcCmd,
	wrapArch,
	svgFromBbox,
	ogeeArchPanel,
	fourCentredArchPanel,
	horseshoeArchPanel,
	mandorlaPanel,
	multifoilArchPanel,
	cuspedArchPanel,
} from './lib/arches.mjs';

const rosetteTrace = JSON.parse(readFileSync(new URL('./scratch/rosette-traced.json', import.meta.url), 'utf8'));

/** Place the traced 8-fold rosette (centered at its own origin, see
 * trace-rosette.mjs) at (cx, cy) scaled so its outer radius becomes r. */
function tracedRosette(cx, cy, r) {
	const s = r / rosetteTrace.radius;
	return `<g transform="translate(${fmt(cx)} ${fmt(cy)}) scale(${fmt(s)})">` +
		`<path fill="currentColor" fill-rule="evenodd" d="${rosetteTrace.d}"/></g>`;
}

// Flat into the theme's existing assets/svg/ — no new subfolder (CLAUDE.md:
// don't create parallel structures). Where a direct predecessor exists
// (rosette.svg, lattice-corner.svg, arch-mask.svg, arch-outline.svg) this
// overwrites it in place, so every existing CSS `url('../svg/NAME.svg')`
// mask reference keeps working unchanged — only genuinely new shapes get
// new filenames.
const OUT = new URL('../../wp-content/themes/easy-quran-classes-child/assets/svg/', import.meta.url);
mkdirSync(OUT, { recursive: true });

function save(name, svg) {
	const { data } = optimize(svg, { multipass: true, plugins: ['preset-default'] });
	writeFileSync(new URL(name, OUT), data);
	console.log(name.padEnd(30), data.length, 'bytes');
}

const svgWrap = (w, h, body, attrs = '') =>
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" ${attrs}>${body}</svg>`;

// =====================================================================
// 1. Rosettes — badge medallions and divider centerpieces.
//    rosette.svg and the 3 medallions below are the client's 8-fold girih
//    star (tools/graphics/reference/rosette.png), traced exactly rather
//    than the previous simple {n/step} star — see trace-rosette.mjs and
//    README.md. rosette-12.svg and star-8-filled.svg have no reference to
//    trace, so they use girihRosettePath (lib/geometry.mjs), the same
//    tip/shoulder/valley construction measured off that same reference,
//    honestly generalized rather than guessed.
// =====================================================================

// rosette.svg: the exact traced 8-fold shape (fill, using its own
// evenodd interior holes — the traced ink shape already reproduces the
// woven look, no separate stroke needed). Overwrites the previous
// circle+star+circle placeholder in place (same CSS mask references keep
// working).
save('rosette.svg', svgWrap(64, 64, tracedRosette(32, 32, 28), 'aria-hidden="true" focusable="false"'));

// Quiet scalloped seal for course indices and pricing badges/bullets. Each
// lobe is a quadratic curve between equal-radius valleys; there are no
// internal spokes behind a number or icon. Both renders share one path.
function scallopedSealPath(cx, cy, r, folds = 12, depth = 3.2) {
	const step = Math.PI * 2 / folds;
	const start = polar(cx, cy, r - depth, -Math.PI / 2 - step / 2);
	let d = `M${fmt(start[0])} ${fmt(start[1])}`;
	for (let i = 0; i < folds; i++) {
		const angle = -Math.PI / 2 + i * step;
		const crest = polar(cx, cy, r + depth, angle);
		const end = polar(cx, cy, r - depth, angle + step / 2);
		d += `Q${fmt(crest[0])} ${fmt(crest[1])} ${fmt(end[0])} ${fmt(end[1])}`;
	}
	return d + 'Z';
}
{
	const d = scallopedSealPath(32, 32, 28);
	save('seal-filled.svg', svgWrap(64, 64, `<path fill="currentColor" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
	save('seal-outline.svg', svgWrap(64, 64, `<path fill="none" stroke="currentColor" stroke-width="1.4" vector-effect="non-scaling-stroke" stroke-linejoin="round" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
}

// rosette-12.svg: no 12-fold reference exists, so this is the measured
// tip/shoulder/valley construction generalized to 12 points (stroke
// outline, matching this asset's decorative-ring role at
// .eqc-pricing-icon-ring's ~60px render size).
{
	const d = girihRosettePath(32, 32, 28, 12);
	save('rosette-12.svg', svgWrap(64, 64, `<path fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
}

// Filled 8-point star bullet — pricing feature lists. Renders around
// 12px, far too small for the rosette's interior weave to read, so this
// uses girihRosettePath's OUTER silhouette only (filled solid) — the
// same measured tip/shoulder/valley proportions as rosette-12.svg, just
// at folds=8 to match this bullet's established 8-point look.
{
	const d = girihRosettePath(12, 12, 11, 8);
	save('star-8-filled.svg', svgWrap(24, 24, `<path fill="currentColor" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
}

// Divider medallion (small, for the section-heading rule) — the exact
// traced rosette, scaled down.
{
	save('divider-medallion.svg', svgWrap(32, 32, tracedRosette(16, 16, 14), 'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 2. Girih lattice — true interlocking star-and-cross tessellation.
//
//    Construction: the classic Archimedean 4.8.8 "octagon + square"
//    tiling (regular octagons on a square grid, sharing edges with 4
//    orthogonal neighbours; a 45°-rotated square fills each 4-way
//    diagonal gap — vertex angle sum 135+135+90=360, so it tiles the
//    plane exactly with no gaps or overlaps by construction). An {8/3}
//    star polygon is inscribed in each octagon and a small diamond in
//    each square, which is precisely how girih strapwork is traditionally
//    laid over this base grid. Every coordinate below follows from the
//    octagon's own edge length `a` — nothing is eyeballed, so adjacent
//    tiles butt together exactly (verified by rendering a 3x3 repeat,
//    see tools/graphics/README.md).
// =====================================================================
function girihTile(a, { starInset = 0.86 } = {}) {
	// Regular octagon: circumradius R, apothem Ap, for side length a.
	const R = a / (2 * Math.sin(Math.PI / 8));
	const Ap = a / (2 * Math.tan(Math.PI / 8));
	const D = 2 * Ap; // grid spacing between octagon centers (== a * (1 + sqrt(2)))

	const octagons = [
		[0, 0], [D, 0], [0, D], [D, D], // 4 corner octagons of the repeat unit
	];
	const squareCenters = [[D / 2, D / 2]]; // one diagonal-gap square per repeat unit

	// Only the star (not the octagon's own outline) and only the gap
	// square's outline (not a redundant inset copy) are drawn — the
	// octagon boundary was visual clutter (a second, larger 8-gon
	// competing with the star for attention) with no structural purpose
	// once the star is in place.
	let body = '';
	for (const [cx, cy] of octagons) {
		body += `<path d="${starPolygonPath(cx, cy, R * starInset, 8, 3, Math.PI / 8)}"/>`;
	}
	for (const [cx, cy] of squareCenters) {
		// The gap square is itself rotated 45° relative to the grid — its
		// own vertices already sit at the grid axes, so angleOffset=0 here
		// (vs. Math.PI/8 for the octagons above) lines its flat sides up
		// against the octagons' flat edges.
		body += `<path d="${regularPolygonPath(cx, cy, (a * Math.SQRT2) / 2, 4, 0)}"/>`;
	}
	return { body, D };
}

for (const [name, a] of [['fine', 34], ['dense', 52]]) {
	const { body, D } = girihTile(a);
	const svg = svgWrap(
		D, D,
		`<g fill="none" stroke="currentColor" stroke-width="1" stroke-linejoin="round">${body}</g>`,
		'aria-hidden="true" focusable="false"'
	);
	save(`girih-lattice-${name}.svg`, svg);
}

// Shared angular field. A real SVG pattern clips each repeat unit before
// repetition, preventing coincident duplicate strokes between neighbors.
function girihPattern(id, side, strokeWidth = 1.2) {
	const { D } = girihTile(side);
	const r = side / (2 * Math.sin(Math.PI / 8));
	let body = '';
	// Outline the star's perimeter instead of drawing its crossing chords:
	// the latter line up across repeats and reduce to a diagonal mesh. Two
	// perimeters make a narrow angular ribbon, with a clear star-shaped void.
	for (const [cx, cy] of [[0, 0], [D, 0], [0, D], [D, D]]) {
		for (const inset of [1, .79]) {
			body += `<path d="${spikedRosettePath(cx, cy, r * inset, r * .62 * inset, 8, Math.PI / 8)}"/>`;
		}
	}
	body += `<path d="${regularPolygonPath(D / 2, D / 2, side * Math.SQRT1_2, 4, 0)}"/>`;
	return `<pattern id="${id}" width="${fmt(D)}" height="${fmt(D)}" patternUnits="userSpaceOnUse"><g fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linejoin="round">${body}</g></pattern>`;
}

// courses.jpeg / Reviews.jpeg show angular lace, not circular brackets or
// floating stars. Both assets are dense at TOP RIGHT, fading completely
// before their left/bottom edges. Mirror horizontally for a top-left card.
// The separate canvas sizes preserve the measured card/section cell scale.
for (const [name, size, side] of [['corner-motif.svg', 120, 14], ['lattice-corner.svg', 300, 30]]) {
	save(name, svgWrap(size, size,
		`<defs>${girihPattern('lace', side)}` +
		`<radialGradient id="fade" cx="${size}" cy="0" r="${size * 0.98}" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff"/><stop offset=".45" stop-color="#fff" stop-opacity=".8"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>` +
		`<mask id="soft"><rect width="${size}" height="${size}" fill="url(#fade)"/></mask></defs>` +
		`<rect width="${size}" height="${size}" fill="url(#lace)" mask="url(#soft)"/>`,
		'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 3. Hexagon tessellation — subtler texture (Riwaq-reference hero
//    background), for sections where the girih lattice would be too busy.
// =====================================================================
{
	// Plain pointy-top honeycomb grid. Standard hex-grid spacing for
	// circumradius s: same-row centers sqrt(3)*s apart; rows 1.5*s apart,
	// alternating rows offset by half that width — so the vertical repeat
	// period is 2 rows (3*s), not 1. Centers are drawn across a 3x3
	// neighbourhood and clipped to the tile's viewBox, which guarantees
	// every hexagon fragment crossing the tile edge is present (safer
	// than hand-counting the minimal set) — verified seamless by
	// rendering a repeated background (tools/graphics/README.md).
	const s = 30;
	const tileW = Math.sqrt(3) * s;
	const tileH = 3 * s;
	let body = '';
	for (let row = -1; row <= 3; row++) {
		const y = row * 1.5 * s;
		const xOffset = row % 2 !== 0 ? tileW / 2 : 0;
		for (let col = -1; col <= 1; col++) {
			const x = col * tileW + xOffset;
			body += `<path d="${regularPolygonPath(x, y, s, 6, -Math.PI / 2)}"/>`;
		}
	}
	save('hex-tessellation.svg', svgWrap(tileW, tileH, `<g fill="none" stroke="currentColor" stroke-width="1">${body}</g>`, 'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 4. Sparkle dust — scattered 4-point sparkle confetti (Riwaq-reference
//    hero texture). Deterministic seeded placement, not a tiling pattern.
// =====================================================================
function mulberry32(seed) {
	let a = seed;
	return function () {
		a |= 0; a = (a + 0x6d2b79f5) | 0;
		let t = Math.imul(a ^ (a >>> 15), 1 | a);
		t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
		return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
	};
}
function sparklePath(cx, cy, r) {
	// 4-point sparkle: a spiked-rosette special case (4 long points, no
	// inner ring radius — the valleys pull almost to center for a slender
	// "twinkle" silhouette rather than a rounded star).
	return spikedRosettePath(cx, cy, r, r * 0.22, 4);
}
{
	const w = 900, h = 620;
	const rand = mulberry32(20260907);
	let body = '';
	const count = 26;
	for (let i = 0; i < count; i++) {
		const x = rand() * w;
		const y = rand() * h;
		const r = 4 + rand() * 10;
		const opacity = (0.25 + rand() * 0.55).toFixed(2);
		const rot = (rand() * 45).toFixed(1);
		body += `<path transform="rotate(${rot} ${fmt(x)} ${fmt(y)})" fill="currentColor" opacity="${opacity}" d="${sparklePath(x, y, r)}"/>`;
	}
	save('sparkle-dust.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid slice"'));
}

// =====================================================================
// 5. Arch family — see tools/graphics/lib/arches.mjs for the geometry
//    (a shared tangent-arc solver plus direct closed-form constructions,
//    replacing the previous hand-tuned bezier ogee, whose control points
//    fell outside its own viewBox and got clipped, and whose curve met
//    its straight jamb without matching tangent — both defects this
//    module's bbox guard and tangent solver make structurally impossible
//    now). Every panel is generated at the real aspect ratio of the CSS
//    class that consumes it (mask-size stretches 1:1, so mismatched
//    proportions here would distort on the live page — see README.md).
// =====================================================================

// Ogee arch — restored to the client's originally-approved silhouette (a
// smooth S-curve shoulder into a moderately blunt point), which an
// intervening detour (rebuilding it as the plainer curve seen in
// Assests/WhatsApp Image 2026-09-04 at 4.23.19 PM.jpeg) wrongly replaced
// wholesale when only the outline's *thickness* needed fixing. That
// mockup-fit detour is still ogeeArchPanel's own default parameterization
// (see its doc comment) — this call site overrides it with a DIFFERENT
// measured fit.
//
// The source bitmap for this shape (tools/graphics/reference/arch.png,
// itself traced from the client's image1.png) was deleted along with the
// rest of that now-unused trace pipeline during the detour, and neither
// was ever committed — gone for good. Recovered anyway, without asking
// for the file again: a Playwright screenshot taken earlier in the same
// session, before the detour, already shows this exact shape rendered at
// 1440px (.playwright-mcp/page-2026-09-08T10-15-43-116Z.png). Same
// fitting method as ogeeArchPanel's own doc describes — color-threshold
// boundary scan (this page has a subtle repeating background texture a
// plain background-difference scan picked up as noise; a gold-specific
// color threshold didn't), then least-squares cubic-bezier fit — measured
// jamb half-width 314px, rise 278px (riseFrac 0.885), fit RMSE 12.4px
// (3.9% of the half-width, in the same range as every other reference
// fit this way). Verified by rendering this exact panel and overlaying it
// (as a translucent mask) back onto that screenshot — the fitted curve
// tracks the screenshot's own gold line closely along its whole length.
//
// w:baseH restored to 307:459 (the aspect-ratio already confirmed fine,
// not the mockup's 400:459) — components.css's aspect-ratio matches.
{
	const w = 307, hw = w / 2, riseFrac = 0.8855;
	const c1 = [3.45 / 314, 23 / 278], c2 = [1, 165 / 278];
	const jamb = Math.round(hw * riseFrac), baseH = 459;
	const panel = ogeeArchPanel(w, jamb, baseH, { riseFrac, c1, c2 });
	save('arch-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));

	// Thin, open-bottomed outline: stroked directly on arch-mask's own
	// exact bbox (pad 0, not wrapArch's padded box) so it shares the
	// identical coordinate frame/viewBox as the mask — the fix for an
	// earlier bug where a separately-padded outline asset, stretched via a
	// CSS inset percentage that didn't match its own padding ratio,
	// distorted the curve. `panel.d` itself has no trailing "Z" (see
	// ogeeArchPanel) — fill (the mask above) treats an open subpath as
	// closed anyway, but this stroked render does NOT draw a line across
	// the base, reading as an open-bottomed frame rather than a capped box.
	save('arch-outline.svg', svgFromBbox(panel.bbox, `<path fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" d="${panel.d}"/>`, 0));

	// Double-line frame: outline plus a second, uniformly inset copy
	// (inset both axes, not just x, so the two lines stay concentric).
	const inset = 14;
	const inner = ogeeArchPanel(w - inset * 2, jamb - inset, baseH - inset * 2, { riseFrac, c1, c2 });
	const unionBbox = {
		minX: Math.min(panel.bbox.minX, inner.bbox.minX + inset),
		minY: Math.min(panel.bbox.minY, inner.bbox.minY + inset),
		maxX: Math.max(panel.bbox.maxX, inner.bbox.maxX + inset),
		maxY: Math.max(panel.bbox.maxY, inner.bbox.maxY + inset),
	};
	const frame =
		`<g fill="none" stroke="currentColor" stroke-width="2">` +
		`<path d="${panel.d}"/>` +
		`<path transform="translate(${fmt(inset)} ${fmt(inset)})" d="${inner.d}"/>` +
		`</g>`;
	save('arch-frame.svg', svgFromBbox(unionBbox, frame, 4));
}

// Cusped keel arch — the client's Home.jpeg hero silhouette (three outward
// lobes on a bulged support curve, sharp ogee point, vertical jambs). The
// profile is a measured fit, not a guess: see cuspedArchPanel's own comment
// in lib/arches.mjs for the trace, and QA/design-review/home.md finding 2.
// Verified by re-sampling the generated left profile against the reference's
// measured boundary (tools/graphics/scratch/fitcheck.mjs): RMSE 4.19px, max
// deviation 11.3px, on a 559px-wide arch — 0.75% of the width.
//
// Generated at the reference's own measured box (559x629, jamb at y291) so
// the CSS aspect-ratio consuming it matches 1:1 and mask-size cannot distort
// the curve.
{
	const w = 559, jamb = 291, baseH = 629;
	const panel = cuspedArchPanel(w, jamb, baseH);
	save('keel-arch-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('keel-arch-outline.svg', svgFromBbox(panel.bbox, `<path fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" d="${panel.d}"/>`, 0));

	// Double gold contour. The reference's two lines sit 11.5px apart on a
	// 537px-wide arch = 2.14% of the width (home.md finding 3) — half the
	// inset arch-frame.svg uses, so the ratio is taken from the measurement
	// rather than reusing that asset's 14px directly.
	const inset = Math.round(w * 0.0214);
	const inner = cuspedArchPanel(w - inset * 2, jamb - inset, baseH - inset * 2);
	const unionBbox = {
		minX: Math.min(panel.bbox.minX, inner.bbox.minX + inset),
		minY: Math.min(panel.bbox.minY, inner.bbox.minY + inset),
		maxX: Math.max(panel.bbox.maxX, inner.bbox.maxX + inset),
		maxY: Math.max(panel.bbox.maxY, inner.bbox.maxY + inset),
	};
	save('keel-arch-frame.svg', svgFromBbox(unionBbox,
		`<g fill="none" stroke="currentColor" stroke-width="2">` +
		`<path d="${panel.d}"/>` +
		`<path transform="translate(${inset} ${inset})" d="${inner.d}"/>` +
		`</g>`, 0)); // pad 0: the frame's OUTER contour must share the mask's exact
		// coordinate frame, or the two render at different scales in the same box
}

// Home2.jpeg's two small panels close into pointed lobed ends. Reuse the
// measured cusped arch for each cap, mirrored vertically; clipping each
// half at its jamb removes the hero arch's flat base without parsing path
// data or duplicating its arc solver. The cap height is an independent
// parameter, so the tall child panel adds straight jamb rather than
// stretching the nearly-square alphabet panel's caps.
function closedCartouche(w, h, capHeight, inset, stroke = false) {
	const cw = w - inset * 2, ch = h - inset * 2, cap = capHeight - inset;
	const stops = [[0, 1], [.0751, .3498 / .4626], [.2397, .1908 / .4626], [.4365, .0477 / .4626]]
		.map(([x, y]) => [x, y * cap / ch]);
	const { d } = cuspedArchPanel(cw, cap, ch, { stops });
	const id = `half-${inset}`;
	const attrs = stroke ? 'fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"' : 'fill="#fff"';
	// A one-unit overlap on each side avoids a raster seam at the shared
	// jamb; the contour is vertical, so this does not alter its shape.
	const half = `<g clip-path="url(#${id})"><path ${attrs} d="${d}"/></g>`;
	return `<defs><clipPath id="${id}"><rect x="-2" y="-2" width="${fmt(cw + 4)}" height="${fmt(ch / 2 + 3)}"/></clipPath></defs>` +
		`<g transform="translate(${inset} ${inset})">${half}<g transform="translate(0 ${fmt(ch)}) scale(1 -1)">${half}</g></g>`;
}
for (const [name, w, h, cap] of [['child', 246, 423, 123], ['alphabet', 276, 300, 150]]) {
	// Same viewBox, no CSS shrink: the photo contour follows the inner
	// frame at5.5px. Two1.2px lines at1.5/5.5 leave the cream ground visible.
	save(`cartouche-${name}-mask.svg`, svgWrap(w, h, closedCartouche(w, h, cap, 5.5), 'aria-hidden="true" focusable="false"'));
	save(`cartouche-${name}-frame.svg`, svgWrap(w, h, closedCartouche(w, h, cap, 1.5, true) + closedCartouche(w, h, cap, 5.5, true), 'aria-hidden="true" focusable="false"'));
}

// Four-centred (Persian/Timurid) arch — course/pricing card media frames.
// Its bbox comes out essentially exact (0..w, 0..baseH) at these
// proportions, but it's still wrapped via wrapArch() rather than a fixed
// box — the same safety net every other panel gets, on principle, not
// because this one happens to need the padding.
{
	const panel = fourCentredArchPanel(320, 200, 380);
	save('fourcentred-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('fourcentred-outline.svg', wrapArch(panel, { pad: 2, stroke: true, strokeWidth: 2.5 }));
}

// Horseshoe (Moorish) arch — decorative accent frame. Genuinely overshoots
// its nominal width (~9px/side at this scale, the bulge below the
// springline that makes it a horseshoe rather than a plain round arch).
{
	const panel = horseshoeArchPanel(300, 260, 340);
	save('horseshoe-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('horseshoe-outline.svg', wrapArch(panel, { pad: 2, stroke: true, strokeWidth: 2.5 }));
}

// Mandorla (pointed-oval) — teacher/avatar photo frames. Flatter than the
// equilateral default (e = 0.4*halfWidth, not halfWidth) so it reads as a
// gentle oval suited to a face photo rather than an aggressive lens; the
// flatter cap also genuinely overshoots the nominal height (~11px
// top/bottom), same reasoning as the keel and horseshoe arches above.
{
	const w = 240, h = 300;
	const panel = mandorlaPanel(w, h, { e: (w / 2) * 0.4 });
	save('mandorla-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('mandorla-outline.svg', wrapArch(panel, { pad: 2, stroke: true, strokeWidth: 2.5 }));
}

// Multifoil (inward-cusped) arch — card frame. Rebuilt on the shared
// scallop construction in lib/arches.mjs (previously a parallel,
// hand-rolled copy of the same math); 3 lobes per side, matching the
// clean mosque-window silhouette confirmed against the Flaticon reference.
// Inward cusps stay within the nominal box by construction (they bulge
// toward the centerline, never past it), so no overshoot here.
{
	const panel = multifoilArchPanel(260, 180, 320, { lobes: 3 });
	save('multifoil-arch-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('multifoil-arch-outline.svg', wrapArch(panel, { pad: 2, stroke: true, strokeWidth: 2.5 }));
}

// =====================================================================
// 7. Quatrefoil — 4-lobed frame (the "alphabet card" reference).
//    Construction: 4 circles of radius r, centers placed at distance r
//    from the shared middle on each axis, union of all 4 (classic
//    quatrefoil / four-petal construction).
// =====================================================================
function quatrefoilPath(size) {
	// 4 circles of radius r, centered at distance r from the shared middle
	// along each cardinal direction — each circle then passes exactly
	// through that shared middle point (center-to-origin distance == r ==
	// its own radius), which is what makes adjacent circles cross at two
	// clean points: the shared middle, and an outer "cusp" point. For the
	// N/E pair (centers (0,-r) and (r,0)), solving the two-circle
	// intersection gives cusp (r,-r) — and by the 4-fold symmetry the
	// other three cusps are just that point rotated 90/180/270°. The
	// union's outline is then just the 4 "outer" semicircle arcs between
	// consecutive cusps (the far half of each circle, not the half that
	// passes through the shared middle).
	const r = size * 0.32;
	const c = size / 2;
	const cusps = [
		[c + r, c - r], // between N and E petals
		[c + r, c + r], // between E and S petals
		[c - r, c + r], // between S and W petals
		[c - r, c - r], // between W and N petals
	];
	let d = `M${fmt(cusps[0][0])} ${fmt(cusps[0][1])} `;
	for (let i = 0; i < 4; i++) {
		const [ex, ey] = cusps[(i + 1) % 4];
		d += `A${fmt(r)} ${fmt(r)} 0 0 0 ${fmt(ex)} ${fmt(ey)} `;
	}
	return d + 'Z';
}

{
	const size = 200;
	const d = quatrefoilPath(size);
	save('quatrefoil-mask.svg', svgWrap(size, size, `<path fill="#fff" fill-rule="evenodd" d="${d}"/>`));
	save('quatrefoil-outline.svg', svgWrap(size, size, `<path fill="none" stroke="currentColor" stroke-width="2" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 7b. Mihrab finial — a pointed 4-lobe ornament (reference: the classic
//     "dome finial" cartouche shape seen on Islamic-design icon sets).
//     Built by taking one ogee-arch lobe and rotating it 4 times around a
//     shared center — reuses ogeeArchPath's own S-curve rather than a
//     second hand-authored curve, so the two shapes stay a visually
//     related family.
// =====================================================================
{
	// A dedicated symmetric "petal" curve (not a reuse of ogeeArchPath,
	// whose control-point formula assumes a small dome atop a tall
	// rectangle and distorts badly outside that range): two mirrored
	// cubic beziers from the shared center out to a pointed tip and back,
	// belly-bulging outward by `bulge` at their midpoint. Rotating one
	// petal 4 times around the center gives the finial.
	const size = 200;
	const cx = size / 2, cy = size / 2;
	const R = size * 0.42;
	const bulge = R * 0.5;
	const petal =
		`M${fmt(cx)} ${fmt(cy)} ` +
		`C${fmt(cx + bulge)} ${fmt(cy - R * 0.42)} ${fmt(cx + bulge * 0.5)} ${fmt(cy - R * 0.86)} ${fmt(cx)} ${fmt(cy - R)} ` +
		`C${fmt(cx - bulge * 0.5)} ${fmt(cy - R * 0.86)} ${fmt(cx - bulge)} ${fmt(cy - R * 0.42)} ${fmt(cx)} ${fmt(cy)} Z`;
	let body = '';
	for (let k = 0; k < 4; k++) {
		body += `<g transform="rotate(${k * 90} ${fmt(cx)} ${fmt(cy)})"><path d="${petal}"/></g>`;
	}
	save('mihrab-finial-mask.svg', svgWrap(size, size, `<g fill="#fff">${body}</g>`));
	save('mihrab-finial-outline.svg', svgWrap(size, size, `<g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">${body}</g>`, 'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 8. Corner frame — double gold arch line sweeping in from a corner
//    (Pricing-card reference). A quarter-circle arc pair.
// =====================================================================
{
	const size = 140;
	const r1 = size * 0.78;
	const r2 = size * 0.6;
	const body =
		`<g fill="none" stroke="currentColor" stroke-linecap="round">` +
		`<path stroke-width="2.5" d="M0 ${fmt(r1)} A${fmt(r1)} ${fmt(r1)} 0 0 1 ${fmt(r1)} 0"/>` +
		`<path stroke-width="1.5" d="M0 ${fmt(r2)} A${fmt(r2)} ${fmt(r2)} 0 0 1 ${fmt(r2)} 0"/>` +
		`</g>`;
	save('corner-frame.svg', svgWrap(size, size, body, 'aria-hidden="true" focusable="false"'));
}

// =====================================================================
// 9. Dividers — the fix for the reported broken heading rule, plus the
//    other 4 variants used across cards/hero/teacher-facts. All inline
//    currentColor (not masks), since dividers need to sit inline in text
//    flow and pick up the surrounding gold/cream color.
// =====================================================================

// divider-section: the main fix. A centered hairline with diamond
// terminals and a rosette medallion at the center — replaces the broken
// eqc-heading-rule--ornate (see components.css). The medallion is the
// same traced rosette as divider-medallion.svg/rosette.svg (see §1),
// not the plain spikedRosettePath star this divider used before.
{
	const w = 240, h = 32, cy = 16;
	const diamond = (cx) => regularPolygonPath(cx, cy, 3.2, 4, 0);
	const lineLen = 74;
	const body =
		`<g fill="none" stroke="currentColor" stroke-width="1">` +
		`<line x1="${fmt(w / 2 - lineLen - 16)}" y1="${cy}" x2="${fmt(w / 2 - 16)}" y2="${cy}"/>` +
		`<line x1="${fmt(w / 2 + 16)}" y1="${cy}" x2="${fmt(w / 2 + lineLen + 16)}" y2="${cy}"/>` +
		`</g>` +
		`<g fill="currentColor">` +
		`<path d="${diamond(w / 2 - lineLen - 16)}"/>` +
		`<path d="${diamond(w / 2 + lineLen + 16)}"/>` +
		`</g>` +
		tracedRosette(w / 2, cy, 11);
	save('divider-section.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// divider-eyebrow: short flanking rules around an eyebrow label (drawn as
// two short lines with generous side margins — the label itself is real
// text, positioned by the consuming CSS, so this asset is just the two
// rule segments as a background-less pair).
{
	const w = 120, h = 2;
	const body = `<line x1="0" y1="1" x2="${w}" y2="1" stroke="currentColor" stroke-width="1"/>`;
	save('divider-eyebrow.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// divider-card: short in-card rule with a small central rosette (same
// traced shape as divider-section.svg's medallion, smaller).
{
	const w = 140, h = 20, cy = 10;
	const lineLen = 46;
	const body =
		`<line x1="${fmt(w / 2 - lineLen - 10)}" y1="${cy}" x2="${fmt(w / 2 - 10)}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<line x1="${fmt(w / 2 + 10)}" y1="${cy}" x2="${fmt(w / 2 + lineLen + 10)}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		tracedRosette(w / 2, cy, 7);
	save('divider-card.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// divider-dot: hairline with a centre dot (teacher-card fact rows).
{
	const w = 120, h = 8, cy = 4;
	const body =
		`<line x1="0" y1="${cy}" x2="${fmt(w / 2 - 6)}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<line x1="${fmt(w / 2 + 6)}" y1="${cy}" x2="${w}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<circle cx="${fmt(w / 2)}" cy="${cy}" r="2" fill="currentColor"/>`;
	save('divider-dot.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// divider-accent: left-aligned short rule with a single diamond.
{
	const w = 90, h = 8, cy = 4;
	const diamond = regularPolygonPath(4, cy, 3.4, 4, 0);
	const body =
		`<path fill="currentColor" d="${diamond}"/>` +
		`<line x1="12" y1="${cy}" x2="${w}" y2="${cy}" stroke="currentColor" stroke-width="1"/>`;
	save('divider-accent.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// divider-rule: diamond - rule - diamond, the hero's gold rule under the H1.
// A separate asset rather than a second terminal on divider-accent above,
// because that one is also consumed by eqc_pricing_card() and the pricing
// reference has not been reviewed yet — changing it here would silently
// restyle a section nobody has measured. Merge the two if the pricing pass
// finds the same symmetric form. Proportion measured off Assests/Home.jpeg:
// rule x105-246 (141px) with ~8px diamonds, i.e. length:terminal 17.6:1
// (QA/design-review/home.md finding 5).
{
	const w = 120, h = 8, cy = 4, r = 3.4;
	const body =
		`<line x1="4" y1="${cy}" x2="${w - 4}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<path fill="currentColor" d="${regularPolygonPath(4, cy, r, 4, 0)}"/>` +
		`<path fill="currentColor" d="${regularPolygonPath(w - 4, cy, r, 4, 0)}"/>`;
	save('divider-rule.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

// pricing.jpeg uses a single central diamond between two hairlines.
{
	const w = 240, h = 16, cx = w / 2, cy = h / 2;
	const body = `<g fill="none" stroke="currentColor" stroke-width="1"><path d="M0 ${cy}H${cx - 12}M${cx + 12} ${cy}H${w}"/><path d="${regularPolygonPath(cx, cy, 5, 4, 0)}"/></g>`;
	save('divider-diamond.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

console.log('\nAll ornament assets generated to', OUT.href);
