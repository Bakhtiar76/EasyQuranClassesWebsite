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
import {
	arcCmd,
	wrapArch,
	svgFromBbox,
	keelArchPanel,
	fourCentredArchPanel,
	horseshoeArchPanel,
	mandorlaPanel,
	multifoilArchPanel,
} from './lib/arches.mjs';

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

// Corner ornament: previously a girih-lattice field cropped to a square
// canvas and faded by a formula that never actually reached zero alpha at
// its own inner edges (opacity floor ~0.35) — the straight-edged "pasted
// wallpaper" patches flagged in review. Replaced with a proper arabesque
// bracket: concentric quarter-arcs anchored at the corner (the same
// "double gold line sweeping in from a corner" construction as
// corner-frame.svg, generalized to N rings), a rosette and finial as the
// ornament's own deliberate silhouette, and a girih fill CLIPPED to a
// curved annular wedge — bounded by real geometry on every edge, not a
// crop — with a radial-gradient mask as a second, independent guarantee
// that alpha reaches true zero well inside the viewBox.
{
	const size = 240;
	const corner = [size, 0]; // top-right, matching the existing --tr/--bl CSS convention
	const rings = [0.3, 0.48, 0.66, 0.86].map((f) => size * f);
	const arcs = rings
		.map((r, i) => {
			const from = [size - r, 0], to = [size, r];
			const strokeW = fmt(2.6 - i * 0.5);
			return `<path fill="none" stroke="currentColor" stroke-width="${strokeW}" stroke-linecap="round" d="M${fmt(from[0])} ${fmt(from[1])} ${arcCmd(corner[0], corner[1], r, from, to)}"/>`;
		})
		.join('');

	// Rosette at the second ring, finial (small drop) at the outermost
	// point along the corner's own diagonal.
	const diag = Math.SQRT1_2;
	const rosetteR = rings[1];
	const rosetteCenter = [size - rosetteR * diag, rosetteR * diag];
	const rosette = spikedRosettePath(rosetteCenter[0], rosetteCenter[1], 13, 6, 8);
	const finialR = rings[3] + 14;
	const finialCenter = [size - finialR * diag, finialR * diag];
	const finial = spikedRosettePath(finialCenter[0], finialCenter[1], 7, 3, 4);

	// Girih fill clipped to the annular wedge between the first and third
	// rings — a shape with real curved inner AND outer edges, so it can
	// never read as a rectangular crop.
	const rInner = rings[0], rOuter = rings[2];
	const wedgeClip =
		`M${fmt(size - rOuter)} 0 ${arcCmd(corner[0], corner[1], rOuter, [size - rOuter, 0], [size, rOuter])} ` +
		`L${fmt(size)} ${fmt(rInner)} ${arcCmd(corner[0], corner[1], rInner, [size, rInner], [size - rInner, 0])} Z`;
	const { body: latticeBody, D: latticeD } = girihTile(26);
	let latticeField = '';
	for (let i = -1; i <= Math.ceil(size / latticeD); i++) {
		for (let j = -1; j <= Math.ceil(size / latticeD); j++) {
			latticeField += `<g transform="translate(${fmt(size - (i + 1) * latticeD)} ${fmt(j * latticeD)})">${latticeBody}</g>`;
		}
	}

	const svg = svgWrap(
		size, size,
		`<defs>` +
			`<clipPath id="wedge"><path d="${wedgeClip}"/></clipPath>` +
			`<radialGradient id="fade" cx="${fmt(corner[0])}" cy="${fmt(corner[1])}" r="${fmt(rings[3])}" gradientUnits="userSpaceOnUse">` +
			`<stop offset="0%" stop-color="#fff"/><stop offset="70%" stop-color="#fff" stop-opacity="0.5"/><stop offset="100%" stop-color="#fff" stop-opacity="0"/>` +
			`</radialGradient>` +
			`<mask id="fadeMask"><rect width="${size}" height="${size}" fill="url(#fade)"/></mask>` +
			`</defs>` +
			`<g clip-path="url(#wedge)" mask="url(#fadeMask)">` +
			`<g fill="none" stroke="currentColor" stroke-width="1" stroke-linejoin="round">${latticeField}</g>` +
			`</g>` +
			`<g mask="url(#fadeMask)">${arcs}</g>` +
			`<path fill="currentColor" d="${rosette}"/>` +
			`<path fill="currentColor" opacity="0.6" d="${finial}"/>`,
		'aria-hidden="true" focusable="false"'
	);
	save('lattice-corner.svg', svg);
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

// Keel/Mughal cusped arch — matches the client's reference: jamb, a
// larger lower bump, a smaller upper bump, then a sharp point. Replaces
// arch-mask/outline/frame in place (same CSS references keep working
// unchanged). Its bbox comes out an exact 400:500 at this scale (no
// overshoot), but it's still wrapped via wrapArch() rather than a fixed
// box — the same safety net every arch in this file gets, on principle,
// matching .eqc-arch-media--masked's 4:5 aspect-ratio in components.css.
{
	const w = 400, jamb = 170, baseH = 500;
	const panel = keelArchPanel(w, jamb, baseH);
	save('arch-mask.svg', wrapArch(panel, { pad: 0, fill: '#fff' }));
	save('arch-outline.svg', wrapArch(panel, { pad: 2, stroke: true, strokeWidth: 3 }));

	// Double-line frame: outline plus a second, uniformly inset copy
	// (inset both axes, not just x, so the two lines stay concentric),
	// wrapped to the union of both panels' true bboxes.
	const inset = 14;
	const inner = keelArchPanel(w - inset * 2, jamb - inset, baseH - inset * 2);
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
