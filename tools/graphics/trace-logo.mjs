// Trace the prepped bilevel logo-mark bitmap into a clean vector path.
//
// Source: Assests/Logo/Logo2.jpeg (client-approved forest-green mark, see
// DESIGN.md §5) — a genuinely flat, single-color mark with clean
// anti-aliasing, which is exactly the case potrace's bezier fitting was
// built for. No manual "layer splitting" is done: the traced curves are
// already smooth (optCurve), so hand-redrawing them would only reintroduce
// error versus the source art.
//
// Usage: node trace-logo.mjs
import { trace, Potrace } from 'potrace';
import { optimize } from 'svgo';
import { writeFileSync } from 'node:fs';

const SRC = 'tools/graphics/scratch/logo-mark-prepped.png';
const OUT = 'tools/graphics/scratch/logo-mark-traced.svg';

const params = {
	threshold: 128,
	turdSize: 2, // drops JPEG speckle, keeps real strokes (thinnest real stroke — the sound-wave bars — is ~4px, well above this floor)
	optCurve: true,
	optTolerance: 0.4, // slightly looser than the potrace default (0.2) — smooths the JPEG's own anti-alias jitter into clean curves without losing the mark's real corners
	alphaMax: 1,
	color: 'currentColor',
	background: 'transparent',
};

trace(SRC, params, (err, svg) => {
	if (err) throw err;
	const { data } = optimize(svg, {
		multipass: true,
		plugins: ['preset-default'],
	});
	writeFileSync(OUT, data);
	console.log('traced ->', OUT, `(${data.length} bytes)`);
});
