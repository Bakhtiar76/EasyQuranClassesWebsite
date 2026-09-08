# Visual asset generation tooling

Local-only Node scripts that generate the site's SVG asset library
(logo, Islamic ornaments/arches/dividers, icon sprite) into
`wp-content/themes/easy-quran-classes-child/assets/svg/`. Not deployed —
only the generated output ships; this directory stays on `feature/*`
branches per `.claude/rules/git.md`.

```
cd tools/graphics
npm install
node trace-logo.mjs        # traces Assests/Logo/Logo2.jpeg -> scratch/logo-mark-traced.svg
node build-logo.mjs        # composes the 4 logo SVG variants from that trace
node gen-ornaments.mjs     # generates all ornament/arch/divider assets
node build-icon-sprite.mjs # regenerates inc/icon-sprite.php from Lucide + Simple Icons
```

Re-run any script after editing it; each is idempotent (overwrites its
own output). `scratch/` holds build intermediates and is gitignored.

## Logo

Traced from `Assests/Logo/Logo2.jpeg` (the client-approved forest-green
mark, DESIGN.md §5) with `potrace`, tuned for a clean bezier fit rather
than a pixel staircase — the key fix was NOT upscaling the bitmap before
tracing (upscaling-then-re-thresholding baked in a jagged edge that
potrace then faithfully traced as thousands of tiny segments; tracing
directly at the source crop's native resolution gave ~100 clean cubic
curves instead). The wordmark/tagline are real `<text>` in the site's own
loaded webfonts (DM Serif Display / Manrope), not traced bitmap type.

Every logo variant uses `currentColor` — there is no separate "mono/dark"
file; the consumer sets CSS `color`.

## Ornament geometry

`lib/geometry.mjs` has the reusable exact-trig helpers (regular polygon
vertices, `{n/step}` star polygons, spiked rosettes). `lib/arches.mjs` has
the Islamic arch family (see below). Everything in `gen-ornaments.mjs` is
built from real closed-form geometry:

- **Girih lattice** (`girihTile`, called once per density): the classic Archimedean
  4.8.8 "octagon + square" tiling — regular octagons on a square grid
  sharing edges with their 4 orthogonal neighbours, a 45°-rotated square
  filling each 4-way diagonal gap. Vertex angle sum 135+135+90=360°, so it
  tiles the plane exactly by construction (verified by rendering a 3x3
  repeat as an actual CSS `background-image` — see `scratch/gallery.html`
  during development). An `{8/3}` star is inscribed in each octagon.
- **Hex tessellation**: standard pointy-top hex-grid spacing (same-row
  centers `sqrt(3)*s` apart, rows `1.5*s` apart, alternating rows offset
  by half that width — a 2-row vertical repeat period).
- **Quatrefoil**: 4 circles of radius `r`, centered at distance `r` from
  the shared middle along each cardinal direction (so each one passes
  exactly through that middle point); the union's outline is the 4 "far"
  semicircle arcs between the cusp points where adjacent circles cross.
- **Mihrab finial**: a dedicated symmetric petal curve (two mirrored
  cubics from center to a pointed tip), rotated 4×.

### Arch family (`lib/arches.mjs`)

The original ogee arch (`ogeeArchPath`, a single hand-tuned S-curve
bezier per side) had two real defects, both confirmed against the
generated coordinates rather than by eye: its control points fell outside
its own `viewBox` (the shoulders were silently clipped — the "tent"
silhouette a client screenshot flagged), and its curve met the vertical
jamb without matching tangent (a visible kink). It's been replaced
entirely by a shared module built on one primitive:

- **Tangent-arc solver** (`nextTangentArc`): given a circle and a point on
  it, solves for the unique second circle that is tangent there *and*
  passes through a target point. The algebra is linear (no quadratic
  branch to pick), and the tangency is structural — a kink at that
  junction is no longer possible by construction. Used by the
  **four-centred (Persian/Timurid) arch** for the haunch→crown transition.
  Tried and abandoned for the keel arch's shoulder→apex transition (see
  below) — forcing exact tangency into a sharp point is a genuinely
  different, ill-conditioned problem from this solver's designed use case.
- **Pointed arch**, **horseshoe arch** and **mandorla** (pointed-oval
  frame) are direct closed-form circle constructions with citations in
  `lib/arches.mjs` itself (Wikipedia's pointed-arch and horseshoe-arch
  pages — the horseshoe's "centre raised R/3 above the springline" is the
  documented classical proportion, not tuned).
- **Multifoil arch** uses the "scalloped support line" construction:
  semicircles drawn on chords of the same straight base→apex line. Two
  adjacent semicircles on collinear chords are automatically tangent at
  their shared endpoint (a semicircle's tangent at its diameter endpoint
  is always perpendicular to that diameter) — no solver needed, and no
  kink possible there either.
- **Keel/Mughal arch** (the hero/photo arch — `keelArchPanel`): a
  vertical jamb, a larger lower bump, a smaller upper bump, then a sharp
  point, matching the client's reference icon exactly. Went through three
  different constructions before this one converged — worth recording
  precisely because the failure modes aren't obvious in advance:
  1. A single-bump ogee (`nextTangentArc` solving the shoulder→apex
     transition) matched a *different*, single-bump reference shown
     earlier, but not this multi-cusp one, and separately the solver
     produced a self-intersecting loop when pushed toward a sharper apex
     — see the tangent-arc solver note below.
  2. Reusing the multifoil's `scallopSide` (semicircles on chords) with
     `outward: true` produced bumps that were uniformly too big and too
     round: a semicircle's bulge is *forced* to exactly half its own
     chord length, so there's no way to make one bump smaller/flatter
     than another independent of moving it — the wrong degree of freedom
     for a reference with two visibly different-sized cusps.
  3. **What actually worked**: `arcChain`, a sequence of 4 independent
     `arcThroughBulge` arcs per side (3 interior waypoints along the
     base→apex line, picked by fraction; each of the 4 segments its own
     bulge). This decouples "where is each cusp" (waypoint fractions)
     from "how round is each cusp" (bulge), which is exactly the control
     the reference needs. The visual convex/concave alternation (bump,
     valley, bump, finish to a point) falls out of each segment's own
     local direction along the winding path — every `bulge` value here
     is positive; nothing needs an alternating sign, which is easy to
     assume wrongly.
  A load-bearing, genuinely counter-intuitive fact about `arcThroughBulge`
  surfaced tuning this: bulge `0` gives a full **semicircle** (radius =
  half the chord — the roundest possible), and *increasing* the
  magnitude makes the arc **flatter**, not deeper. Reads backwards in
  plain English ("more bulge" sounds like "more curve"), and several
  early tuning passes at "small bulge for a subtler bump" produced
  near-identical full-round bumps until this was worked out properly —
  see the function's own doc comment before changing any bulge constant.
- **Exact bounding boxes, not fixed canvases.** Every arch function
  returns `{ d, bbox }` with the bbox computed from the actual circle
  geometry (`circleExtent`/`semicircleExtent`: an arc's true extent is its
  two endpoints plus any 0°/90°/180°/270° axis crossing it sweeps
  through), not guessed padding. The keel arch's bbox comes out an exact
  400:500 at production scale (no overshoot), but the four-centred and
  horseshoe arches do genuinely overshoot slightly — confirmed by the
  exact math, not assumed either way. `wrapArch()`/`svgFromBbox()` size
  the final `<svg>`'s viewBox from the true bbox, so a consumer's CSS
  `aspect-ratio` must match the asset's own bbox ratio (documented per
  class in `components.css`).

**CSS masking gotcha worth remembering:** `mask-image` on a parent clips
its *entire* rendered subtree, including a differently-sized or
differently-positioned pseudo-element inside it. An outline meant to
trace just *outside* a masked photo cannot live as a sibling pseudo-element
on the same masked container — it gets clipped back down to the smaller
shape, producing a confusing overlapping-cusps artifact instead of a
clean frame. Fix: put the mask on the `<img>` itself and leave the
container (and its `::after` outline) unmasked. See `.eqc-arch-media--masked`
and `.eqc-card--teacher .eqc-teacher-photo-widget` in `components.css`.

**Mask vs. inline color:** `rosette`, `lattice-corner`, `corner-frame`,
`arch-mask`/`arch-outline`/`arch-frame`, `fourcentred-*`, `horseshoe-*`,
`mandorla-*`, `multifoil-*`, `quatrefoil-*`, `mihrab-finial-*`,
`hex-tessellation` and `girih-lattice-*` are consumed via CSS `mask-image`
(only their alpha coverage matters — recoloring means changing the
consumer's `background-color`, not the SVG). The 5 `divider-*` assets are
the exception: they're inlined directly as HTML so `currentColor` follows
the surrounding text/gold color, since dividers sit inline in text flow
rather than as a full-bleed background layer.

### Corner ornament

`lattice-corner.svg` was previously a girih-lattice field simply cropped
to a square canvas, faded by an opacity formula that never actually
reached zero at its own inner edges (~0.35 opacity floor) — the
straight-edged "pasted wallpaper" patches a client screenshot circled.
It's now a genuine arabesque bracket: concentric quarter-arcs anchored at
the corner (reusing the same construction as `corner-frame.svg`), a
rosette and finial as the ornament's own deliberate silhouette, and a
girih fill *clipped* to a curved annular wedge (`<clipPath>`, bounded by
real arcs on both its inner and outer edge — never a rectangular crop)
under a `<radialGradient>` mask that guarantees the alpha reaches true
zero well inside the canvas. Two independent fixes for one bug: geometry
that can't produce a straight edge, and a fade that can't fail to
converge.

## Icon sprite

`build-icon-sprite.mjs` maps every existing `eqc-icon-*` id to a
[Lucide](https://lucide.dev) (ISC) icon, except the 5 brand marks
(whatsapp/facebook/twitter/instagram/youtube), which map to
[Simple Icons](https://github.com/simple-icons/simple-icons) (CC0) and
ship **filled** as their real logos — a deliberate exception to
DESIGN.md §11's one-outline-family rule, since outline-tracing a brand
mark (the previous WhatsApp glyph) is exactly what read as broken.
