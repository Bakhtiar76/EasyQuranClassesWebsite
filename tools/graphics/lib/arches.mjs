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

/**
 * Circle through two given points P1, P2 with a chosen "bulge" — the
 * center sits on the perpendicular bisector of P1-P2, offset by
 * `bulge * |P1-P2|` (sign picks which side, magnitude how deeply it
 * curves). Unlike nextTangentArc (solving for a circle tangent to a
 * PRIOR circle), this has no reflex/continuation branch to go unstable:
 * bulge is a plain, bounded design choice, so it can't produce the
 * tiny/huge-radius degenerate arcs a forced tangent-solve can when the
 * target point sits awkwardly relative to the previous circle (this is
 * exactly what happened trying to force ogeeArchPanel's sharp apex via
 * nextTangentArc — small parameter changes flipped the solver into a
 * self-intersecting loop at the tip).
 */
function arcThroughBulge(P1, P2, bulge) {
	const mid = [(P1[0] + P2[0]) / 2, (P1[1] + P2[1]) / 2];
	const d = sub(P2, P1);
	const length = len(d);
	const perp = [-d[1] / length, d[0] / length];
	const center = [mid[0] + perp[0] * bulge * length, mid[1] + perp[1] * bulge * length];
	const radius = len(sub(center, P1));
	return { center, radius };
}

// ---------------------------------------------------------------------
// Ogee/keel arch: one convex shoulder arc off the jamb, into one concave
// arc up to a sharp apex — the classic two-arc-per-side ogee S-curve,
// matching the client's clean keel-arch reference (a single smooth
// shoulder bulge per side, not a multi-cusp scallop, and a true point at
// the top, not a soft dome).
//
// The shoulder arc's centre is constrained to the springline (exactly
// fourCentredArchPanel's own haunch construction), which is what
// guarantees it departs the vertical jamb tangentially — an
// arcThroughBulge chord here left a visible kink at the springline,
// since nothing tied its tangent direction to the jamb. The finish arc
// (shoulder's end point up to the sharp apex) uses arcThroughBulge, not
// the tangent-arc solver: forcing exact tangency into a sharp apex point
// is ill-conditioned there (the solved circle's radius can blow up or
// collapse for small parameter changes, producing a self-intersecting
// loop at the tip — confirmed by rendering an early attempt), whereas
// choosing its bulge directly is stable by construction and still reads
// as a smooth continuous sweep once tuned by rendering.
// ---------------------------------------------------------------------
export function ogeeArchPanel(w, jamb, baseH, { shoulderFrac = 0.12, shoulderSweepDeg = 48, bulge2 = 0.32, riseFrac = 0.62 } = {}) {
	const hw = w / 2;
	const spring = jamb;
	const apex = [hw, spring - hw * riseFrac];
	const J0 = [w, spring];

	// Shoulder: centre on the springline (tangent to the vertical jamb at
	// J0), sweeping CCW by shoulderSweepDeg to J1.
	const r1 = hw * shoulderFrac;
	const C1 = [w - r1, spring];
	const theta = (shoulderSweepDeg * Math.PI) / 180;
	const J1 = [C1[0] + r1 * Math.cos(Math.PI - theta), C1[1] - r1 * Math.sin(Math.PI - theta)];
	const shoulderR = arcCmd(C1[0], C1[1], r1, J0, J1);

	const finish = arcThroughBulge(J1, apex, bulge2);
	const finishR = arcCmd(finish.center[0], finish.center[1], finish.radius, J1, apex);

	// Left half is the exact mirror across x = hw, traced apex -> base.
	const mirrorX = (p) => [2 * hw - p[0], p[1]];
	const J1l = mirrorX(J1), J0l = mirrorX(J0), C1l = mirrorX(C1);
	const finishCl = mirrorX(finish.center);
	const finishL = arcCmd(finishCl[0], finishCl[1], finish.radius, apex, J1l);
	const shoulderL = arcCmd(C1l[0], C1l[1], r1, J1l, J0l);

	const d = `M${fmt(w)} ${fmt(baseH)} L${fmt(w)} ${fmt(spring)} ${shoulderR} ${finishR} ${finishL} ${shoulderL} L0 ${fmt(baseH)} Z`;

	let bbox = { minX: 0, minY: 0, maxX: w, maxY: baseH };
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C1[0], C1[1], r1, J0, J1)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(finish.center[0], finish.center[1], finish.radius, J1, apex)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(finishCl[0], finishCl[1], finish.radius, apex, J1l)));
	bbox = mergeBbox(bbox, bboxOf(circleExtent(C1l[0], C1l[1], r1, J1l, J0l)));
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
// Scalloped support-line construction — the multifoil arch (Gothic
// tracery: semicircles bulging INTO the arch, drawn on chords of the
// straight base->apex support line).
//
// Why this can never kink at a cusp junction: each semicircle is drawn on
// a chord of the *same straight line*; a semicircle's tangent at its own
// diameter endpoint is always perpendicular to that diameter, so two
// adjacent semicircles on collinear chords share the identical tangent
// direction at their shared endpoint — automatically G1-continuous, no
// solver needed. The final short run to the apex is deliberately a
// straight line, not another arc: a multifoil arch ends in a visible
// point, not a smooth dome, so a small corner there is correct.
// ---------------------------------------------------------------------
/** One side of a scalloped support line, base->apex. Returns the path
 * fragment AND the exact extent points of every semicircle drawn (each
 * one computed with the SAME sweepFlag used in the path, via
 * semicircleExtent, so the reported bbox can never disagree with what's
 * actually drawn). */
function scallopSide(base, apex, breakpoints) {
	const pts = [base, ...breakpoints.map((t) => lerp(base, apex, t))];
	const sweepFlag = 1; // inward — verified by rendering, see README.md
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

/** Classic multifoil (inward-cusped) card frame. `breakpoints` are
 * fractions along the base->apex line where each cusp ends. */
export function multifoilArchPanel(w, jamb, baseH, { lobes = 3 } = {}) {
	const breakpoints = Array.from({ length: lobes - 1 }, (_, i) => (i + 1) / lobes);
	const hw = w / 2;
	const apex = [hw, 0];
	const baseR = [w, jamb], baseL = [0, jamb];
	const sweepFlag = 1; // inward — same flag scallopSide uses, verified symmetric by rendering
	const right = scallopSide(baseR, apex, breakpoints);
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
