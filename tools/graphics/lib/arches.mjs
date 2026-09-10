// Islamic arch geometry — every profile here is either a direct closed-form
// circle construction (pointed, horseshoe, mandorla — textbook drafting
// methods, see tools/graphics/README.md for citations) or built from the
// tangent-arc solver below (four-centred, and the cusped keel arch's
// scallop-on-a-straight-support-line construction, which is automatically
// tangent-continuous by the geometry of a chord — see the multifoil/keel
// section). None of this is hand-tuned bezier control points: every
// coordinate is derived from w/h and a small number of *named* proportion
// parameters, and every shape reports its own true bounding box so the
// caller can never clip it (the defect this module replaces).
import { fmt } from './geometry.mjs';

// ---------------------------------------------------------------------
// Vector + arc-command primitives
// ---------------------------------------------------------------------

const sub = (a, b) => [a[0] - b[0], a[1] - b[1]];
const add = (a, b) => [a[0] + b[0], a[1] + b[1]];
const scale = (a, s) => [a[0] * s, a[1] * s];
const len = (a) => Math.hypot(a[0], a[1]);
const unit = (a) => scale(a, 1 / len(a));
const dot = (a, b) => a[0] * b[0] + a[1] * b[1];

/**
 * Given a circle (C1, r1) and a point J that lies ON it (the point where an
 * adjacent straight line or arc is already tangent to this circle), solve
 * for the unique second circle (C2, r2) that is tangent to the first AT J
 * (centers C1, J, C2 colinear — the definition of tangent circles) and
 * that also passes through a target point P. This is the one primitive
 * that makes every multi-arc profile below tangent-continuous by
 * construction: no junction can ever show a kink, because a kink is
 * exactly what this equation forbids.
 *
 * Derivation: let u = unit(J-C1), C2 = J + t*u for unknown signed t
 * (r2 = |t|; t<0 continues curving the same way as circle 1, t>0 reflects
 * to the opposite curvature — an inflection, e.g. the ogee's S-curve).
 * Requiring |C2-P| = |t| expands to a *linear* equation in t (the t^2
 * terms cancel), so there is exactly one solution — not a quadratic with
 * a branch to pick.
 */
function nextTangentArc(C1, J, P) {
	const u = unit(sub(J, C1));
	const V = sub(J, P);
	const denom = 2 * dot(V, u);
	const t = -dot(V, V) / denom;
	const C2 = add(J, scale(u, t));
	const r2 = Math.abs(t);
	return { C2, r2 };
}

const TWO_PI = Math.PI * 2;
const normAngle = (a) => ((a % TWO_PI) + TWO_PI) % TWO_PI;

/**
 * SVG elliptical-arc command from (cx,cy,r) between two points already
 * known to lie on that circle — always the *minor* (≤180°) arc between
 * them, or the *major* (>180°) arc if `long` is set (only the horseshoe
 * arch needs that). Deliberately does not take a hand-picked
 * clockwise/counter-clockwise "sense": an earlier version did, and got it
 * wrong for a mirrored shape (mirroring across an axis flips which sense
 * label is correct, which is exactly the kind of by-hand reasoning this
 * module exists to avoid). Picking "the short way" vs "the long way" is
 * an unambiguous, mirror-safe question — it only depends on the actual
 * angle between the two points, not on which side they started from.
 */
export function arcCmd(cx, cy, r, from, to, { long = false } = {}) {
	const angleFrom = Math.atan2(from[1] - cy, from[0] - cx);
	const angleTo = Math.atan2(to[1] - cy, to[0] - cx);
	const swept1 = normAngle(angleTo - angleFrom); // the sweep-flag=1 arc's angle
	const wantSweep1 = long ? swept1 > Math.PI : swept1 <= Math.PI;
	const sweepFlag = wantSweep1 ? 1 : 0;
	const largeArcFlag = long ? 1 : 0;
	return `A${fmt(r)} ${fmt(r)} 0 ${largeArcFlag} ${sweepFlag} ${fmt(to[0])} ${fmt(to[1])}`;
}

const lerp = (a, b, t) => [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];

// ---------------------------------------------------------------------
// Bounding-box tracking + final SVG assembly. Every arch function below
// returns { d, bbox }; wrapArch() turns that into a complete <svg> whose
// viewBox is derived from the *true* extent of the geometry (with the
// jamb/base rectangle folded in), so a shoulder or cusp can never be cut
// off by a mismatched hand-picked canvas size — the defect that produced
// the previous session's clipped hero arch.
// ---------------------------------------------------------------------

/** The extreme points of an arc are its two endpoints plus any axis
 * crossing (angle 0/90/180/270) it actually sweeps through — the shared
 * core used by both circleExtent (arcCmd's minor/major choice) and
 * semicircleExtent (scallopSide's manually-chosen sweep flag), so a
 * bbox can never disagree with the path actually drawn. */
function arcExtentCore(cx, cy, r, from, to, sweepFlag) {
	const pts = [from, to];
	const angleFrom = Math.atan2(from[1] - cy, from[0] - cx);
	const angleTo = Math.atan2(to[1] - cy, to[0] - cx);
	const swept1 = normAngle(angleTo - angleFrom);
	const wantSweep1 = sweepFlag === 1;
	// Whichever endpoint the sweep starts from, it always proceeds in the
	// "increasing angle" direction from there — that's the one invariant
	// shared by both the sweep1 and sweep0 cases (verified algebraically:
	// starting at angleTo and adding 2π-swept1 lands back on angleFrom).
	const [startAngle, sweptAngle] = wantSweep1 ? [angleFrom, swept1] : [angleTo, TWO_PI - swept1];
	for (const axis of [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2]) {
		if (normAngle(axis - startAngle) <= sweptAngle) pts.push([cx + r * Math.cos(axis), cy + r * Math.sin(axis)]);
	}
	return pts;
}

function circleExtent(cx, cy, r, from, to, { long = false } = {}) {
	const angleFrom = Math.atan2(from[1] - cy, from[0] - cx);
	const angleTo = Math.atan2(to[1] - cy, to[0] - cx);
	const swept1 = normAngle(angleTo - angleFrom);
	const wantSweep1 = long ? swept1 > Math.PI : swept1 <= Math.PI;
	return arcExtentCore(cx, cy, r, from, to, wantSweep1 ? 1 : 0);
}

/** Extent of an exact semicircle on the chord from->to, given the same
 * sweepFlag scallopSide already picked by hand (0/1 is unambiguous there
 * since "which half of the circle" can't be inferred from "shorter arc"
 * when both halves are exactly 180°). */
function semicircleExtent(from, to, sweepFlag) {
	const cx = (from[0] + to[0]) / 2, cy = (from[1] + to[1]) / 2;
	const r = len(sub(to, from)) / 2;
	return arcExtentCore(cx, cy, r, from, to, sweepFlag);
}

function bboxOf(points) {
	let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
	for (const [x, y] of points) {
		if (x < minX) minX = x;
		if (x > maxX) maxX = x;
		if (y < minY) minY = y;
		if (y > maxY) maxY = y;
	}
	return { minX, minY, maxX, maxY };
}

function mergeBbox(a, b) {
	return {
		minX: Math.min(a.minX, b.minX), minY: Math.min(a.minY, b.minY),
		maxX: Math.max(a.maxX, b.maxX), maxY: Math.max(a.maxY, b.maxY),
	};
}

/** viewBox attribute value that exactly fits `bbox` plus `pad` on every
 * side — the one place the "derive the canvas from the true geometry"
 * rule lives, so every consumer (a single path, or custom multi-element
 * body like a double-line frame) gets it the same way. */
export function viewBoxFor(bbox, pad = 2) {
	const minX = bbox.minX - pad, minY = bbox.minY - pad;
	const w = bbox.maxX - bbox.minX + 2 * pad, h = bbox.maxY - bbox.minY + 2 * pad;
	return `${fmt(minX)} ${fmt(minY)} ${fmt(w)} ${fmt(h)}`;
}

export function svgFromBbox(bbox, body, pad = 2) {
	return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBoxFor(bbox, pad)}" aria-hidden="true" focusable="false">${body}</svg>`;
}

export function wrapArch({ d, bbox }, { pad = 2, stroke, strokeWidth = 3, fill } = {}) {
	const p = stroke ? pad + strokeWidth : pad;
	const fillAttr = fill ? `fill="${fill}"` : 'fill="none"';
	const strokeAttr = stroke ? `stroke="currentColor" stroke-width="${strokeWidth}" stroke-linejoin="round"` : '';
	const path = `<path ${fillAttr} ${strokeAttr} d="${d}"/>`;
	return svgFromBbox(bbox, path, p);
}

// ---------------------------------------------------------------------
// Two-centred pointed arch (the Gothic/mosque-window pointed arch).
// Centres sit on the springline, offset e from the centreline; radius
// R = halfSpan + e. e = halfSpan is the classic "equilateral" arch
// (centres AT the opposite springing points, rise = halfSpan*sqrt(3));
// e > halfSpan gives a taller "lancet"; e < halfSpan a flatter "drop"
// arch. https://en.wikipedia.org/wiki/Pointed_arch
// ---------------------------------------------------------------------
export function pointedArch(w, jamb, { e } = {}) {
	const hw = w / 2;
	const ee = e ?? hw;
	const R = hw + ee;
	const spring = jamb;
	const rise = Math.sqrt(Math.max(R * R - ee * ee, 0));
	const apexY = spring - rise;
	const apex = [hw, apexY];
	const baseL = [0, spring], baseR = [w, spring];
	const cR = [hw - ee, spring], cL = [hw + ee, spring];

	let bbox = bboxOf([baseL, baseR]);
	bbox = mergeBbox(bbox, bboxOf(circleExtent(cR[0], cR[1], R, baseR, apex)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(cL[0], cL[1], R, apex, baseL)));

	// Callers assemble the closed path themselves (jamb/base handling
	// differs per consumer — panel vs. outline-only); this returns the
	// solved centres/radius/bbox shared by every consumer.
	return { apex, baseL, baseR, cL, cR, R, bbox };
}

/** Closed fill/mask silhouette for a pointed arch sitting on a rectangular
 * jamb of height `jamb` down to a base of height `baseH` (baseH=jamb draws
 * no extra rectangle below the springline; pass a larger baseH for a photo
 * frame that extends further down). */
export function pointedArchPanel(w, jamb, baseH, opts = {}) {
	const { apex, baseL, baseR, cL, cR, R, bbox } = pointedArch(w, jamb, opts);
	const rightArc = arcCmd(cR[0], cR[1], R, baseR, apex);
	const leftArc = arcCmd(cL[0], cL[1], R, apex, baseL);
	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(baseR[1])} ${rightArc} ${leftArc} L0 ${fmt(baseH)} Z`;
	const panelBbox = mergeBbox(bbox, { minX: 0, minY: 0, maxX: w, maxY: baseH });
	return { d, bbox: panelBbox };
}

/**
 * Ogee arch: a vertical jamb into ONE smooth cubic-bezier curve per side
 * (apex → `c2` → `c1` → springline), generic over `c1`/`c2`/`riseFrac` so
 * it can be re-fit to whichever reference bitmap is currently authoritative
 * — gen-ornaments.mjs's actual arch-mask/outline call site passes its own
 * measured values, not these defaults; see that call site's own comment
 * for which reference it's currently fit to and why (this asset's shape
 * has changed reference more than once this project — recorded in
 * README.md's "hero/photo arch" section, worth reading before changing
 * either the defaults here or the call site).
 *
 * `c1`/`c2` are the fitted bezier's two control points, as fractions of
 * (halfSpan, rise) in a frame with the apex at the origin — this is what
 * makes the curve scale cleanly to any panel size. `riseFrac` (rise ÷
 * halfSpan) is measured the same way. Fitting method: scan the reference
 * bitmap (or, when the source bitmap itself is gone, a saved screenshot
 * that rendered it — see README.md) for its own boundary to get a dx(dy)
 * half-width profile from apex to springline, then least-squares fit a
 * single cubic bezier to that profile — reliably within ~4% RMSE of the
 * jamb half-width on every reference tried so far. A two-centred circular
 * arc through the same endpoints, tried once as a first guess, missed by
 * 4-5x that error — confirming these curves are drawn freehand, not
 * derived from a circle, so don't re-try that shortcut.
 *
 * Bbox is exact with no extra computation needed: a cubic bezier's curve
 * always stays within the convex hull of its 4 control points, and here
 * all 4 (apex, c1, c2, springline) lie within the [0,halfSpan]×[0,rise]
 * box by construction (c1, c2 are both fractions in [0,1]) — so the panel
 * can't overshoot its own w×baseH rectangle, the same guarantee every
 * other arch in this module gets via `circleExtent`, just via a different
 * (and here, simpler) argument.
 */
export function ogeeArchPanel(w, jamb, baseH, {
	riseFrac = 1.0554,
	c1 = [0.4626, 0.2063],
	c2 = [1.0, 0.7448],
} = {}) {
	const hw = w / 2;
	const rise = hw * riseFrac;
	const apexY = jamb - rise;
	const apex = [hw, apexY];
	const ctrl = (c) => [hw + c[0] * hw, apexY + c[1] * rise];
	const P1r = ctrl(c1), P2r = ctrl(c2);
	const mirrorX = (p) => [2 * hw - p[0], p[1]];
	const P1l = mirrorX(P1r), P2l = mirrorX(P2r);
	// Deliberately no trailing "Z": fill treats an open subpath as if
	// closed by a straight line back to the start anyway (so arch-mask.svg,
	// which needs fill, renders identically either way), but a stroked
	// render of this same `d` (arch-outline.svg) then does NOT draw that
	// closing line across the base — the frame reads as open at the
	// bottom, not capped, matching the reference.
	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(jamb)} ` +
		`C${fmt(P2r[0])} ${fmt(P2r[1])} ${fmt(P1r[0])} ${fmt(P1r[1])} ${fmt(apex[0])} ${fmt(apex[1])} ` +
		`C${fmt(P1l[0])} ${fmt(P1l[1])} ${fmt(P2l[0])} ${fmt(P2l[1])} 0 ${fmt(jamb)} L0 ${fmt(baseH)}`;
	const bbox = { minX: 0, minY: apexY, maxX: w, maxY: baseH };
	return { d, bbox };
}

// ---------------------------------------------------------------------
// Four-centred (Persian/Timurid) arch: a steep small-radius haunch from
// each springing point, tangent-transitioning (via nextTangentArc) into a
// wide shallow crown arc that reaches the apex. This is the textbook
// "two radii per side" construction — https://en.wikipedia.org/wiki/Four-centred_arch
// — solved exactly rather than eyeballed: pick the haunch radius and how
// far it sweeps, then the solver finds the unique crown arc that stays
// tangent AND lands exactly on the chosen apex.
// ---------------------------------------------------------------------
export function fourCentredArchPanel(w, jamb, baseH, { haunchFrac = 0.32, haunchSweepDeg = 52, riseFrac = 0.5 } = {}) {
	const hw = w / 2;
	const spring = jamb;
	const r1 = hw * haunchFrac;
	const apex = [hw, spring - hw * riseFrac];

	// Right haunch: centre left of the right springing point (tangent to
	// the vertical jamb there), sweeping CCW (up and inward) by haunchSweepDeg.
	const C1r = [w - r1, spring];
	const theta = (haunchSweepDeg * Math.PI) / 180;
	const J1r = [C1r[0] + r1 * Math.cos(Math.PI - theta), C1r[1] - r1 * Math.sin(Math.PI - theta)];
	const { C2: C2r, r2: r2r } = nextTangentArc(C1r, J1r, apex);

	const haunchR = arcCmd(C1r[0], C1r[1], r1, [w, spring], J1r);
	const crownR = arcCmd(C2r[0], C2r[1], r2r, J1r, apex);

	// Left half is the exact mirror across x = hw, traced apex -> base
	// (the reverse direction of the right half — arcCmd's minor-arc choice
	// is direction-agnostic, so no sense/orientation bookkeeping is needed).
	const mirrorX = (p) => [2 * hw - p[0], p[1]];
	const C1l = mirrorX(C1r), J1l = mirrorX(J1r), C2l = mirrorX(C2r);
	const crownL = arcCmd(C2l[0], C2l[1], r2r, apex, J1l);
	const haunchL = arcCmd(C1l[0], C1l[1], r1, J1l, [0, spring]);

	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(spring)} ${haunchR} ${crownR} ${crownL} ${haunchL} L0 ${fmt(baseH)} Z`;

	let bbox = { minX: 0, minY: 0, maxX: w, maxY: baseH };
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C1r[0], C1r[1], r1, [w, spring], J1r)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C2r[0], C2r[1], r2r, J1r, apex)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C2l[0], C2l[1], r2r, apex, J1l)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C1l[0], C1l[1], r1, J1l, [0, spring])));
	return { d, bbox };
}

// ---------------------------------------------------------------------
// Horseshoe (Moorish/keyhole) arch: a single circle whose centre sits
// ABOVE the springline, so the arc from one springing point to the other
// sweeps the *major* arc (>180°) — bulging out past the jamb width below
// the springline before curving back in to meet it. Raising the centre by
// R/3 above the springline (so the circle passes exactly through both
// springing points) is the documented classical proportion —
// https://en.wikipedia.org/wiki/Horseshoe_arch — and fully determines R
// from the span alone: R = halfSpan * sqrt(9/8).
// ---------------------------------------------------------------------
export function horseshoeArchPanel(w, jamb, baseH) {
	const hw = w / 2;
	const R = hw * Math.sqrt(9 / 8);
	const raise = R / 3;
	const O = [hw, jamb - raise];
	const baseL = [0, jamb], baseR = [w, jamb];
	// The major (long-way) arc — through the bulge below/around — is what
	// makes this a horseshoe instead of a plain round arch.
	const arc = arcCmd(O[0], O[1], R, baseR, baseL, { long: true });
	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(jamb)} ${arc} L0 ${fmt(baseH)} Z`;
	let bbox = { minX: 0, minY: 0, maxX: w, maxY: baseH };
	bbox = mergeBbox(bbox, bboxOf(circleExtent(O[0], O[1], R, baseR, baseL, { long: true })));
	return { d, bbox };
}

// ---------------------------------------------------------------------
// Mandorla (pointed-oval) frame — a top pointed arch and a mirrored
// bottom pointed arch sharing the same widest points, built from the same
// two-centred construction as pointedArch (called once per half). Used
// for teacher/avatar photo frames per the client reference.
// ---------------------------------------------------------------------
export function mandorlaPanel(w, h, opts = {}) {
	const midY = h / 2;
	const top = pointedArch(w, 0, opts); // apex at y = -rise relative to spring=0
	// top half traced from baseR(w,0) up to apex, then apex down to baseL(0,0)
	const topApex = [top.apex[0], midY + top.apex[1]]; // top.apex[1] is negative (rise above spring=0)
	const topRight = arcCmd(top.cR[0], top.cR[1] + midY, top.R, [w, midY], topApex);
	const topLeft = arcCmd(top.cL[0], top.cL[1] + midY, top.R, topApex, [0, midY]);
	// bottom half: exact vertical mirror of the top half about y = midY
	const bottomApex = [top.apex[0], midY - top.apex[1]];
	const cRb = [top.cR[0], midY - (top.cR[1] - 0)];
	const cLb = [top.cL[0], midY - (top.cL[1] - 0)];
	// One continuous closed path: right-top -> apex-top -> left-top ->
	// left-bottom -> apex-bottom -> right-bottom -> close.
	const bottomRight = arcCmd(cRb[0], cRb[1], top.R, bottomApex, [w, midY]);
	const bottomLeft = arcCmd(cLb[0], cLb[1], top.R, [0, midY], bottomApex);
	const d = `M${fmt(w)} ${fmt(midY)} ${topRight} ${topLeft} ${bottomLeft} ${bottomRight} Z`;

	let bbox = bboxOf([[0, midY], [w, midY], topApex, bottomApex]);
	bbox = mergeBbox(bbox, bboxOf(circleExtent(top.cR[0], top.cR[1] + midY, top.R, [w, midY], topApex)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(top.cL[0], top.cL[1] + midY, top.R, topApex, [0, midY])));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(cLb[0], cLb[1], top.R, [0, midY], bottomApex)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(cRb[0], cRb[1], top.R, bottomApex, [w, midY])));
	return { d, bbox };
}

// ---------------------------------------------------------------------
// Scalloped support-line construction — semicircles drawn on chords of
// the straight base->apex support line. Used both by the multifoil arch
// (Gothic tracery: cusps bulge INWARD, into the arch) and the keel/Mughal
// arch below (cusps bulge OUTWARD, away from the arch — the client's
// keel-arch reference: a vertical jamb, two stacked outward bumps, then
// a sharp point).
//
// Why this can never kink at a cusp junction: each semicircle is drawn on
// a chord of the *same straight line*; a semicircle's tangent at its own
// diameter endpoint is always perpendicular to that diameter, so two
// adjacent semicircles on collinear chords share the identical tangent
// direction at their shared endpoint — automatically G1-continuous, no
// solver needed. The final short run to the apex is deliberately a
// straight line, not another arc: both arches end in a visible point,
// not a smooth dome, so a small corner there is correct.
// ---------------------------------------------------------------------
/** One side of a scalloped support line, base->apex. Returns the path
 * fragment AND the exact extent points of every semicircle drawn (each
 * one computed with the SAME sweepFlag used in the path, via
 * semicircleExtent, so the reported bbox can never disagree with what's
 * actually drawn). `outward` picks which side the cusps bulge — verified
 * by rendering, not assumed, since SVG's arc sweep-flag doesn't behave
 * symmetrically in an intuitive way without checking. */
function scallopSide(base, apex, breakpoints, outward) {
	const pts = [base, ...breakpoints.map((t) => lerp(base, apex, t))];
	const sweepFlag = outward ? 0 : 1;
	let d = '';
	let extent = [];
	for (let i = 0; i < pts.length - 1; i++) {
		const [from, to] = [pts[i], pts[i + 1]];
		const r = len(sub(to, from)) / 2;
		d += `A${fmt(r)} ${fmt(r)} 0 0 ${sweepFlag} ${fmt(to[0])} ${fmt(to[1])} `;
		extent = extent.concat(semicircleExtent(from, to, sweepFlag));
	}
	d += `L${fmt(apex[0])} ${fmt(apex[1])} `; // final straight run to the sharp point
	return { d, extent };
}

function scallopedArchPanel(w, jamb, baseH, breakpoints, outward) {
	const hw = w / 2;
	const apex = [hw, 0];
	const baseR = [w, jamb], baseL = [0, jamb];
	const sweepFlag = outward ? 0 : 1;
	const right = scallopSide(baseR, apex, breakpoints, outward);
	const leftPts = [apex, ...breakpoints.map((t) => lerp(baseL, apex, t)).reverse(), baseL];
	let leftD = '';
	let leftExtent = [];
	for (let i = 0; i < leftPts.length - 1; i++) {
		const [from, to] = [leftPts[i], leftPts[i + 1]];
		if (i === 0) { leftD += `L${fmt(to[0])} ${fmt(to[1])} `; continue; }
		const r = len(sub(to, from)) / 2;
		leftD += `A${fmt(r)} ${fmt(r)} 0 0 ${sweepFlag} ${fmt(to[0])} ${fmt(to[1])} `;
		leftExtent = leftExtent.concat(semicircleExtent(from, to, sweepFlag));
	}
	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(jamb)} ${right.d} ${leftD} L0 ${fmt(baseH)} Z`;
	const bbox = mergeBbox(
		bboxOf([[0, 0], [w, 0], [0, baseH], [w, baseH]]),
		bboxOf([...right.extent, ...leftExtent])
	);
	return { d, bbox };
}

/** Classic multifoil (inward-cusped) card frame. `breakpoints` are
 * fractions along the base->apex line where each cusp ends. */
export function multifoilArchPanel(w, jamb, baseH, { lobes = 3 } = {}) {
	const breakpoints = Array.from({ length: lobes - 1 }, (_, i) => (i + 1) / lobes);
	return scallopedArchPanel(w, jamb, baseH, breakpoints, false);
}

/** Keel / Mughal arch — the OUTWARD-cusped half of the same scalloped
 * support-line construction: a vertical jamb, `bumps` stacked lobes that
 * bulge away from the arch, then a sharp point. The construction was
 * already written and documented in the header comment above ("the
 * keel/Mughal arch below") but only the inward variant was ever exported.
 *
 * NOTE: this even-semicircle form does NOT fit the client's Home.jpeg hero
 * arch — see cuspedArchPanel below for the measured profile and why. Kept
 * because it is the natural companion to multifoilArchPanel and the
 * general form of the same construction. */
export function keelArchPanel(w, jamb, baseH, { bumps = 2, breakpoints } = {}) {
	const bp = breakpoints ?? Array.from({ length: bumps }, (_, i) => (i + 1) / (bumps + 1));
	return scallopedArchPanel(w, jamb, baseH, bp, true);
}

// ---------------------------------------------------------------------
// Measured cusped keel arch — the client's actual hero silhouette.
//
// Why this is not scallopedArchPanel: that construction draws SEMIcircles
// on chords of the straight base->apex line, so the profile necessarily
// returns to that line at every cusp and each lobe's depth is exactly half
// its chord. The reference does neither. Measured off Assests/Home.jpeg by
// per-row background difference (tools/graphics/scratch/silhouette.mjs;
// numbers in QA/design-review/home.md finding 2):
//
//   arch box x684-1243, y120-749 -> 559x629, aspect 0.8887
//   vertical jamb begins y411    -> jamb/baseH 0.4626
//   three outward lobes, peak horizontal deviation from the straight
//   base->apex line 25.8 / 54.7 / 38.2px, with the CUSPS BETWEEN THEM
//   still 24.5 and 26.1px clear of that line — i.e. the lobes sit on an
//   underlying bulged curve, not on the chord — and lobe depth/chord
//   ratios of 0.265 / 0.40 / 0.31 where a semicircle would give 0.50.
//
// So the profile is specified directly by its measured cusp points plus a
// per-segment sagitta, and each segment is a circular arc through two
// known points with a known bulge: r = (c^2/4 + s^2) / 2s, centre on the
// perpendicular bisector at (r - s) inward from the chord midpoint. The
// cusp junctions are deliberately NOT tangent-continuous — a cusp is a
// visible corner, which is the whole point of the shape, so the
// nextTangentArc solver is not wanted here.
//
// Coordinates are fractions of w (x) and baseH (y) from the apex, so the
// shape scales to any box at the measured aspect ratio.
// ---------------------------------------------------------------------

/** Circular arc command from `from` to `to` bulging `sag` (perpendicular,
 * in the direction of `outward`) — both points and the bulge are known, so
 * the radius and centre are fully determined. */
function bulgedArc(from, to, sag, outwardSign) {
	const chord = sub(to, from), c = len(chord);
	if (!(sag > 1e-6)) return { d: `L${fmt(to[0])} ${fmt(to[1])} `, extent: [to] };
	const r = (c * c / 4 + sag * sag) / (2 * sag);
	const mid = lerp(from, to, 0.5);
	// perpendicular to the chord; outwardSign picks which side bulges.
	const n = scale([-chord[1] / c, chord[0] / c], outwardSign);
	const centre = sub(add(mid, scale(n, sag)), scale(n, r));
	const crest = add(mid, scale(n, sag));
	return { d: arcCmd(centre[0], centre[1], r, from, to) + ' ', extent: [from, to, crest] };
}

/** Measured three-lobe cusped keel arch (the Home.jpeg hero). `stops` are
 * [x/w, y/baseH] cusp points from the apex down the LEFT side, `sagittae`
 * the perpendicular bulge of each segment as a fraction of w. Defaults are
 * the measured fit; override to fit a different reference.
 *
 * `bottomRadius` rounds the two square base corners into quarter-arcs. The
 * client's About collage reference (QA/qa-10092026/14.png) closes the arch's
 * foot with a curve; the hero's reference does not, so this is opt-in and the
 * default output is byte-identical to the square-footed original. The rounded
 * path deliberately STARTS at (r, baseH) and ENDS at (w-r, baseH), so the
 * existing `d.replace(/Z$/, '')` trick that derives the open-bottom frame
 * keeps working: dropping the close removes exactly the flat base and leaves
 * corner-arc + jambs + arch as one continuous stroked run. */
/** The measured bulge of each cusp segment, as a fraction of w. Exported so
 *  callers that need to SCALE the profile (closedCartouche's lobeDepth) derive
 *  it from here instead of keeping their own copy that silently stops matching
 *  the moment this profile is retuned. */
export const CUSPED_SAGITTAE = [0.0130, 0.0363, 0.0218];

export function cuspedArchPanel(w, jamb, baseH, {
	// left-side cusps, base -> apex (x fraction of w, y fraction of baseH)
	stops = [[0.0000, 0.4626], [0.0751, 0.3498], [0.2397, 0.1908], [0.4365, 0.0477]],
	sagittae = CUSPED_SAGITTAE,
	bottomRadius = 0,
} = {}) {
	const hw = w / 2, apex = [hw, 0];
	const P = stops.map(([fx, fy]) => [fx * w, fy * baseH]);
	const mirror = (p) => [w - p[0], p[1]];

	// Left side: base corner up through each cusp, then a straight run to
	// the point (the reference's final run measures 0.4-5.0px off straight).
	let leftD = '', extent = [...P];
	for (let i = 0; i < P.length - 1; i++) {
		const seg = bulgedArc(P[i], P[i + 1], sagittae[i] * w, -1);
		leftD += seg.d; extent = extent.concat(seg.extent);
	}
	leftD += `L${fmt(apex[0])} ${fmt(apex[1])} `;

	// Right side mirrored: apex down through the mirrored cusps to the base.
	const R = P.map(mirror).reverse();
	let rightD = `L${fmt(R[0][0])} ${fmt(R[0][1])} `; // mirror of the left's straight run off the point
	for (let i = 0; i < R.length - 1; i++) {
		const seg = bulgedArc(R[i], R[i + 1], sagittae[sagittae.length - 1 - i] * w, -1);
		rightD += seg.d; extent = extent.concat(seg.extent);
	}
	// Clamp so a radius can never eat past the jamb or the half-width.
	const r = Math.max(0, Math.min(bottomRadius, w / 2, baseH - P[0][1]));

	rightD += `L${fmt(w)} ${fmt(baseH - r)} `;

	// Sweep flag 1 on both corners: the run travels up the left jamb, over the
	// crown and down the right, which is clockwise in SVG's y-down space, so
	// both quarter-arcs turn the same way to stay convex.
	const startD = r
		? `M${fmt(r)} ${fmt(baseH)} A${fmt(r)} ${fmt(r)} 0 0 1 0 ${fmt(baseH - r)} `
		: `M0 ${fmt(baseH)} `;
	const endD = r ? `A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(w - r)} ${fmt(baseH)} ` : '';

	const d = `${startD}L${fmt(P[0][0])} ${fmt(P[0][1])} ${leftD}${rightD}${endD}Z`;
	const bbox = mergeBbox(bboxOf([[0, 0], [w, 0], [0, baseH], [w, baseH]]), bboxOf(extent));
	return { d, bbox };
}
