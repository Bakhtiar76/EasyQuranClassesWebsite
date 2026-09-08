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
vertices, `{n/step}` star polygons, spiked rosettes). Everything in
`gen-ornaments.mjs` is built from real closed-form geometry, **except**
the arch/finial families below, which don't reduce to simple polygon
math and were tuned by rendering and comparing against reference images:

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
- **Ogee arch** (`ogeeArchPath`): a single S-curve cubic per side — an
  S-curve bezier is exactly "control point 1 on one side of the
  start-to-end chord, control point 2 on the other side" (control 1
  outward past the vertical base = the convex shoulder; control 2 inward
  toward the centerline = the concave finish into the point).
- **Multifoil arch** (`multifoilArchPath`): the documented
  intersecting-semicircles construction — divide each of the pointed
  arch's two straight support lines into N equal sections and draw a
  semicircle on each, bulging into the arch.
- **Quatrefoil**: 4 circles of radius `r`, centered at distance `r` from
  the shared middle along each cardinal direction (so each one passes
  exactly through that middle point); the union's outline is the 4 "far"
  semicircle arcs between the cusp points where adjacent circles cross.
- **Mihrab finial**: a dedicated symmetric petal curve (two mirrored
  cubics from center to a pointed tip), rotated 4× — not a reuse of
  `ogeeArchPath`, whose control-point formula assumes a small dome atop a
  tall rectangle and distorts outside that ratio.

None of these five have a simple closed form the way a regular polygon
does; their parameters (springline height, bulge/tuck ratios, lobe count)
were chosen by generating, rendering, and visually comparing against the
reference screenshots in `Assests/` and the Flaticon-style Islamic arch
icons shared during this build — say so plainly rather than presenting
tuned constants as derived formulas.

**Mask vs. inline color:** `rosette`, `lattice-corner`, `corner-frame`,
`arch-mask`/`arch-outline`/`arch-frame`, `multifoil-*`, `quatrefoil-*`,
`mihrab-finial-*`, `hex-tessellation` and `girih-lattice-*` are consumed
via CSS `mask-image` (only their alpha coverage matters — recoloring means
changing the consumer's `background-color`, not the SVG). The 5
`divider-*` assets are the exception: they're inlined directly as HTML so
`currentColor` follows the surrounding text/gold color, since dividers sit
inline in text flow rather than as a full-bleed background layer.

## Icon sprite

`build-icon-sprite.mjs` maps every existing `eqc-icon-*` id to a
[Lucide](https://lucide.dev) (ISC) icon, except the 5 brand marks
(whatsapp/facebook/twitter/instagram/youtube), which map to
[Simple Icons](https://github.com/simple-icons/simple-icons) (CC0) and
ship **filled** as their real logos — a deliberate exception to
DESIGN.md §11's one-outline-family rule, since outline-tracing a brand
mark (the previous WhatsApp glyph) is exactly what read as broken.
