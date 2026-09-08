// Trace the client's reference rosette (tools/graphics/reference/rosette.png
// — an 8-fold girih star: a small 8-point star void at the centre,
// surrounded by 8 interlacing lens/kite "petals") into exact vector path
// data, replacing the simple {n/step} star (starPolygonPath/spikedRosettePath
// in lib/geometry.mjs) used until now for divider medallions and the
// rosette mask badges.
//
// Unlike a stroke-outline reference that just needs filling solid (see
// git history's now-removed trace-arch.mjs for that pattern), this
// reference is topologically significant: the centre star and the gaps
// between petals are meant to stay holes, not get filled in. So this does
// NOT flood-fill from the border — potrace traces the ink pixels
// directly, and its own even-odd fill-rule output already gets the holes
// right (verified: 10 subpaths — 1 outer boundary, 8 congruent
// petal-hole loops at 45° apart, 1 centre-star void).
//
// Symmetrization is majority-vote (lib/trace.mjs's symmetrizeDihedral), not
// union: this is a thin (~4-5px) stroke, and unioning 16 rotated/mirrored
// copies of a thin stroke systematically grows it every single time (each
// rotation's nearest-pixel rounding can only add area). A 50%-of-16 vote
// cancels that bias and reproduces the true stroke width.
//
// A note on the IoU verification gate: for a stroke this thin, IoU is
// intrinsically noisy — the reference bitmap compared against *itself*
// shifted by a single pixel already drops to ~0.93 IoU (confirmed by
// direct measurement), because a 1px edge disagreement on a 4px stroke is
// a large fraction of the stroke's own area. So unlike the arch (a big
// filled shape, where 0.99+ is the right bar), the rosette's gate is
// "at or above that same-image 1px-shift noise floor" plus a direct visual
// side-by-side render, not a flat 0.99.
//
// Usage: node tools/graphics/trace-rosette.mjs
import { writeFileSync } from 'node:fs';
import { loadMask, symmetrizeDihedral, maskToPngBuffer, tracePath, rasterizePath, iou } from './lib/trace.mjs';

const SRC = 'tools/graphics/reference/rosette.png';
const OUT = 'tools/graphics/scratch/rosette-traced.json';
const CENTER = 255.5; // measured: bbox 31,32-480,479 on the 512x512 canvas

const ref = loadMask(SRC);
const sym = symmetrizeDihedral(ref, 8, CENTER, CENTER);

const buf = maskToPngBuffer(sym);
const svg = await tracePath(buf, {
	threshold: 128,
	turdSize: 2,
	optCurve: true,
	optTolerance: 0.2,
	alphaMax: 1,
	color: 'black',
	background: 'white',
});
const d = svg.match(/d="([^"]+)"/)[1];

// Verify against the 1px self-shift noise floor established for this asset.
const traced = rasterizePath(d, 512, 512);
const matchIou = iou(ref, traced);
const shifted = (() => {
	const { width, height, grid } = ref;
	const s = new Uint8Array(width * height);
	for (let y = 0; y < height; y++) for (let x = 1; x < width; x++) s[y * width + x] = grid[y * width + x - 1];
	return { width, height, grid: s };
})();
const noiseFloor = iou(ref, shifted);
console.log('traced vs reference IoU:', matchIou.toFixed(4), '  (1px-shift noise floor:', noiseFloor.toFixed(4), ')');
if (matchIou < noiseFloor - 0.02) {
	throw new Error(`Rosette trace IoU ${matchIou.toFixed(4)} is meaningfully below the ${noiseFloor.toFixed(4)} noise floor — inspect scratch/rosette-traced.json before shipping.`);
}

// Normalize: shift so the measured center sits at the origin, so
// gen-ornaments.mjs can place this at any (cx, cy, r) via a simple
// translate+scale transform.
function shiftPath(dStr, dx, dy) {
	const tokens = dStr.match(/[MLC]|-?\d*\.?\d+/g);
	let out = '';
	let cmd = '';
	let coordIndex = 0;
	for (const tok of tokens) {
		if (/[MLC]/.test(tok)) {
			cmd = tok;
			coordIndex = 0;
			out += (out ? ' ' : '') + tok;
			continue;
		}
		const n = parseFloat(tok);
		const isX = coordIndex % 2 === 0;
		const v = isX ? n - dx : n - dy;
		out += ' ' + (Math.round(v * 1000) / 1000);
		coordIndex++;
	}
	return out;
}

const finalD = shiftPath(d, CENTER, CENTER);

// Measure the true outer radius of the symmetrized (post-vote) mask so the
// (cx,cy,r) wrapper in gen-ornaments.mjs scales to a known, exact value.
let maxR = 0;
{
	const { width, height, grid } = sym;
	for (let y = 0; y < height; y++) {
		for (let x = 0; x < width; x++) {
			if (!grid[y * width + x]) continue;
			const r = Math.hypot(x - CENTER, y - CENTER);
			if (r > maxR) maxR = r;
		}
	}
}

writeFileSync(OUT, JSON.stringify({ d: finalD, radius: Math.round(maxR * 100) / 100 }, null, '\t') + '\n');
console.log('rosette outer radius', maxR.toFixed(2));
console.log('wrote', OUT);
