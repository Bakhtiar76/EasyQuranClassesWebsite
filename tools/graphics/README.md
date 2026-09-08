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
node trace-rosette.mjs     # traces reference/rosette.png -> scratch/rosette-traced.json
node gen-ornaments.mjs     # generates all ornament/arch/divider assets (reads rosette-traced.json)
node build-icon-sprite.mjs # regenerates inc/icon-sprite.php from Lucide + Simple Icons
```

Re-run any script after editing it; each is idempotent (overwrites its
own output). `scratch/` holds build intermediates and is gitignored —
`gen-ornaments.mjs` depends on `trace-rosette.mjs` having been run at
least once in the current checkout (same relationship `build-logo.mjs`
has to `trace-logo.mjs`).

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
- **Multifoil arch** uses the "scalloped support line" construction:
  semicircles drawn on chords of the same straight base→apex line. Two
  adjacent semicircles on collinear chords are automatically tangent at
  their shared endpoint (a semicircle's tangent at its diameter endpoint
  is always perpendicular to that diameter) — no solver needed, and no
  kink possible there either.
- **Exact bounding boxes, not fixed canvases.** Every parametric arch
  function returns `{ d, bbox }` with the bbox computed from the actual
  circle geometry (`circleExtent`/`semicircleExtent`: an arc's true extent
  is its two endpoints plus any 0°/90°/180°/270° axis crossing it sweeps
  through), not guessed padding. The four-centred and horseshoe arches do
  genuinely overshoot their nominal box slightly — confirmed by the exact
  math, not assumed either way. `wrapArch()`/`svgFromBbox()` size the
  final `<svg>`'s viewBox from the true bbox, so a consumer's CSS
  `aspect-ratio` must match the asset's own bbox ratio (documented per
  class in `components.css`).

### The hero/photo arch: measured from the client's own mockup (`ogeeArchPanel`)

The hero/photo arch (`arch-mask.svg`/`arch-outline.svg`/`arch-frame.svg`,
`ogeeArchPanel` in `lib/arches.mjs`) went through **four** rejected
attempts before converging — worth recording precisely, since each
rejection pointed at a different, non-obvious mistake:

1. A hand-tuned ogee (`nextTangentArc` solving a shoulder→apex
   transition) matched an earlier, different single-bump reference, but
   not the reference shown next.
2. A `keelArchPanel` built from independent `arcThroughBulge` arcs (tuned
   to a two-cusp reading of a Flaticon-style abstract icon reference) was
   closer to *that* reference, but was rejected — the icon reference
   itself turned out to be the wrong thing to chase.
3. **Bitmap-tracing that same icon reference exactly** (flood-fill +
   mirror-symmetrize + potrace, achieving 0.9966 IoU against it — the
   technique is still in `lib/trace.mjs`, unused by this asset now) was
   rejected too, and correctly so: it was a faithful reproduction of an
   abstract vector icon, not of the client's actual approved page design.
   DESIGN.md is explicit that client screenshots outrank this kind of
   secondary reference, and the live result — a fairly ornate S-curve with
   a small capital-circle detail, painted with a thick solid offset gold
   band — read as "weird," not decent, next to the client's own mockup.
4. **What actually worked**: measure the arch drawn in the client's own
   approved page mockup (`Assests/WhatsApp Image 2026-09-04 at 4.23.19
   PM.jpeg`) and fit a curve to *that*. Scanning the mockup for the
   photo-vs-page-background boundary (robust against the JPEG noise a
   gold-color-specific threshold hit once the arch line crossed busy photo
   content) gave a clean half-width-vs-height profile from apex to
   springline. A two-centred circular arc through the same apex/springline
   endpoints — the obvious first guess — missed that profile by 4-5x the
   error a direct least-squares cubic-bezier fit achieved (RMSE ≈10px on a
   271px half-span), confirming the real curve is a plain designed bezier,
   not a circle. `ogeeArchPanel`'s default `c1`/`c2` are that fitted
   bezier's control points, and `riseFrac` is the measured rise÷half-span
   ratio — none of the three numbers are guessed.

A cubic bezier's curve always stays within the convex hull of its own 4
control points, and here all 4 (apex, `c1`, `c2`, springline) lie within
the panel's own half-span×rise box by construction — so, same as the
arc-based panels above, the bbox is exact with no overshoot possible,
just via a different argument than `circleExtent`.

**Thin outline, not a thick band:** the mockup's own gold line is a thin
hairline sitting right at the photo's edge, not an offset band. So unlike
`--fourcentred`/`--horseshoe` (which still reuse their `*-mask.svg` at
`inset:-3%`, per the distortion bug documented below), `--masked`'s
`::before` uses `arch-outline.svg` at `inset:0` — a thin stroke traced on
`arch-mask.svg`'s own *unpadded* bbox (not `wrapArch`'s padded box), so it
shares the identical coordinate frame and can't hit that same
padding/inset mismatch.

### The rosette medallion: also traced (`trace-rosette.mjs`)

`rosette.svg`, `divider-medallion.svg` and the centre medallion in
`divider-section.svg`/`divider-card.svg` are the client's 8-fold girih
star (`reference/rosette.png` — a small 8-point star void at the centre,
surrounded by 8 interlacing lens/kite petals), replacing the previous
plain `{8/3}` star (`starPolygonPath`). Same reasoning as the arch: this
specific woven interlace is real geometry to reproduce exactly, not
something to re-derive by eye.

Unlike the arch, this trace does **not** run `fillFromBorder` — the
centre star and the gaps between petals are meant to stay holes, and
`potrace`'s own even-odd fill-rule output already gets that right when
traced directly off the ink pixels (confirmed: 10 subpaths — 1 outer
boundary, 8 congruent petal-hole loops at 45° apart, 1 centre-star void).
Symmetrization uses `lib/trace.mjs`'s `symmetrizeDihedral` (8-fold
rotation + mirror) — by **majority vote** across all 16 symmetric copies,
not union: this is a thin (~4-5px) stroke, and unioning 16 rotated copies
of a thin stroke systematically grows it every single time (each
rotation's nearest-pixel rounding can only add area, never remove it) —
confirmed by measurement (raw-vs-union IoU 0.93, raw-vs-majority-vote IoU
0.95, and the union version visibly fatter on inspection). The resulting
traced path is embedded directly (`tracedRosette()` in
`gen-ornaments.mjs`, reading `scratch/rosette-traced.json`), positioned at
any `(cx, cy, r)` via a plain translate+scale — not re-parametrized,
since the exact bezier curvature is part of what needs reproducing.

A calibration note on the IoU verification metric: for a stroke this
thin, IoU is intrinsically noisy — the reference bitmap compared against
*itself* shifted by a single pixel already drops to ~0.93 IoU, because a
1px edge disagreement on a 4px stroke is a large fraction of the stroke's
own area. So unlike the arch (a big filled shape, where 0.99+ is the
right bar), the rosette trace is verified against that same-image
1px-shift noise floor, not a flat number — and confirmed visually
side-by-side against the reference (`scratch/gallery.html`).

`rosette-12.svg` (a decorative ring at `.eqc-pricing-icon-ring`, no
12-fold reference exists) and `star-8-filled.svg` (the pricing bullet,
rendered too small — ~12px — for the rosette's interior weave to read)
use `girihRosettePath` (`lib/geometry.mjs`) instead: a parametric
tip→shoulder→valley kite construction whose three radius/angle ratios
were measured off the SAME averaged, symmetrized `reference/rosette.png`
landmarks, generalized honestly to other fold counts (12) or filled as an
outer silhouette only (8, for the tiny bullet) rather than guessed from
scratch.

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
