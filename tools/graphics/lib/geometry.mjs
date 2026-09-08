// Shared exact-geometry helpers for the ornament generator. Every shape
// here is derived from closed-form regular-polygon/arc trigonometry, not
// eyeballed coordinates — see gen-ornaments.mjs for how each is used and
// README.md for the construction notes.

export const TAU = Math.PI * 2;

export function polar(cx, cy, r, angleRad) {
	return [cx + r * Math.cos(angleRad), cy + r * Math.sin(angleRad)];
}

export function fmt(n) {
	// Trim to 3 decimals, strip trailing zeros — keeps generated path data small.
	return (Math.round(n * 1000) / 1000).toString();
}

export function pathFromPoints(points, close = true) {
	const [first, ...rest] = points;
	let d = `M${fmt(first[0])} ${fmt(first[1])}`;
	for (const [x, y] of rest) d += ` L${fmt(x)} ${fmt(y)}`;
	if (close) d += ' Z';
	return d;
}

/** Vertices of a regular n-gon, circumradius r, centered at (cx,cy), first vertex at angleOffset. */
export function polygonVertices(cx, cy, r, n, angleOffset = -Math.PI / 2) {
	const pts = [];
	for (let k = 0; k < n; k++) {
		pts.push(polar(cx, cy, r, angleOffset + (k * TAU) / n));
	}
	return pts;
}

/**
 * A {n/step} star polygon: connect every `step`-th vertex of a regular
 * n-gon. Requires gcd(n, step) === 1 for a single unicursal path (e.g.
 * n=8, step=3 gives the classic 8-point star used throughout this
 * project). Returns one closed path visiting all n vertices.
 */
export function starPolygonPath(cx, cy, r, n, step, angleOffset = -Math.PI / 2) {
	const verts = polygonVertices(cx, cy, r, n, angleOffset);
	const order = [];
	let i = 0;
	for (let k = 0; k < n; k++) {
		order.push(i);
		i = (i + step) % n;
	}
	return pathFromPoints(order.map((idx) => verts[idx]));
}

/**
 * A two-radius "spiked" rosette (alternating outer point / inner valley),
 * the shape used for badge medallions and divider centerpieces — distinct
 * from starPolygonPath (which connects a single ring of vertices with
 * skips; this alternates two rings, giving sharper valleys).
 */
export function spikedRosettePath(cx, cy, rOuter, rInner, points, angleOffset = -Math.PI / 2) {
	const n = points * 2;
	const verts = [];
	for (let k = 0; k < n; k++) {
		const r = k % 2 === 0 ? rOuter : rInner;
		verts.push(polar(cx, cy, r, angleOffset + (k * TAU) / n));
	}
	return pathFromPoints(verts);
}

export function regularPolygonPath(cx, cy, r, n, angleOffset = -Math.PI / 2) {
	return pathFromPoints(polygonVertices(cx, cy, r, n, angleOffset));
}
