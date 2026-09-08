// Generate the Islamic ornament/arch/divider asset system from exact
// parametric geometry (regular-polygon and arc trigonometry — see
// lib/geometry.mjs), replacing the previous hand-eyeballed 271-1444 byte
// placeholder SVGs. See tools/graphics/README.md for the construction
// notes behind each shape and DESIGN.md's asset-library section for how
// each is consumed (mask-image vs inline currentColor).
//
// Usage: node gen-ornaments.mjs
import { writeFileSync, mkdirSync } from 'node:fs';
import { optimize } from 'svgo';
import {
	fmt,
	starPolygonPath,
	spikedRosettePath,
	regularPolygonPath,
} from './lib/geometry.mjs';

// Flat into the theme's existing assets/svg/ — no new subfolder (CLAUDE.md:
// don't create parallel structures). Where a direct predecessor exists
// (rosette.svg, lattice-corner.svg, arch-mask.svg, arch-outline.svg) this
// overwrites it in place, so every existing CSS `url('../svg/NAME.svg')`
// mask reference keeps working unchanged — only genuinely new shapes get
// new filenames.
const OUT = 'wp-content/themes/easy-quran-classes-child/assets/svg';
mkdirSync(OUT, { recursive: true });

function save(name, svg) {
	const { data } = optimize(svg, { multipass: true, plugins: ['preset-default'] });
	writeFileSync(`${OUT}/${name}`, data);
	console.log(name.padEnd(30), data.length, 'bytes');
}

const svgWrap = (w, h, body, attrs = '') =>
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" ${attrs}>${body}</svg>`;

// =====================================================================
// 1. Rosettes — badge medallions and divider centerpieces.
//    Outline style (stroke only) so they work as mask-image silhouettes
//    exactly like the asset they replace; a filled variant is also
//    produced for inline currentColor use (divider medallions, the new
//    star-8 bullet icon).
// =====================================================================
for (const points of [8, 12]) {
	const cx = 32, cy = 32;
	const star = starPolygonPath(cx, cy, 26, points, Math.floor(points / 2) - 1 || 3, -Math.PI / 2);
	const body =
		`<g fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round">` +
		`<circle cx="${cx}" cy="${cy}" r="30"/>` +
		`<path d="${star}"/>` +
		`<circle cx="${cx}" cy="${cy}" r="9"/>` +
		`</g>`;
	// 8-point overwrites the existing rosette.svg in place (same CSS mask
	// references keep working); 12-point ships alongside as a bonus variant.
	const filename = points === 8 ? 'rosette.svg' : `rosette-${points}.svg`;
	save(filename, svgWrap(64, 64, body, 'aria-hidden="true" focusable="false"'));
}

// Filled 8-point star bullet — pricing feature lists, replacing plain
// checkmarks per the reference screenshots' gold-star list markers.
{
	const d = starPolygonPath(12, 12, 10, 8, 3);
	save('star-8-filled.svg', svgWrap(24, 24, `<path fill="currentColor" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
}

// Divider medallion (small, for the section-heading rule).
{
	const d = spikedRosettePath(16, 16, 15, 6.5, 8);
	save('divider-medallion.svg', svgWrap(32, 32, `<path fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
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

// Corner placement: the same lattice, radially faded from one corner so
// it reads as an ornament rather than a wallpaper repeat when used once
// (replaces lattice-corner.svg). Built by tiling the fine unit across a
// larger canvas and fading opacity by distance from the top-right corner.
{
	const a = 30;
	const { body, D } = girihTile(a);
	const cols = 5, rows = 5;
	const canvas = D * cols;
	let field = '';
	for (let i = 0; i < cols; i++) {
		for (let j = 0; j < rows; j++) {
			const x = canvas - (i + 1) * D; // dense at top-right: i=0 is the rightmost column
			const y = j * D;
			const distFromCorner = Math.hypot(i, j) / Math.hypot(cols, rows);
			const opacity = Math.max(0, 1 - distFromCorner * 1.15);
			if (opacity <= 0.02) continue;
			field += `<g transform="translate(${fmt(x)} ${fmt(y)})" opacity="${fmt(opacity)}">${body}</g>`;
		}
	}
	save('lattice-corner.svg', svgWrap(canvas, canvas, `<g fill="none" stroke="currentColor" stroke-width="1" stroke-linejoin="round">${field}</g>`, 'aria-hidden="true" focusable="false"'));
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
			body += `<path d="${regularPolygonPath(x, y, s, 6, 0)}"/>`;
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
// 5. Ogee (mihrab) arch — hero/photo mask + double-line outline frame.
//
//    Construction: a true ogee profile is two S-curves (each a pair of
//    circular arcs of opposite curvature) rising from the springline to
//    a point, flanked by vertical sides below the springline. Parametrized
//    by width w, total height h, springline height (fraction of h) and
//    shoulder outset (how far the S-curve bulges past the vertical sides)
//    — not eyeballed control points.
// =====================================================================
function ogeeArchPath(w, h, { springline = 0.62, flareOut = 0.34, tuck = 0.32 } = {}) {
	// True onion/ogee profile as a single S-curve cubic per side: an
	// S-curve bezier is exactly "control point 1 on one side of the
	// start-to-end chord, control point 2 on the other side" — here
	// control 1 sits outward (past the vertical base line: the convex
	// shoulder) and control 2 sits inward (toward the centerline: the
	// concave finish into the point). `flareOut` sets the shoulder's
	// outward reach, `tuck` how sharply it pulls back in near the apex —
	// both as fractions of the half-width.
	const spring = h * (1 - springline);
	const hw = w / 2;
	// Left half, base (0,spring) -> apex (hw,0).
	const c1x = -hw * flareOut;
	const c1y = spring * 0.62;
	const c2x = hw * tuck;
	const c2y = spring * 0.16;
	const d =
		`M${fmt(w)} ${fmt(h)} L${fmt(w)} ${fmt(spring)} ` +
		// right half mirrored: base(w,spring) -> apex(hw,0)
		`C${fmt(w - c1x)} ${fmt(c1y)} ${fmt(w - c2x)} ${fmt(c2y)} ${fmt(hw)} 0 ` +
		// left half: apex(hw,0) -> base(0,spring)
		`C${fmt(hw + c2x)} ${fmt(c2y)} ${fmt(c1x)} ${fmt(c1y)} 0 ${fmt(spring)} ` +
		`L0 ${fmt(h)} Z`;
	return d;
}

{
	const w = 400, h = 500;
	const d = ogeeArchPath(w, h);
	// Overwrites the previous arch-mask.svg / arch-outline.svg in place —
	// same CSS references (.eqc-arch-media--masked) keep working unchanged.
	save('arch-mask.svg', svgWrap(w, h, `<path fill="#fff" d="${d}"/>`));
	save('arch-outline.svg', svgWrap(w, h, `<path fill="none" stroke="currentColor" stroke-width="3" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));

	// Double-line frame: the outline plus a second, inset-offset outline —
	// approximated by drawing the same path at two scales from the arch's
	// own base-center, which keeps both lines concentric without a true
	// (and much more complex) path-offset algorithm.
	const inset = 14;
	const d2 = ogeeArchPath(w - inset * 2, h - inset);
	const frame =
		`<g fill="none" stroke="currentColor" stroke-width="2">` +
		`<path d="${d}"/>` +
		`<path transform="translate(${fmt(inset)} 0)" d="${d2}"/>` +
		`</g>`;
	save('arch-frame.svg', svgWrap(w, h, frame, 'aria-hidden="true" focusable="false"'));
}


// =====================================================================
// 6. Multifoil (cusped) arch — card frame.
//
//    Construction (documented method, see tools/graphics/README.md):
//    take the two straight support lines of a pointed arch from the
//    springline to the apex, divide each into `lobes` equal sections, and
//    draw a semicircle on each section bulging into the arch. This is the
//    standard intersecting-circles construction for a multifoil arch.
// =====================================================================
function multifoilArchPath(w, h, lobes = 5, springline = 0.55) {
	const spring = h * (1 - springline);
	const apex = [w / 2, 0];
	const baseL = [0, spring];
	const baseR = [w, spring];
	// Points along each support line from base to apex.
	const lerp = (a, b, t) => [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
	const leftPts = Array.from({ length: lobes + 1 }, (_, i) => lerp(baseL, apex, i / lobes));
	const rightPts = Array.from({ length: lobes + 1 }, (_, i) => lerp(apex, baseR, i / lobes));
	const supportPts = [...leftPts.slice(0, -1), ...rightPts]; // apex counted once

	// Each semicircle must bulge toward the arch's interior (right, for the
	// rising left support line; left, for the falling right support line).
	// SVG's arc sweep-flag=1 draws the "positive angle" arc between two
	// points — for a line rising left-to-right (left support half) that is
	// the arc bulging to its right (into the arch); for a line falling
	// left-to-right (right support half) sweep-flag=1 bulges to its left
	// (also into the arch). So sweep=1 is correct for every segment here
	// regardless of which half it's on — confirmed by rendering (see
	// tools/graphics/README.md).
	let d = `M0 ${fmt(h)} L0 ${fmt(spring)} `;
	for (let i = 0; i < supportPts.length - 1; i++) {
		const [x1, y1] = supportPts[i];
		const [x2, y2] = supportPts[i + 1];
		const r = Math.hypot(x2 - x1, y2 - y1) / 2;
		d += `A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(x2)} ${fmt(y2)} `;
	}
	d += `L${fmt(w)} ${fmt(h)} Z`;
	return d;
}

{
	// 3 lobes per side (a shoulder cusp + a taller center point, not a busy
	// scalloped edge) — matches the clean mosque-window silhouette in the
	// reference more closely than the originally-tried 5-per-side version.
	const w = 260, h = 320;
	const d = multifoilArchPath(w, h, 3, 0.42);
	save('multifoil-arch-mask.svg', svgWrap(w, h, `<path fill="#fff" d="${d}"/>`));
	save('multifoil-arch-outline.svg', svgWrap(w, h, `<path fill="none" stroke="currentColor" stroke-width="2.5" d="${d}"/>`, 'aria-hidden="true" focusable="false"'));
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
// eqc-heading-rule--ornate (see components.css).
{
	const w = 240, h = 32, cy = 16;
	const diamond = (cx) => regularPolygonPath(cx, cy, 3.2, 4, 0);
	const medallion = spikedRosettePath(w / 2, cy, 11, 5, 8);
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
		`<path fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" d="${medallion}"/>`;
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

// divider-card: short in-card rule with a small central rosette.
{
	const w = 140, h = 20, cy = 10;
	const medallion = spikedRosettePath(w / 2, cy, 7, 3, 8);
	const lineLen = 46;
	const body =
		`<line x1="${fmt(w / 2 - lineLen - 10)}" y1="${cy}" x2="${fmt(w / 2 - 10)}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<line x1="${fmt(w / 2 + 10)}" y1="${cy}" x2="${fmt(w / 2 + lineLen + 10)}" y2="${cy}" stroke="currentColor" stroke-width="1"/>` +
		`<path fill="none" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round" d="${medallion}"/>`;
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

// divider-accent: left-aligned short rule with a single diamond (hero).
{
	const w = 90, h = 8, cy = 4;
	const diamond = regularPolygonPath(4, cy, 3.4, 4, 0);
	const body =
		`<path fill="currentColor" d="${diamond}"/>` +
		`<line x1="12" y1="${cy}" x2="${w}" y2="${cy}" stroke="currentColor" stroke-width="1"/>`;
	save('divider-accent.svg', svgWrap(w, h, body, 'aria-hidden="true" focusable="false"'));
}

console.log('\nAll ornament assets generated to', OUT);
