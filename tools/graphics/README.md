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
- **Pointed arch**, **horseshoe arch** and **mandorla** (pointed-oval
  frame) are direct closed-form circle constructions with citations in
  `lib/arches.mjs` itself (Wikipedia's pointed-arch and horseshoe-arch
  pages — the horseshoe's "centre raised R/3 above the springline" is the
  documented classical proportion, not tuned).
- **Keel/Mughal cusped arch** and **multifoil arch** share one
  "scalloped support line" construction: semicircles drawn on chords of
  the same straight base→apex line. Two adjacent semicircles on collinear
  chords are automatically tangent at their shared endpoint (a
  semicircle's tangent at its diameter endpoint is always perpendicular
  to that diameter) — no solver needed, and no kink possible there either.
  The keel arch's cusp *count and relative sizes* (`breakpoints`, e.g.
  `[0.52, 0.8]` for one large lower cusp and one smaller upper cusp) are a
  chosen design parameter, matched against the client's own Mughal
  keel-arch reference image by generating and rendering — that part is
  honestly tuned, the same way real Islamic pattern books specify cusp
  proportions as a design rule rather than deriving them from nothing.
  Multifoil's cusps bulge inward (Gothic tracery); the keel arch's bulge
  outward (`outward: true` flips one sweep-flag) — verified by rendering,
  not assumed, since SVG's arc sweep-flag doesn't behave symmetrically in
  an intuitive way without checking.
- **Exact bounding boxes, not fixed canvases.** Every arch function
  returns `{ d, bbox }` with the bbox computed from the actual circle
  geometry (`circleExtent`/`semicircleExtent`: an arc's true extent is its
  two endpoints plus any 0°/90°/180°/270° axis crossing it sweeps
  through), not guessed padding. This matters concretely: the keel arch's
  outward cusps genuinely overshoot its nominal width by ~5% per side at
  production scale (a real feature of the "shoulder" look, confirmed by
  the exact math) — wrapping with a naive fixed `w×h` viewBox would clip
  them, reintroducing the exact defect this module exists to fix.
  `wrapArch()`/`svgFromBbox()` size the final `<svg>`'s viewBox from that
  true bbox, so a consumer's CSS `aspect-ratio` must match the asset's own
  bbox ratio (documented per class in `components.css`) rather than a
  round number like 4:5.

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
