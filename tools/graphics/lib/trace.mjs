// Shared bitmap-tracing helpers for deriving exact vector shapes from
// client-supplied reference PNGs (tools/graphics/reference/*.png), used by
// trace-rosette.mjs. Kept separate from lib/geometry.mjs (closed-form trig
// for shapes with no external reference) and lib/arches.mjs (parametric
// arch families) — this module's job is purely "bitmap in, exact-enough
// vector data out."
import { PNG } from 'pngjs';
import { readFileSync } from 'node:fs';
import { trace as potraceTrace } from 'potrace';

/** Decode a PNG into { width, height, dark(x,y) }, dark = alpha>128 and average RGB<128. */
export function loadMask(path) {
	const png = PNG.sync.read(readFileSync(path));
	const { width, height, data } = png;
	const isDark = (x, y) => {
		if (x < 0 || y < 0 || x >= width || y >= height) return false;
		const i = (y * width + x) * 4;
		return data[i + 3] > 128 && (data[i] + data[i + 1] + data[i + 2]) / 3 < 128;
	};
	const grid = new Uint8Array(width * height);
	for (let y = 0; y < height; y++) for (let x = 0; x < width; x++) grid[y * width + x] = isDark(x, y) ? 1 : 0;
	return { width, height, grid };
}

/** Write a mask to a throwaway PNG potrace can read back in (it needs a file path, not a raw buffer of this shape). */
export function maskToPngBuffer(mask) {
	const { width, height, grid } = mask;
	const png = new PNG({ width, height });
	for (let i = 0; i < grid.length; i++) {
		const v = grid[i] === 1 ? 0 : 255;
		png.data[i * 4] = v; png.data[i * 4 + 1] = v; png.data[i * 4 + 2] = v; png.data[i * 4 + 3] = 255;
	}
	return PNG.sync.write(png);
}

/** Promisified potrace trace of a PNG buffer or file path, returning the raw <svg> string. */
export function tracePath(source, opts = {}) {
	return new Promise((resolve, reject) => {
		potraceTrace(source, opts, (err, svg) => (err ? reject(err) : resolve(svg)));
	});
}

/**
 * Rasterize an SVG path `d` (M/L/C only, absolute coordinates — exactly
 * what potrace emits) into a boolean grid, for objective IoU comparison
 * against a reference bitmap without needing a browser. Cubic beziers are
 * flattened to short line segments, then filled by the standard even-odd
 * scanline rule (matching potrace's own `fill-rule="evenodd"` output, which
 * is how a traced shape's interior holes stay holes).
 */
export function rasterizePath(d, width, height) {
	const tokens = d.match(/[MLC]|-?\d*\.?\d+/g) || [];
	const subpaths = [];
	let current = null;
	let cmd = '';
	let i = 0;
	let cx = 0, cy = 0;
	const readPair = () => {
		const x = parseFloat(tokens[i++]);
		const y = parseFloat(tokens[i++]);
		return [x, y];
	};
	while (i < tokens.length) {
		const tok = tokens[i];
		if (tok === 'M') { i++; cmd = 'M'; const [x, y] = readPair(); cx = x; cy = y; current = [[x, y]]; subpaths.push(current); continue; }
		if (tok === 'L') { i++; cmd = 'L'; }
		if (tok === 'C') { i++; cmd = 'C'; }
		if (cmd === 'L') {
			const [x, y] = readPair();
			current.push([x, y]);
			cx = x; cy = y;
		} else if (cmd === 'C') {
			const [x1, y1] = readPair(), [x2, y2] = readPair(), [x, y] = readPair();
			const steps = 24;
			for (let s = 1; s <= steps; s++) {
				const t = s / steps, mt = 1 - t;
				const bx = mt * mt * mt * cx + 3 * mt * mt * t * x1 + 3 * mt * t * t * x2 + t * t * t * x;
				const by = mt * mt * mt * cy + 3 * mt * mt * t * y1 + 3 * mt * t * t * y2 + t * t * t * y;
				current.push([bx, by]);
			}
			cx = x; cy = y;
		} else {
			// Bare coordinate pair after M/L without a repeated command letter
			// (implicit lineto continuation, valid SVG path syntax).
			const [x, y] = readPair();
			current.push([x, y]);
			cx = x; cy = y;
		}
	}
	const grid = new Uint8Array(width * height);
	for (let y = 0; y < height; y++) {
		const yc = y + 0.5;
		const xs = [];
		for (const poly of subpaths) {
			for (let k = 0; k < poly.length; k++) {
				const [x1, y1] = poly[k];
				const [x2, y2] = poly[(k + 1) % poly.length];
				if ((y1 <= yc && y2 > yc) || (y2 <= yc && y1 > yc)) {
					xs.push(x1 + ((yc - y1) / (y2 - y1)) * (x2 - x1));
				}
			}
		}
		xs.sort((a, b) => a - b);
		for (let k = 0; k + 1 < xs.length; k += 2) {
			const from = Math.max(0, Math.ceil(xs[k] - 0.5));
			const to = Math.min(width - 1, Math.floor(xs[k + 1] - 0.5));
			for (let x = from; x <= to; x++) grid[y * width + x] = 1;
		}
	}
	return { width, height, grid };
}

/**
 * Force exact dihedral (rotation + mirror) symmetry on a mask by MAJORITY
 * VOTE across all `2*folds` rotated/mirrored copies about the given
 * center. Deliberately a vote, not a union: an OR/union over
 * imperfectly-aligned rotated copies
 * of a thin stroke systematically *grows* it every one of the `folds`
 * times it's resampled (each rotation's rounding-to-nearest-pixel can
 * only add area, never remove it), visibly fattening a several-px stroke.
 * Averaging with a 50% threshold cancels that bias out — a pixel only
 * flips dark if a genuine majority of its symmetric copies agree.
 */
export function symmetrizeDihedral(mask, folds, cx, cy) {
	const { width, height, grid } = mask;
	const out = new Uint8Array(width * height);
	const angleStep = (Math.PI * 2) / folds;
	const sample = (x, y) => {
		const xi = Math.round(x), yi = Math.round(y);
		return xi >= 0 && yi >= 0 && xi < width && yi < height ? grid[yi * width + xi] : 0;
	};
	for (let y = 0; y < height; y++) {
		for (let x = 0; x < width; x++) {
			const dx = x - cx, dy = y - cy;
			let votes = 0;
			for (let k = 0; k < folds; k++) {
				const a = k * angleStep;
				const cosA = Math.cos(a), sinA = Math.sin(a);
				// Rotate this output pixel BACKWARD by -a to find the source
				// pixel in the unrotated mask (inverse mapping avoids gaps).
				const rx = cx + dx * cosA + dy * sinA;
				const ry = cy - dx * sinA + dy * cosA;
				votes += sample(rx, ry);
				// Inverse of "mirror about the x-axis, then rotate by a":
				// rotate by -a first (same rx,ry basis), then mirror.
				const mrx = cx + dx * cosA + dy * sinA;
				const mry = cy + dx * sinA - dy * cosA;
				votes += sample(mrx, mry);
			}
			out[y * width + x] = votes >= folds ? 1 : 0; // >= half of the 2*folds samples
		}
	}
	return { width, height, grid: out };
}

/** Intersection-over-union of two same-size boolean grids — the objective match metric against a reference bitmap. */
export function iou(a, b) {
	if (a.width !== b.width || a.height !== b.height) throw new Error('iou: size mismatch');
	let inter = 0, union = 0;
	for (let i = 0; i < a.grid.length; i++) {
		const av = a.grid[i] === 1, bv = b.grid[i] === 1;
		if (av || bv) union++;
		if (av && bv) inter++;
	}
	return union === 0 ? 1 : inter / union;
}
