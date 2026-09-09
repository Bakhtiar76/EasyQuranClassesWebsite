/**
 * Shared geometry for the project-owned icons in build-icon-sprite.mjs.
 *
 * The reference design reuses a handful of shapes across many icons — a bust
 * silhouette appears in six of them, a shield body in three, a mortarboard in
 * three, a five-point star in four. Each is defined once here and composed,
 * so the icons stay consistent with each other by construction rather than by
 * two paths happening to have been drawn the same way.
 *
 * Everything is on the same 24x24 grid the rest of the sprite uses.
 */
import { fmt as f, starPolygonPath, polar } from './geometry.mjs';

/* ------------------------------------------------------------------ people */

/**
 * Head + shoulders silhouette. The design draws people as a circle over a
 * flat-bottomed dome, never as a rounded-rectangle torso.
 *
 * @param {number} cx     centre of the figure
 * @param {number} hy     head centre y
 * @param {number} hr     head radius
 * @param {number} halfW  half-width of the shoulders
 * @param {number} top    y of the shoulder line
 * @param {number} base   y of the flat bottom
 * @param {number} round  0..1, how round the shoulder corners are
 */
export function bust(cx, hy, hr, halfW, top, base, round = 0.86) {
	const r = halfW * round;
	return `<circle cx="${f(cx)}" cy="${f(hy)}" r="${f(hr)}"/>`
		+ `<path d="M${f(cx - halfW)} ${f(base)}V${f(top + r)}`
		+ `a${f(r)} ${f(r)} 0 0 1 ${f(r)}-${f(r)}`
		+ `h${f(2 * (halfW - r))}`
		+ `a${f(r)} ${f(r)} 0 0 1 ${f(r)} ${f(r)}`
		+ `V${f(base)}Z"/>`;
}

/**
 * Head with a covering — the band across a cap or headscarf, drawn as a slot
 * knocked out of the head with even-odd rather than painted in a second
 * colour, so it stays transparent on cream and on dark green alike.
 *
 * The slot is kept comfortably inside the circle: with even-odd, any part of
 * it that fell outside would be *added* to the fill, not removed from it.
 */
export function headWithBand(cx, cy, r, at = 0.5, thickness = 0.46) {
	const halfSlot = r * 0.72;
	const y = cy - r * at;
	return `<path fill-rule="evenodd" d="M${f(cx - r)} ${f(cy)}`
		+ `a${f(r)} ${f(r)} 0 1 0 ${f(2 * r)} 0a${f(r)} ${f(r)} 0 1 0-${f(2 * r)} 0Z`
		+ `M${f(cx - halfSlot)} ${f(y)}h${f(2 * halfSlot)}v${f(thickness)}h-${f(2 * halfSlot)}Z"/>`;
}

/** Shoulders only, clipped to one side — used for figures standing behind. */
export function bustBehind(cx, hy, hr, halfW, top, base, side, round = 0.9) {
	const r = halfW * round;
	const inner = side === 'left' ? cx + halfW : cx - halfW;
	const outer = side === 'left' ? cx - halfW : cx + halfW;
	const sweep = side === 'left' ? 1 : 0;
	return `<circle cx="${f(cx)}" cy="${f(hy)}" r="${f(hr)}"/>`
		+ `<path d="M${f(outer)} ${f(base)}V${f(top + r)}`
		+ `a${f(r)} ${f(r)} 0 0 ${sweep} ${f(side === 'left' ? r : -r)}-${f(r)}`
		+ `L${f(inner)} ${f(top)}V${f(base)}Z"/>`;
}

/* ------------------------------------------------------------------ shield */

/** Shield body: flat shouldered top, sides drawing in to a rounded point. */
export const SHIELD = 'M12 1.5C9 3.6 5.9 4.5 3 4.9v6.2c0 5 3.7 9 9 11.4 5.3-2.4 9-6.4 9-11.4V4.9c-2.9-.4-6-1.3-9-3.4Z';

/** The same shield scaled about its own centre, for shield-inside-shield. */
export function shieldInset(scale) {
	const cx = 12;
	const cy = 12;
	return `<g transform="translate(${f(cx - cx * scale)} ${f(cy - cy * scale)}) scale(${f(scale)})">`
		+ `<path d="${SHIELD}"/></g>`;
}

/* -------------------------------------------------------------------- star */

/** Five-point star. `waist` sets how chunky it is (higher = fatter arms). */
export function star5(cx, cy, r, waist = 0.475) {
	const pts = [];
	for (let i = 0; i < 10; i++) {
		const rr = i % 2 ? r * waist : r;
		const a = -Math.PI / 2 + (i * Math.PI) / 5;
		const [px, py] = polar(cx, cy, rr, a);
		pts.push(`${f(px)} ${f(py)}`);
	}
	return `M${pts.join('L')}Z`;
}

/** Eight-point sparkle/diamond with concave sides — the design's "diamond". */
export function sparkle4(cx, cy, rx, ry, pinch = 0.16) {
	const px = rx * pinch;
	const py = ry * pinch;
	return `M${f(cx)} ${f(cy - ry)}`
		+ `C${f(cx + px)} ${f(cy - py)} ${f(cx + px)} ${f(cy - py)} ${f(cx + rx)} ${f(cy)}`
		+ `C${f(cx + px)} ${f(cy + py)} ${f(cx + px)} ${f(cy + py)} ${f(cx)} ${f(cy + ry)}`
		+ `C${f(cx - px)} ${f(cy + py)} ${f(cx - px)} ${f(cy + py)} ${f(cx - rx)} ${f(cy)}`
		+ `C${f(cx - px)} ${f(cy - py)} ${f(cx - px)} ${f(cy - py)} ${f(cx)} ${f(cy - ry)}Z`;
}

/**
 * Rosette with ROUNDED lobes — the design's badge/bullet motif. This is a
 * different shape from geometry.mjs's spikedRosettePath, whose lobes come to
 * a point; using the spiked one for these read as a cog rather than a flower.
 */
export function lobedRosettePath(cx, cy, rOuter, rInner, lobes) {
	let d = '';
	for (let i = 0; i < lobes; i++) {
		const a0 = -Math.PI / 2 + (i * Math.PI * 2) / lobes;
		const a1 = a0 + (Math.PI * 2) / lobes;
		const mid = (a0 + a1) / 2;
		const [x0, y0] = polar(cx, cy, rInner, a0);
		const [x1, y1] = polar(cx, cy, rInner, a1);
		const [xm, ym] = polar(cx, cy, rOuter, mid);
		const lobeR = (rOuter - rInner) * 1.35 + rInner * 0.12;
		d += (i === 0 ? `M${f(x0)} ${f(y0)}` : '')
			+ `A${f(lobeR)} ${f(lobeR)} 0 0 1 ${f(xm)} ${f(ym)}`
			+ `A${f(lobeR)} ${f(lobeR)} 0 0 1 ${f(x1)} ${f(y1)}`;
	}
	return d + 'Z';
}

/* ---------------------------------------------------------- mortarboard */

/**
 * Graduation cap. The reference always shows the crown/band under the board,
 * which Lucide's cap omits — that band is what makes it read as a cap rather
 * than a paper dart at 20px.
 *
 * @param {boolean} tassel include the tassel cord and bead
 */
export function mortarboard({ cy = 7.4, halfW = 10.6, boardH = 3.4, bandW = 5.9, bandTop = 12, bandH = 3.2, tassel = true, band = true, tasselSide = 'right', tasselLen = 5.2 } = {}) {
	const cx = 12;
	const board = `M${f(cx)} ${f(cy - boardH)}L${f(cx + halfW)} ${f(cy)}`
		+ `L${f(cx)} ${f(cy + boardH)}L${f(cx - halfW)} ${f(cy)}Z`;
	// Crown: straight sides, bulged lower edge, sitting just below the board
	// with a visible gap -- the reference separates the two clearly.
	const taper = bandW * 0.12;
	const bandPath = `M${f(cx - bandW)} ${f(bandTop)}h${f(2 * bandW)}`
		+ `l-${f(taper)} ${f(bandH)}`
		+ `q0 ${f(bandW * 0.3)} -${f(bandW - taper)} ${f(bandW * 0.3)}`
		+ `q-${f(bandW - taper)} 0 -${f(bandW - taper)}-${f(bandW * 0.3)}Z`;
	let extra = '';
	if (tassel) {
		const tx = tasselSide === 'left' ? cx - halfW + 1.6 : cx + halfW - 1.6;
		extra = `<path d="M${f(tx)} ${f(cy + 0.4)}v${f(tasselLen)}" `
			+ `fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>`
			+ `<circle cx="${f(tx)}" cy="${f(cy + tasselLen + 1.7)}" r="1.3"/>`;
	}
	return `<path d="${board}"/>${band ? `<path d="${bandPath}"/>` : ''}${extra}`;
}

/* ---------------------------------------------------------------- book */

/**
 * Open book: two page blocks meeting at a spine, flat along the bottom.
 * `filled` gives the solid gold book of the courses eyebrow and the teacher
 * medallion; the outline form is the same construction stroked.
 */
export function bookOpen({ top = 4.4, bottom = 16.6, halfW = 9.4, spine = 0.9, sag = 2.1 } = {}) {
	const cx = 12;
	const ox = cx - halfW;          // outer edge, left page
	const left = `M${f(cx - spine)} ${f(top + sag)}`
		+ `C${f(cx - halfW * 0.62)} ${f(top - 0.2)} ${f(ox + halfW * 0.2)} ${f(top)} ${f(ox)} ${f(top)}`
		+ `V${f(bottom)}`
		+ `c${f(halfW * 0.38)} 0 ${f(halfW * 0.72)} .6 ${f(halfW - spine)} ${f(sag)}Z`;
	const right = `M${f(cx + spine)} ${f(top + sag)}`
		+ `C${f(cx + halfW * 0.62)} ${f(top - 0.2)} ${f(cx + halfW - halfW * 0.2)} ${f(top)} ${f(cx + halfW)} ${f(top)}`
		+ `V${f(bottom)}`
		+ `c-${f(halfW * 0.38)} 0 -${f(halfW * 0.72)} .6 -${f(halfW - spine)} ${f(sag)}Z`;
	return { left, right };
}

/* --------------------------------------------------------------- quotes */

/** The design's double quote: two filled commas, opening (66) not closing. */
export const QUOTE_PAIR = 'M10.3 3.3C4.7 4.8 2 8.7 2 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Zm11 0C15.7 4.8 13 8.7 13 14.2c0 3.6 1.7 5.3 4.2 5.3a4 4 0 0 0 .5-8c.4-2.7 1.6-4.7 4.3-6.3Z';

/** Same marks scaled into a box, for the speech-bubble variant. */
export function quotePair(scale, dx, dy) {
	return `<g transform="translate(${f(dx)} ${f(dy)}) scale(${f(scale)})"><path d="${QUOTE_PAIR}"/></g>`;
}

export { starPolygonPath };
