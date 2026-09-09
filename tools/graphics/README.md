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

### After regenerating: some assets need the Elementor pages re-baked too

`mask-image`-consumed assets (`arch-mask.svg`, `fourcentred-*`,
`horseshoe-*`, `mandorla-*`, `girih-lattice-*`, `corner-frame.svg`, …) are
referenced by URL from `components.css` and are read fresh by the browser
on every load — regenerating one takes effect immediately, no further
step needed (past the theme's own `style.css` version bump for cache
busting, already part of every commit here).

**Divider/rosette/medallion assets are different.** `eqc_divider_svg()`
and `eqc_get_svg_asset()` (`inc/template-tags.php`) are called from
`tools/elementor-helpers.php`, which is only ever invoked from
`tools/pages/*.php` — build-time PHP run once via `wp eval-file`, whose
*output HTML string* (not a reference to the file) gets saved into the
page's `_elementor_data`. Regenerating `divider-section.svg` (etc.) only
changes what a *future* page-build would embed; every page's
*already-saved* content keeps showing whatever was embedded the last time
its script ran — confirmed the hard way once, by querying a live page's
`_elementor_data` directly and finding the old star markup still sitting
there, byte-for-byte, well after the source SVG had changed.

So: after regenerating any asset consumed via `eqc_divider_svg()`/
`eqc_get_svg_asset()`, re-run **every** `tools/pages/*.php` script (not
just the page you think is affected — `eqc_section_heading_el()` embeds
`divider('section')` and is used by every page's section headings) to
refresh `_elementor_data`, then `wp elementor flush-css`:

```
for f in tools/pages/*.php; do
  wp --user=1 eval-file "/$f"
done
wp elementor flush-css
```

**`--user=1` is required, not optional.** Without it, the script still
prints `Success: Saved Elementor content for post #N` — but the save
silently doesn't take, and the page keeps its old embedded content. This
was mistaken for success once; only a direct `_elementor_data` re-query
caught it. Verify with the same query rather than trusting the CLI's own
"Success" line alone when it matters:

```
wp post meta get <ID> _elementor_data | grep -o '<some distinctive bit of the new SVG path data>'
```

Per CLAUDE.md, do not edit `_elementor_data` directly (raw SQL/meta
writes) to work around this — re-running the sanctioned build script is
the correct fix, not a shortcut around it.

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

### The hero/photo arch (`ogeeArchPanel`)

The hero/photo arch (`arch-mask.svg`/`arch-outline.svg`/`arch-frame.svg`)
went through **five** rejected attempts before converging — worth
recording precisely, since each rejection pointed at a different,
non-obvious mistake, and the last one is a real "don't do this again":

1. A hand-tuned ogee (`nextTangentArc` solving a shoulder→apex
   transition) matched an earlier, different single-bump reference, but
   not the reference shown next.
2. A `keelArchPanel` built from independent `arcThroughBulge` arcs (tuned
   to a two-cusp reading of a Flaticon-style abstract icon reference) was
   closer to *that* reference, but was rejected — the icon reference
   itself turned out to be the wrong thing to chase.
3. **Bitmap-tracing that same icon reference exactly** (flood-fill +
   mirror-symmetrize + potrace, achieving 0.9966 IoU against it — the
   technique is still in `lib/trace.mjs`, unused by this asset now)
   produced a smooth S-curve shoulder with a small capital-circle detail
   near the jamb top, matching that icon precisely. This one was actually
   correct — but was live with a thick, solid offset gold band (see
   below), which read as "weird."
4. **The mistake**: reading "make it thin and more decent, like [a
   further client mockup]" as license to replace the *shape* too, not
   just the line weight. The client's mockup (`Assests/WhatsApp Image
   2026-09-04 at 4.23.19 PM.jpeg`) has a plainer single-curve silhouette;
   fitting a bezier to *that* (still `ogeeArchPanel`, different `c1`/`c2`)
   produced a real, working arch, but not the one the client actually
   wanted kept — confirmed the hard way when the client said the shape
   from step 3 "was fine," only the line needed thinning. Lesson: a
   request to fix a rendering property (thickness) is not a request to
   re-derive the geometry from a different reference, even when a "looks
   more like this" comparison is offered alongside it — the two are
   independent unless the user says both are wrong.
5. **Recovery**: step 3's own source bitmap (`tools/graphics/reference/arch.png`,
   itself traced from the client's `image1.png`) had already been deleted
   as part of the step-4 detour, and neither file was ever committed —
   gone for good, confirmed via `git log --all --diff-filter=A`. Rather
   than ask the client to resend it, a Playwright screenshot taken
   *earlier in the same session*, before the detour, already showed that
   exact shape rendered live at 1440px. Scanning it for the arch's gold
   color (not a plain background-difference scan — this page has its own
   subtle repeating background texture that a naive scan picks up as
   noise) gave a clean apex→springline profile, least-squares cubic-bezier
   fit the same way as step 4 (RMSE ≈12px on a 314px half-span, ~4% —
   the same order of accuracy every reference fit this way has landed at).
   `ogeeArchPanel`'s current call site in `gen-ornaments.mjs` uses these
   values — the function's own *default* parameters are still step 4's
   mockup fit, documented there for reference, but nothing calls it with
   those defaults now.

A cubic bezier's curve always stays within the convex hull of its own 4
control points, and here all 4 (apex, `c1`, `c2`, springline) lie within
the panel's own half-span×rise box by construction — so, same as the
arc-based panels above, the bbox is exact with no overshoot possible,
just via a different argument than `circleExtent`.

**Thin, open-bottomed outline, not a thick closed band:** `ogeeArchPanel`'s
`d` deliberately has no trailing `Z`. Fill (`arch-mask.svg`, the photo
clip) treats an open subpath as closed anyway, so that asset is unaffected
— but a stroked render of the exact same `d` (`arch-outline.svg`) then
does NOT draw a line across the base, reading as an open frame instead of
a capped box. `.eqc-arch-media--masked::before` uses that outline at
`inset:0` (not the old `inset:-3%` enlarged-solid-mask trick, which read
as a thick offset band) — unlike `--fourcentred`/`--horseshoe`, which
still reuse their `*-mask.svg` at `inset:-3%` per the distortion bug
documented below. `arch-outline.svg` is stroked directly on
`arch-mask.svg`'s own *unpadded* bbox (not `wrapArch`'s padded box), so
it shares the identical coordinate frame as the mask and can't hit that
same padding/inset mismatch.

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

Eight original `*-filled` UI variants supplement the outline symbols where
the reference explicitly shows solid silhouettes. They are authored in the
same generator on its 24px grid; no additional library is installed. See
`QA/ASSET-SOURCES.md` and `DESIGN.md` §11 for role selection and provenance.

`build-icon-sprite.mjs` maps every existing `eqc-icon-*` id to a
[Lucide](https://lucide.dev) (ISC) icon, except the 5 brand marks
(whatsapp/facebook/twitter/instagram/youtube), which map to
[Simple Icons](https://github.com/simple-icons/simple-icons) (CC0) and
ship **filled** as their real logos — a deliberate exception to
DESIGN.md §11's one-outline-family rule, since outline-tracing a brand
mark (the previous WhatsApp glyph) is exactly what read as broken.

The reference uses Twitter's bird mark. Its CC0 Simple Icons9.21.0 source is vendored in twitter-reference.svg; the generator reads it alongside the installed brand icons.

### Where each symbol's geometry comes from

`build-icon-sprite.mjs` now draws from four sources, in this order:

0. **`SUPPLIED`** — hand-supplied vector artwork in `source-icons/`, used for
   six icons where the supplied drawing is better than anything worth drawing
   from primitives (`graduation-cap-filled`, `graduate`, `people-pair`,
   `rehal-quran`, `presenter`, `target-arrow`). It is applied last and
   *replaces* any same-id symbol built earlier, so the artwork wins rather
   than colliding. See `source-icons/README.md` for what was normalised and
   why. Everything below still applies to the other 51.

1. **`LUCIDE_MAP`** — icons where Lucide's drawing was compared with the
   client's screenshot and matched. An icon leaves this map only when that
   comparison failed.
2. **`ORIGINAL_OUTLINE`** — project-owned outline geometry for icons whose
   Lucide namesake is a different drawing (Lucide's calendar has no date dots,
   its TrendingUp has no bars, its Headset no boom mic), plus icons no library
   has at all (`rehal-quran`, `presenter`, `shield-halved`). Same 24px grid and
   the same 2.1 stroke, so the family still reads as one.
3. **`FILLED_UI`** — project-owned solid geometry for the roles the design
   draws filled.

Shared shapes live in `lib/icon-shapes.mjs`, not copied between icons: one
`bust()` silhouette serves `person-filled`, `users`, `people-pair`,
`presenter` and `graduate`; one `SHIELD` serves `shield-filled`,
`shield-halved` and `shield-star`; one `mortarboard()` serves both graduation
caps and the graduate; one `star5()` serves `star-filled`, `shield-star`,
`certificate-filled` and `rosette-star`; one `bookOpen()` serves `book-open`,
`book-open-filled` and `rehal-quran`.

**Flat fills inside an outline symbol must use `stroke-width="0"`, not
`stroke="none"`.** `none` is the SVG default for `stroke`, so svgo's
`removeUnknownsAndDefaults` strips it while the fragment is being optimised
outside its symbol — and the symbol's own 2.1px stroke then lands on details
meant to be flat, which is what turned the calendar's date dots into blobs.

### Checking a symbol against the client's drawing

`reference/icons/` holds all 98 icons, brand marks and ornaments cut out of
the client's design screenshots at native resolution, with a labelled contact
sheet and a manifest giving each one's exact source rectangle. Reference only
— nothing there ships. Its README also lists the eight roles the design draws
that the 43-symbol sprite has no equivalent for (`clipboard-check`,
`target-arrow`, `rehal-quran` and five variants of existing symbols), which is
the list to work from if any of those sections is built out further.
