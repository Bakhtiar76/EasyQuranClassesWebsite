# Home close review

Status: hero + trust strip measured, implemented, iterated and verified.
Sections 3–10 are **not** reviewed — see "Not done" at the end.

> Rows 1–13 below are the review as first written. Where a later iteration
> disproved one, the row carries a **Superseded** note; the iteration log is
> the record of what was actually built.

## Authorship

| Model | Did |
|---|---|
| **Codex** (supervisor / senior dev) | `TASK-DESIGN-PARITY.md`, Phase 0 (environment, `tests/visual/sweep.mjs` extension, `local/iterate.ps1`, all baselines), page 1 *Global chrome*, and the review conventions this document follows. Out of usage limit mid-task. |
| **Claude Code `claude-opus-5`** (2026-09-09) | Split Codex's uncommitted checkpoint into three reviewable commits; this Home review, its measurements, implementation, iterations and verification. Every judgement call Codex might overturn is flagged `[claude-opus-5 — review]`. |

Iteration-log entries below are prefixed with the model that performed them.

## Reference calibration and applicable lessons

Applied `QA/LESSONS.md` 1–10. Native dimensions re-measured this session
(LESSONS #5 — every file differs, never assume 1920):

| File | Native | Governs |
|---|---|---|
| `Home.jpeg` | 1307×967 | header, hero, trust strip |
| `Home2.jpeg` | 1600×896 | about collage, stat row, dual CTA |
| `courses.jpeg` | 1296×974 | course grid |
| `pricing.jpeg` | 1600×906 | plan cards, benefits strip |
| `Teachers.jpeg` | 1599×923 | teacher split layout |
| `Reviews.jpeg` | 1600×862 | testimonials |
| `Blogs.jpeg` | 1600×905 | blog carousel |
| `End.jpeg` | 1489×978 | final CTA, footer |
| `Website Layout.png` | 992×1586 | section order and vertical rhythm only |

**New calibration lesson (this session).** The section JPEGs are crops taken at
*different zoom levels*, so a canvas fraction is only comparable **within one
file**. Measured content-column fractions: `pricing.jpeg` 91.9%, `Teachers.jpeg`
94.4%, `Home.jpeg` 84.4% — these do not disagree about the design, they disagree
about how tightly each export was cropped. Only `Home.jpeg` contains both the
header bar and a content-width element on one canvas, so it is the only file that
can fix the content:chrome ratio. Added to `QA/LESSONS.md` as #11.

Scaling convention follows Codex (`global-chrome.md` line 7): scale horizontal and
vertical geometry together by 1920 / reference width. For `Home.jpeg` that is
**×1.4690**.

## Measured baseline (Playwright, 1920×1080, doc width 1905)

Content column `.eqc-hero-grid` x284.5 w1336 (`--eqc-content-max: 1400px` minus the
32px `--eqc-gutter` each side that `box-sizing: border-box` folds in).
`h1` 66px/66px DM Serif Display, w628, **3 lines**. `.eqc-eyebrow` h37.1.
`.eqc-chip` h44, four chips wrapping to two rows. `.eqc-btn-group` h76.
`.eqc-arch-media` 628×938.9 masked with `arch-mask.svg`.

Reference hero vertical rhythm, `Home.jpeg` native px, column x105–660
(ink extents, so pill/card boxes are taller than the text rows shown):

| Block | native y | h | ×1.469 @1920 |
|---|---|---|---|
| eyebrow text | 205–223 | 19 | 28 |
| H1 line 1 | 271–326 | 56 | 82 |
| H1 line 2 | 336–378 | 43 | 63 |
| gold rule + diamond terminals | 404–411 | 8 | 12 |
| body line 1 | 438–454 | 17 | 25 |
| body line 2 | 465–482 | 18 | 26 |
| feature cards (ink) | 530–558 | 29 | 43 |
| buttons | 605–658 | 54 | 79 |
| avatars / stars / trust line | 691–735 | 45 | 66 |

H1 line-to-line 65 native → **95.5px @1920**; ascender height 43 native → 63 @1920
→ DM Serif Display font-size ≈ **86px @1920** (ascender ≈ 0.73em), against the
build's 66px.

## Findings

| # | Section | Reference shows | Build currently does | Severity | Fix (file + change) | Reuses |
|---|---|---|---|---|---|---|
| 1 | Site-wide content column | Trust panel x109–1212 of 1307 = **84.39%** of canvas, stable across rows 790/860/890. Header bar is 94.3% (Codex row 1), so content = 0.895 × chrome | 1336px at 1920 = 69.6% — content never re-derived when Codex widened the chrome | **P0** | `tokens.css`: `--eqc-content-max` 1400px → **1708px**. **Superseded:** first set to 1684px from the trust panel's 1620px column; the hero's own bounds (H1 left edge x105 to arch outer right x1224 = 1119 of 1307 = 1644px) are the tighter check and the H1's two-line break needs them. Affects every page — recorded in `DESIGN.md` | `.eqc-container`, existing `--eqc-gutter` |
| 2 | Hero arch silhouette | **Keel/Mughal arch**: vertical jamb, **three outward lobes per side** (first read as two), sharp ogee point. Outer contour x687–1224 (w537), apex y≈127, base y≈747 (h620) | Smooth ogee `arch-mask.svg`, no cusps — fitted in an earlier session to `WhatsApp Image … 4.23.19 PM.jpeg`, a reference the client has since replaced | **P1** | `gen-ornaments.mjs`: emit `keel-arch-{mask,outline,frame}.svg`. **Superseded:** `scallopedArchPanel(…, outward=true)` looked like an exact match (its own header comment calls it "the client's keel-arch reference") but does **not** fit: it draws semicircles on chords of the straight base→apex line, so the profile returns to that line at every cusp and each lobe is exactly half its chord. The reference's cusps stay 24.5–26.1px clear of that line and its lobe/chord ratios are 0.265 / 0.40 / 0.31, not 0.50. Added `cuspedArchPanel()` — measured cusp points plus a per-segment sagitta, each segment a circular arc through two known points with a known bulge | `arcCmd`, `wrapArch`, `svgFromBbox`, the module's vector primitives; `keelArchPanel` exported too as the general form |
| 3 | Hero arch frame | **Double** gold contour: outer x687, inner x698.5 → 11.5px gap on a 537px arch = **2.14% of width** | Single 3px stroke | P1 | Emit `keel-arch-frame.svg` via the existing `arch-frame.svg` double-line recipe, inset 2.14% not the current 4.56% | `arch-frame.svg` construction |
| 4 | Hero H1 | Two lines, ≈86px/95px | 66px/66px, wraps to **three** lines — string length driving wrong geometry (§2) | P1 | `tokens.css`: `--eqc-fs-h1` cap to **4.9375rem (79px)**. **Superseded:** 86px came from an assumed 0.73em ascender ratio and was wrong — measuring the reference's own line widths (742px / 823px @1920) against the real rendered strings gives 86 x 823/897 = 79px. Also needed zeroing Elementor's 10px container padding, which was taking 40px off the H1's usable width | existing `--eqc-fs-h1` clamp |
| 5 | Hero rule | Thin gold rule with a **diamond terminal at each end**, x105–246 native (w141 → 207 @1920), y404–411 | Absent | P1 | `10-home.php`: `eqc_divider_svg('rule')`. **Superseded:** `divider-accent.svg` has only a LEFT terminal and the reference has diamonds at **both** ends (verified on a 6x crop). It is also consumed by `eqc_pricing_card()`, so a new `divider-rule.svg` was generated rather than mutating a shared asset before pricing is reviewed | `regularPolygonPath`, `svgWrap`; new `divider-rule.svg` justified above |
| 6 | Hero body copy | "1-to-1 live classes with qualified male & female teachers." / "Flexible timing, personalized learning, and **real** progress." — two explicit lines | "…and **steady** progress.", one flowing paragraph | **P0** (copy, §2) | `10-home.php`: exact string + explicit line break | — |
| 7 | Hero feature row | Four **white rounded cards**, one row, icon left + **two-line** label (`1-to-1` / `Live Classes`). Icons dark, largely filled | `eqc_chip()` pills, one-line labels, bronze icons, wrapping to two rows | P1 | `elementor-helpers.php`: extend `eqc_chip()` with an optional second line rather than adding a second function; `components.css`: card treatment | `eqc_chip`, `.eqc-chip`, sprite icons |
| 8 | Hero eyebrow | Sentence case "Trusted by Families Worldwide", bronze, in a white pill with a **circular cream icon chip** at the left | Uppercase + `letter-spacing: .08em`, bare icon | P2 | `components.css`: `.eqc-eyebrow--hero` modifier (no `text-transform`, circular icon chip) | `.eqc-eyebrow` |
| 9 | Hero trust row | Five overlapping circular avatars (white rings), then a right block: **five gold stars above** `Trusted by 5,000+ Students & Parents` | **Entirely absent** from `10-home.php` | **P0** | `10-home.php` + `components.css`: avatar stack + star row. `5,000+` registered as unverified. **Superseded:** the `star` sprite symbol is an outline glyph carrying `fill="none"`, unreachable from page CSS through `<use>` — added a `star-filled` symbol to `build-icon-sprite.mjs` | `.eqc-avatar-stack` (footer CTA, Codex); new `star-filled` symbol from the same `starPolygonPath` construction as `star-8` |
| 10 | Hero secondary button | White fill, visible dark hairline border, WhatsApp glyph in an outlined circle | Cream fill, no circled glyph | P2 | `components.css`: `.eqc-btn--secondary` on cream sections | `eqc_icon_button` |
| 11 | Hero background | Plain cream with a fine allover texture only | A girih corner watermark top-right the reference does not have | P2 | `10-home.php`: drop `eqc-section--ornamented` from the hero | — |
| 12 | Trust strip copy | `Support 7 Days A Week`, `Students from 20+ Countries`, `Certificates Available`; captions `We're here to help / anytime you need`, `A global community / of Quran learners`, `Recognize your progress / with achievement` | "Support 7 Days a Week", "Students from **Many** Countries" — the specific claim softened, which §2 forbids | **P0** (copy) | `10-home.php`: exact strings. `20+ Countries` registered as unverified | `eqc_trust_tile` |
| 13 | Trust strip anatomy | Two-line title + two-line caption per tile, full-height hairlines between columns, panel overlapping the section boundary below | Single-line titles, no hairlines, panel does not overlap | P2 | `components.css`: hairline dividers + negative bottom overlap | `.eqc-grid--trust` |

Sections 3–10 (about, courses, pricing, teachers, testimonials, blog, final CTA)
are governed by `Home2/courses/pricing/Teachers/Reviews/Blogs/End.jpeg` and were
**not reviewed at all** this session — see "Not done" at the end.

## Ordered fix plan

1. Token layer first, because it moves everything: `--eqc-content-max`,
   `--eqc-fs-h1` (findings 1, 4).
2. Arch family: export `keelArchPanel`, emit mask/outline/frame, wire the hero's
   CSS modifier (findings 2, 3). CSS `mask-image` assets are read fresh by the
   browser — **no page re-bake needed** (§4 gotcha 2 applies only to inline
   `eqc_divider_svg()` snapshots).
3. Hero content: copy fixes, divider, feature cards, eyebrow, trust row,
   background (findings 5–11).
4. Trust strip copy and anatomy (findings 12, 13).
5. Re-measure at 1920 against the reference; iterate.
6. Five-viewport capture, 360–1920 scan, keyboard/contrast/reduced-motion,
   Lighthouse, PHP lint.

## Improvised

- `[claude-opus-5 — review]` Finding 4 raises the H1 cap to 86px @1920 on Codex's
  own scaling convention (×1920/refWidth). If Codex instead reads `Home.jpeg` as a
  native 1307px-viewport render, the correct H1 is ~59px and the current 66px is
  already close — the whole type scale turns on this one convention, so it is
  called out rather than buried. Codex's `global-chrome.md` line 7 and its header
  fit (1400→1805px wide) already commit to the scaling reading; this follows it.
- `[claude-opus-5 — review]` Icons in the reference's feature cards are dark and
  largely filled. `DESIGN.md` §11 forbids mixing icon families and Codex settled on
  one Lucide outline family in global chrome, so the outline sprite is kept and
  only the **colour** matched (dark, not bronze). Consistency outranks per-glyph
  fidelity.
- `[claude-opus-5 — review]` The reference is silent on the feature cards below
  desktop; they wrap to 2×2 then stack, rather than shrinking below a readable
  size or a 44px tap target.

## Iteration log

- **`claude-opus-5` — review.** Measured the reference by pixel rather than by eye:
  built `tools/graphics/scratch/{crop,edges,gold,panel,blocks}.mjs` (gitignored
  inspection aids, never shipped assets). Traced the hero arch's gold contour row
  by row, which is what identified it as a two-bump-per-side keel arch and not the
  build's smooth ogee. Confirmed the trust panel's 84.39% column across three
  independent rows before proposing a site-wide token change. No presentation
  edits in this pass.

- **`claude-opus-5` — iteration 1 (tokens + arch + hero content).** Exported
  `cuspedArchPanel()` in `lib/arches.mjs` and emitted `keel-arch-{mask,outline,
  frame}.svg`; raised `--eqc-content-max` and `--eqc-fs-h1`; rewrote the hero's
  eyebrow, H1, rule, feature row, trust row and the trust-strip copy in
  `10-home.php`; extended `eqc_chip()` for a two-line label. **Fixed:** copy,
  chip cards, avatars, keel silhouette, content width. **Did not fix:** H1 still
  three lines; rule rendered dark not gold; labels ran together; stars hollow.
- **`claude-opus-5` — iteration 2 (diagnosis, not styling).** Those four
  failures were one cause, and it was not CSS: the child theme versions its
  stylesheets by *theme* version, which only changes on release, so
  `components.css` was served from cache while `tokens.css` was not. New custom
  properties were live but the rules consuming them were not — which reads
  exactly like a specificity bug. Confirmed by re-fetching the sheets with a
  cache-busting query (all four rules then applied). Fixed at source:
  `eqc_asset_version()` in `functions.php` falls back to `filemtime()` when
  `wp_get_environment_type()` is `local`, production keeps the theme version.
  Recorded as LESSONS #13.
- **`claude-opus-5` — iteration 3 (H1 size re-derived).** With CSS actually
  applying, the H1 was still three lines at 86px. Measured the reference's own
  line widths by pixel: line 1 = 742px @1920, line 2 = 823px. The same strings
  in DM Serif Display need 746px and 897px at 86px — line 1 matched, line 2 did
  not, so the ascender-derived 86px was wrong. Width-derived size is
  86 x 823/897 = **79px**. Set `--eqc-fs-h1` max to 4.9375rem and gave the hero
  grid the measured 50.5 / 48.2 split. Still three lines: Elementor's 10px
  container padding, nested twice, was taking 40px off the usable width — the
  H1 box was 800px against the 824px the line needs. Zeroed on the hero
  containers. **Result: two lines, matching the reference.** LESSONS #12, #16.
- **`claude-opus-5` — iteration 4 (stars, accessible name, mobile gap).** The
  stars stayed hollow because icons render through `<use>`: page CSS cannot
  reach the symbol's inner paths and Lucide's `star` carries `fill="none"` as a
  presentation attribute. Added a real `star-filled` symbol to
  `build-icon-sprite.mjs`, built from the same `starPolygonPath` construction as
  `star-8` (LESSONS #14). The H1's accessible name read "Learn Quran
  Onlinewith Personal Guidance" — the same class of defect as LESSONS #10 — so a
  real space now precedes each `<br>`; it reads "Learn Quran Online with Personal
  Guidance". The 390px capture showed ~130px of dead space above the buttons:
  `repeat(auto-fit, minmax(min(150px, 100%), 1fr))` settled at two columns while
  its Elementor widget wrapper kept the four-row single-column height. Replaced
  with explicit per-breakpoint column counts (LESSONS #15).

### Evidence

- `QA/after/home/home/` — full-page at 1920 / 1440 / 1024 / 768 / 390, plus
  `report.json`.
- `report.json`: `h1Count` 1, **0** console errors, **0** page errors, **0**
  asset errors (no 404 on the new SVGs), **0** images missing `alt`.
- Intermediate-width scan 380-1900 at 40px steps, 39 widths: no horizontal
  overflow at any width. Re-run after every change, per LESSONS #9.
- Contrast, measured on the rendered page against each element's real computed
  background — all pass WCAG AA, lowest 5.65:1: eyebrow 6.04, H1 11.24, body
  8.57, card line 1 16.33, card line 2 9.47, trust line 8.57, trust tile title
  and caption 5.65, secondary button 12.23.
- Arch fit: `tools/graphics/scratch/fitcheck.mjs` re-samples the generated left
  profile against the reference's measured boundary — RMSE **4.19px**, max
  deviation 11.3px, on a 559px-wide arch (**0.75%** of width).
- Save verified by re-querying `_elementor_data` for distinctive new strings
  rather than trusting the success line (§4 gotcha 1): `eqc-arch-media--keel`,
  `eqc-hero-trust`, `chip-row--cards`, `20+ Countries`, `eyebrow--hero`,
  `112.6` (the second diamond terminal) and 5x `eqc-icon-star-filled` all present.
- PHP lint clean: `10-home.php`, `elementor-helpers.php`, `functions.php`.
- Keyboard/semantics at 1920: both hero controls take a visible 3px focus
  outline, no tap target under 44px, zero decorative SVGs exposed to the
  accessibility tree, hero image carries real alt text. The five stars are
  `aria-hidden` deliberately — no verified aggregate rating exists, so the row
  must not announce one (registered in `QA/PLACEHOLDER-REGISTER.md`).
- Both ornament generators re-run and diffed: output is deterministic, only the
  three intended `keel-arch-*` files, `divider-rule.svg` and the `star-filled`
  sprite symbol changed. No churn in existing assets.

**Not verified: Lighthouse.** The `chrome-devtools` MCP server — the wired path
for Lighthouse in this repo — refuses to start because a browser is still
running against its profile from the earlier Codex session
(`The browser is already running for … chrome-profile`). The desktop and mobile
baselines in `QA/baseline/home/` and `QA/after/global-chrome/home/` are
therefore **not** re-compared. Nothing in this change adds script, blocking
requests or network weight — the only new assets are three inline-referenced
SVG masks and one sprite symbol, and `report.json` shows zero failed requests —
but that is reasoning, not a measurement. Re-run it once the stale browser is
closed.

### Not done — honest scope statement

Only the two sections `Home.jpeg` governs (hero, trust strip) were measured and
built. Home's remaining seven sections — about, courses, pricing, teachers,
testimonials, blog, final CTA — are governed by their own reference images and
have **not** been reviewed to this depth. They inherit the wider content column
and the new H1 scale, so they have changed appearance and should be re-checked
when their own passes run.

Known remaining deltas in the two sections that were done, all P2/P3:

| # | Delta | Why not fixed |
|---|---|---|
| 14 | Buttons ~64px tall against the reference's 79px @1920 | P2 metric; the reference's own button row measures 667px wide against our 800px column, so button sizing wants doing together with the hero's vertical rhythm rather than piecemeal |
| 15 | Hero photo framing differs from the reference (different source image) | The photograph itself, not the mask — the silhouette matches. Logged in `QA/IMAGE-BRIEF.md` |
| 16 | Arch foil depths are a measured fit, not an exact reconstruction | RMSE 4.19px stated above; closing the last hairline needs the full potrace-and-fit pipeline from §5.1 |
| 17 | Trust-strip panel does not overlap the section boundary below | Reference shows a slight overlap; deferred with the section-rhythm pass that owns vertical spacing |

---

## Proportional scale system (`claude-opus-5`, 2026-09-09)

The user reported the hero still deviated in "positioning, sizing, formatting,
layout, icons, backgrounds, BG patterns". A matched-scale overlay
(`tools/graphics/scratch/compare.mjs`, which scales the reference to the build's
width and stacks the two) confirmed it and showed one root cause.

**The reference is a proportional design captured at 1307px wide** — every
dimension in it is a fixed fraction of the viewport. The build's tokens were a
*mix*: the values the previous pass touched (H1, content column, arch, chrome)
had been converted to correct proportional values; everything still on fixed px
had not moved. That is worse than uniformly wrong — a correct 79px H1 above 18px
body text reads as broken in a way neither value does alone.

Measured at 1920 before this pass:

| Element | Reference @1920 | Build | Delta |
|---|---|---|---|
| Body text ink width | 623px | 460px | −26% (font 24.4 vs 18) |
| Header bottom → hero start | ~0 | 119px gap | +119 |
| Eyebrow top | y279 | y454 | +175 |
| Trust panel top | y1121 | y1338 | +217 |
| Background pattern cell | 76×132 | 52×90 fixed | −31%, didn't scale |

At 1307px the build was *worse* than at 1920 (H1 on three lines, content near
full-bleed) because `--eqc-content-max` was a px cap rather than a proportion.

### The unit

```css
--eqc-u: clamp(0.72px, 0.0765vw, 1.607px);   /* 100/1307 */
```

One unit is one pixel on the reference canvas. Any measurement taken off a
reference image is transcribed directly as `calc(<measured px> * var(--eqc-u))`.
At 1307 → 1.0; at 1920 → 1.469; held at 1.607 from ~2100 up; floored at 0.72.
The floor and ceiling are the two places this is deliberately *not* the
reference (§2A): unbounded scaling would give a 2560px display 32px body copy,
and unbounded shrinking would give a phone 7px labels.

Every size token now derives from it — type scale, section spacing, gutters,
grid gaps, radii, control heights, icon sizes, avatar diameter, and the
background pattern's cell. Measured type, in native units: H1 53.7, body-l 16.6,
body 13.6, small 11.6, xsmall 10.3, button 11.2. All derived by **glyph width**
against the reference's own line widths, not assumed font metrics (LESSONS #12).

### Result — landmark deltas at 1920

Expected = reference native × 1.469. Target ±8px.

| Landmark | x | y | w | h |
|---|---|---|---|---|
| content column | +0 | — | +0 | — |
| hero arch | −4 | +1 | +4 | +4 |
| eyebrow pill | +0 | +3 | +7 | +0 |
| gold rule | +0 | — | +0 | — |
| feature card | −4 | −4 | −2 | +0 |
| primary button | **+9** | −4 | −3 | +0 |
| secondary button | +6 | −8 | +5 | +3 |
| avatar | +2 | −8 | +0 | +0 |
| trust panel | +6 | +5 | +0 | +0 |

**Worst 9px, mean 2.9px, 31 of 32 checks inside ±8.** Before this pass the worst
was 144px. `x` is compared against a *centred* column because the reference
screenshot's own margins are asymmetric (105 left / 83 right) — it includes a
scrollbar, so its raw x is an artefact rather than a design intent.

### Other fixes in this pass

- **Trust strip** is now the reference's white rounded panel (1104×178 native)
  with hairline-separated columns, not four bordered cards on a section
  background. Its own `.eqc-trust-panel` class so the about section's stat row,
  which reuses `.eqc-grid--trust`, is untouched.
- **Trust tile line breaks** are carried in the copy, not by a max-width.
  No single width reproduces both "Your child's safety is | our top priority"
  (~105 native) and "Recognize your progress | with achievement" (~150), so
  `eqc_trust_tile()` now treats `|` as an explicit break — the break is content,
  per §2. Backward compatible: callers without a `|` wrap naturally.
- **Icons.** `certificate` was Lucide `Award` (a ribbon); the reference draws a
  document with a seal, so it is now `FileBadge2`. Sprite stroke weight raised
  1.75 → 2.1: the reference's UI icons are markedly heavier and several are
  solid silhouettes, but filling them would put two icon styles on one row,
  which `DESIGN.md` §11 forbids — matching the weight is the closest
  single-family reading.
- **Background pattern** now scales with the design instead of staying 52×90px.

### Mobile — designed, not matched

The reference shows no mobile layout, so this is ours (§2A). Scaling its type
proportionally to 390px gives 9.8px body and 7.4px labels — unreadable, and
below `DESIGN.md` §6's 16px floor. Below 1100px the type therefore stops
scaling down and floors out, while layout keeps scaling. The floors sit *above*
the reference's own small-role sizes, so they are applied in a media query
rather than as `max()` on the token — a `max()` would have changed the 1307px
rendering this pass exists to match.

### Verification

- Landmark table above; `worstDelta` 9, `meanAbsDelta` 2.9, 32 checks.
- Matched-scale overlays at **1920 and 1307**. The 1307 case is the proof the
  system works: before this pass the build was badly wrong there.
- Sweep at 1920/1440/1024/768/390 plus the 380–1900 scan (39 widths): no
  horizontal overflow, 0 console errors, 0 page errors, 0 asset errors, 0
  images missing `alt`, `h1Count` 1.
- **Chrome regression guard passed**: header bar measures 1791×149 at y40,
  identical to Codex's fitted geometry, despite the whole token set being
  re-expressed.
- Contrast all AA, lowest 5.74:1 (tile title/caption on white).
- Focus rings 2/2 on hero controls, no tap target under 44px, 0 decorative SVGs
  exposed to the accessibility tree, H1 accessible name intact.
- PHP lint clean on `10-home.php`, `elementor-helpers.php`, `functions.php`.

### Still not done

Home's remaining seven sections — about, courses, pricing, teachers,
testimonials, blog, final CTA — are **not** reviewed. They inherit the new
scale and have changed appearance; each needs its own measured pass against its
own reference image. The other nine pages likewise shift with the token change
and are not re-reviewed here.

---

## Section 3 — About (`Home2.jpeg`, `claude-opus-5`)

### Calibrating a second reference

`Home2.jpeg` is 1600px wide against `Home.jpeg`'s 1307, so its scale had to be
established before anything could be measured (LESSONS #11). Two independent
anchors agree it is **~1:1 with the reference canvas** — i.e. a wider crop at the
same zoom, not a magnified one:

- its "Call Any Time" button measures 56px tall, and the secondary control is
  56u in the design system (verified in the hero);
- its body copy solves to 16.45px, against body-l's 16.6u — and `DESIGN.md` §6
  assigns Body-L to "hero/**about** intro".

`courses.jpeg` was calibrated the same way and also lands at ~1.0 (3 cards of
313u + 2 gaps of 88u = 1115, against the 1119u content column).

**This mattered.** Measured naively, the two references implied heading sizes of
71u and 61u — a 17% disagreement that looked like measurement error. Once each
was scaled by its own anchor the disagreement held, which is the actual finding:
**About's heading is genuinely larger than the other sections'.** It is a display
size, not an h2. So `--eqc-fs-display-xl` is now 70.5u and `--eqc-fs-h2` 60u
(from 36.8u — every section heading on the site was undersized).

### Findings

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| A1 | Ornament **rail** (rule–rosette–rule) spanning the column | Pill eyebrow "Why Easy Quran Classes" | P0 | `.eqc-about-rail` with `divider-section` |
| A2 | Three-line display heading, **all dark green** | h2 at 36.8u, one line | P0 | `--eqc-fs-display-xl` 70.5u + transcribed breaks |
| A3 | Small centred rule–quatrefoil–rule under the heading | Absent | P1 | `.eqc-about-rule` with `divider-card` |
| A4 | Body copy ending "Nobody else is in the room. Nobody is watching you make mistakes." | Shorter paraphrase, em dash replaced | P0 (§2) | transcribed verbatim |
| A5 | Three stats, **label above value**, rosette-framed icon discs, hairlines | Countup numbers above labels, no discs | P1 | `eqc_about_stat()` |
| A6 | **Two** buttons — dark-green "More About Us" (icon in a gold disc) + outlined "Call Any Time" | One button | P0 | `.eqc-btn--green`, `.eqc-btn--icon-disc` |
| A7 | Collage of **three** overlapping arch-masked images | One image | P1 | Two built; the Arabic alphabet chart does not exist in staging and is briefed, not faked from a low-res crop |

### Iteration log

- **`claude-opus-5` — build.** Rebuilt the section, added `eqc_about_stat()` and
  the `--green` / `--icon-disc` button skins, set both heading tokens.
- **`claude-opus-5` — collage debug.** Both images computed correctly (loaded,
  sized, masked, opacity 1) yet only their gold frames painted. Cause was CSS
  order: `.eqc-about-collage figure { margin: 0 }` sat *after* the main image's
  `margin-inline-start: auto` and cancelled it, so the main sat flush left and
  covered the child exactly. Moved the reset above; added an explicit z-index.
- **`claude-opus-5` — a QA artefact, not a site bug.** After that fix the images
  still looked absent in an ad-hoc Playwright `fullPage` capture. They render
  fine in the live viewport: below-fold images are `loading="lazy"`, and a
  full-page capture that never scrolls does not load them. `tests/visual/sweep.mjs`
  already scrolls the page and waits before capturing, so the committed
  `QA/after/` evidence is truthful — **the ad-hoc capture was the unreliable
  one.** Use the sweep for evidence, not one-off MCP screenshots (LESSONS #23).

### Verification

Sweep at 1920/1440/1024/768/390 plus the 380–1900 scan: no overflow, 0 console,
0 page, 0 asset errors, 0 images missing `alt`, one H1.

### Remaining on this section

- The alphabet-chart quatrefoil (third collage image) — briefed in
  `QA/IMAGE-BRIEF.md`, not invented.
- The reference's collage is larger and **bleeds left** of the content column;
  ours stays inside it. Deferred with the collage's final geometry, which is
  hard to fix properly while one of its three elements is missing.

---

## Section 4 — Courses (`courses.jpeg`, `claude-opus-5`)

Calibration: ~1:1, anchored by the card grid (3 cards of 313u + 2 gaps of 88u
= 1115 against the 1119u content column).

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| C1 | Bare gold book icon + letterspaced "OUR COURSES", **no pill** | Full-width outlined pill | P1 | `.eqc-eyebrow--plain` variant; pricing/teachers keep their pills |
| C2 | Eyebrow label 145u wide beside a 37u icon | 8.85u type — a guess carried over from the old scale | P1 | `--eqc-fs-eyebrow` 8.85u → **20.7u** |
| C3 | Heading on **two** lines, tail bronze | Three lines: no explicit break and the block was capped at `--eqc-content-narrow` (558u) | P1 | `<br>` after "that" + block to 820u |
| C4 | Card 313 × 284u, gap 88u | 310 × 418u, gap 29u | P1 | `.eqc-grid--courses` gap 88u; card padding/gap tightened |
| C5 | Card title 160u ink, body 213u ink | h3 23.1u, body 11.6u | P2 | `--eqc-fs-h3` → 25.5u; card body 13u |
| C6 | Arrow bottom-**right**; index badge paired with a short rule | Arrow bottom-left, badge alone | P2 | `align-self: flex-end`; `::after` rule on the badge |
| C7 | Card copy ends "Ends when you can read short Quranic words unaided", title "Quran Reading **With** Tajweed", level "(Beginner **To** Advanced)" | Paraphrased, different capitalisation | P0 (§2) | all six cards transcribed verbatim |

**A measurement note worth carrying forward.** The heading's two lines gave
conflicting sizes — line 1 asked for 59.8u, line 2 for 54.5u. Line 1 matched the
independent card-grid calibration exactly, so 60u stands; our render of line 2
simply runs ~10% wider than the reference's for the same string and size (the
bronze span). The block was widened to keep the reference's two-line break
rather than shrinking the type to chase one line's width. **When two
measurements of the same token disagree, trust the one corroborated by an
independent anchor** — added as LESSONS #27.

Verified: sweep at five viewports + the 380–1900 scan, no overflow, 0 console /
page / asset errors, 0 images missing `alt`, one H1.

Remaining: the section's closing centred divider (473 × 48u) and the large
corner girih watermarks are not yet matched to the reference's size/placement.

---

## Section 5 — Pricing (`pricing.jpeg`, `claude-opus-5`)

Calibration: **s = 1.345** — anchored by the card row and the benefits strip
both spanning 1505px, i.e. the 1119u content column. (Confirmed after the fact:
the rebuilt benefits strip measures 1119u against the reference's 1120u.)

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| P1 | Heading **centred**, both lines dark green, explicit break | Left-aligned, "no hidden fees" in bronze, natural wrap | P0 | centred; single colour; `<br>` |
| P2 | Heading line 1 545u — smaller than the courses heading's 604u | shared 60u h2 | P2 | per-section 51u override (see note) |
| P3 | **Five** feature rows per plan, incl. "Personalized Focus" | Four | P0 (§2) | fifth added |
| P4 | Frequency in a dark-green **chamfered banner** 207 × 54u | Plain text | P1 | `.eqc-pricing-banner` with `clip-path` |
| P5 | Price + a cream **"Month" pill** + dark-green arrow disc | `$39/ month` as a slashed suffix, outlined arrow | P1 | `.eqc-pricing-unit` pill; green disc |
| P6 | White **benefits strip** below the cards, 1120 × 96u, four hairline-separated tiles | **Absent entirely** | P0 | `.eqc-benefits` + `eqc_benefit_tile()` |
| P7 | Cards span the full content column | Grid inset ~44u a side by the panel | P1 | panel padding zeroed → card 256u vs 262u |
| P8 | RECOMMENDED as a horizontal tab on the top-right corner | Diagonal corner ribbon | P2 | tab geometry |
| P9 | Cards sit straight on the section ground | Enclosing panel frame | P2 | panel background/border removed |

**Note on P2.** This heading measures 545u for a 23-character first line where
the courses heading measures 604u for a 22-character one — after both were
scaled by their own independent anchors. Rather than move the shared token
(which courses corroborates to within 0.3%), Pricing carries its own size. Two
sections legitimately differing is the same pattern as About's display heading.

Verified: sweep at five viewports + the 380–1900 scan, no overflow, 0 console /
page / asset errors, 0 images missing `alt`, one H1.

Remaining: the section's ornate gold corner frames, and the plan badge should
float fully clear of the card's top edge rather than overlapping it.

---

## Section order — a finding that needs a decision

The build's Home page runs: hero → trust → about → courses → **how it works** →
**teachers** → **pricing** → testimonials → **FAQ** → blog.

`TASK-DESIGN-PARITY.md` §10 and the reference set give: hero → trust → about →
courses → **pricing** → **teachers** → testimonials → blog → final CTA.

So the build has **two sections the reference does not contain** ("From first
message to first class", and the FAQ accordion), and **pricing/teachers are
swapped**. Removing or reordering sections deletes working content and changes
the page's argument, so it is not something to do unprompted — raised for the
user rather than actioned.

---

## Section 6 — Teachers (`Teachers.jpeg`, `claude-opus-5`)

Calibration: **s = 1.356** — content column x48–1565 = 1517px = 1119u, which
then decomposes exactly as **left 268u + gap 51u + card area 800u**. That clean
decomposition is the strongest confirmation of a scale factor so far.

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| T1 | **Two-column split** — aside (label, heading, ornament, body, three feature tiles, prev/next + CTA) beside four cards | Full-width heading + a carousel + one centred button | **P0** | `.eqc-teachers-split` at the measured 268/51/800 |
| T2 | Three-line heading, third line bronze, ~40.6u in its narrow column | One line, 60u | P1 | explicit breaks + per-section size |
| T3 | Three feature tiles in the aside | Absent | P0 | reused `.eqc-trust-tile` markup |
| T4 | prev/next circular buttons + dark-green "View All Teachers" pill | One centred button | P1 | `.eqc-teachers-nav` + `.eqc-btn--sm` |
| T5 | Oval portrait **overlapping the card's top edge**, book badge centred on the overlap | Portrait inside the card | P1 | absolute portrait at −56u, 104u content clearance |
| T6 | Two-line bold **sans** name | One-line display serif | P1 | `\|` break in the data + body font |
| T7 | Meta rows plain on the card | Tinted inset box | P2 | background/border removed |
| T8 | "View Profile" pill with a circled chevron, inside the card | Absent | P1 | added; `.eqc-btn--outline` |

Verified: sweep at five viewports + the 380–1900 scan, no overflow, 0 console /
page / asset errors, 0 images missing `alt`, one H1.

**Heading sizes across sections now measure:** About 70.5u (display), Courses
60u, Pricing 51u, Teachers 40.6u. Each was derived against its own independently
anchored reference, and the spread tracks the width of the column each heading
sits in — a designed hierarchy, not measurement drift.

---

## Section 7 — Testimonials (`Reviews.jpeg`, `claude-opus-5`)

Calibration: **s = 1.313** — cards span x50–1519 = 1469px = 1119u.

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| R1 | Heading in **bold sans**, not the display serif — the only section that does this | DM Serif Display | P1 | Manrope 800 at the measured 48.4u |
| R2 | Two lines, title case, second line gold: "Trusted by Families / Loved by Students" | One line, sentence case | P1 (§2) | transcribed with the break |
| R3 | Two-line intro paragraph under the ornament | Absent | P0 | transcribed |
| R4 | Circular avatar **overlapping the card's top edge**, dark-green quote badge at its lower right | Avatar inside the card | P1 | absolute at −54u; 108u diameter (matches exactly) |
| R5 | Cream inset strip of **three** mini-features, hairline-separated | Two tags, no strip | P0 | three, in the reference's own spelling ("Personalised Focus") |
| R6 | Rizwan's quote ends "JazakAllah for the amazing support!" | Sentence dropped | P0 (§2) | restored |
| R7 | Name in bold sans | Display serif | P2 | body font |

Verified: sweep clean at 1920; avatar 108u against the reference's 108u.

Remaining: the eyebrow's flanking rules (reference draws rule–icon–label–rule);
the carousel constrains cards to 332u against the reference's 354u.

---

## Section 8 — Blog (`Blogs.jpeg`, `claude-opus-5`)

Calibration: **s = 1.282** — cards span x86–1520 = 1435px = 1119u.

| # | Reference shows | Build did | Sev | Fix |
|---|---|---|---|---|
| B1 | Heading in **bold sans**, two lines, second gold | Display serif, one line, sentence case | P1 | Manrope 800 at 46.1u + transcribed break |
| B2 | White **date chip** (calendar icon, day, month) over the image's top-left, 65 × 79u | Absent — a category ribbon sat on the image instead | P0 | `.eqc-blog-date` in `eqc_render_blog_cards()` |
| B3 | Category as gold caps **above the title** | Inline in a meta line with the date | P1 | `.eqc-blog-category` |
| B4 | "Read More" + a gold circular **double-chevron**, bottom right | Plain arrow, inline | P1 | `.eqc-read-more__disc` with `chevrons-right` |
| B5 | Eyebrow icon is a **megaphone** | `monitor-play` (a screen) | P2 | `megaphone` added to the sprite generator |

## Section 9 — Final CTA (`End.jpeg`)

**Already complete.** Verified as `.eqc-footer-cta` in `footer.php` — Codex built
it from `End.jpeg` during the global-chrome pass, so it is not a missing Home
section. Measures 1219 × 365u and carries the reference's copy.

---

## Cross-section defect found by the heading outline

Transcribing the reference's line breaks with `<br>` produced run-on accessible
names across **six** headings — "Learning the Quranshouldn't depend on" — which
is exactly LESSONS #10 recurring at scale. A real space now precedes every
`<br>` in the page builder and the helpers; the `report.json` outline confirms
all ten headings read correctly. **The outline in `report.json` is the cheapest
way to catch this** — it is worth reading after any pass that adds line breaks.

Final state of Home: sweep at 1920/1440/1024/768/390 plus the 380–1900 scan —
no horizontal overflow, 0 console errors, 0 page errors, 0 asset errors, 0
images missing `alt`, exactly one H1, and a clean H1→H2 outline.

---

## Full recheck (`claude-opus-5`, 2026-09-09)

An adversarial pass over everything committed so far. It found six real
problems; all are fixed.

### 1. CSS I had duplicated against itself

Appending rules had produced `.eqc-card--course` defined **twice with
conflicting padding and gap**, the later silently winning — the same
source-order fragility that had already hidden a collage image. Consolidated,
and the superseded `.eqc-section-heading` max-width removed.

### 2. Helper changes never reached the other pages

`eqc_teacher_card()`, `eqc_trust_tile()`, `eqc_testimonial_card()` and
`eqc_section_heading_el()` all changed, but only `10-home.php` had been re-run
— every other page still had the **old markup baked into `_elementor_data`**.
The standalone `/teachers/` page was visibly missing its "View Profile" button.
All nine builders re-run. **This is the §4 gotcha-2 pattern generalised: a
change to a shared helper needs every page rebuilt, not just the one being
worked on.** Added as LESSONS #31.

### 3. An accessibility regression I introduced

Moving the blog category out of the image link left `a.eqc-blog-media` with
**no accessible name** — three nameless tab stops. The title and "Read More"
already link to the same post, so the image link is now `aria-hidden` and
removed from the tab order rather than given a duplicate name.

### 4. Tap targets below 44px (pre-existing)

**17** of them: seven 24×24 carousel dots and ten 33px-tall footer links. Fixed
by expanding the *hit area* with a pseudo-element, so the visual design is
unchanged and there is no layout shift.

### 5. Sections systematically too tall — the biggest finding

Section-height **ratios** are immune to the composite's uncertain vertical
scale, so they are comparable. Every section after the hero was running 1.2–1.6×
the reference's proportion, and `courses.jpeg` confirmed it independently:
that section measured **1256u against the reference's 940u (+34%)**.

Diagnosed, not guessed. Two causes, both mine or Elementor's:

- **Paragraph margins inside widget wrappers.** The course card's body widget
  measured 77.3u around 56.5u of text — 20.8u of dead margin per card, plus 6u
  on each divider. The card owns its own rhythm, so those are zeroed.
- **I had applied the reference's column gap to both axes.** Columns sit 88u
  apart, but row 1 ends at y571 and row 2 starts at y588 — a **17u row gap**.
  `gap: 88u` was adding 71u of dead space between the card rows.

Courses is now 1046u against 940u; the card 284u exactly matches the reference.
An earlier guess (`--widgets-spacing: 0`) changed nothing and was replaced by
the measured fix rather than left in place on the assumption it helped.

### 6. Verification of the whole site

- **Nine routes** (`/`, about, courses, teachers, pricing, contact, faq,
  free-trial, blog) × 3 viewports + a 39-width scan each: no horizontal
  overflow, 0 console errors, 0 page errors, 0 asset errors, 0 images missing
  `alt`, exactly one H1 per route.
- **Contrast:** all 72 distinct text styles on Home pass WCAG AA. Lowest
  passing ratio 3.48 (large text, needs 3.0).
- **Keyboard:** 54 focusable elements, **0** missing a focus ring, **0**
  missing an accessible name, **0** hit areas under 44px.
- **Decorative SVGs exposed to the accessibility tree: 0.**
- PHP lint clean.

### Confirmed from the composite

`Website Layout.png` independently confirms the section-order finding already
raised: it runs hero → trust → about → courses → **pricing → teachers** →
testimonials, with **no** "how it works" and **no** FAQ section. That remains a
user decision, not something to action unprompted.

## Supervisor re-audit — 2026-09-09, HEAD f60e0a8

The earlier completion claims above are superseded by this audit. Home is **not accepted**. Current evidence: `QA/supervisor-review/home/` (1920 and 390).

| ID | Section | Reference | Current defect | Severity | Fix and reuse |
|---|---|---|---|---|---|
| S1 | Order | Courses → pricing → teachers → reviews → blog | Teachers precede pricing; extra How It Works and FAQ blocks add 2,019px at 1920 | P0 | Remove extra Home composition blocks and reorder existing variables in 10-home.php; dedicated FAQ remains |
| S2 | Teachers | Smooth ellipse, 244×262 native, nearly card width | Pointed 164×218 portrait inside 270px card | P1 | Reuse teacher photo widget; CSS ellipse and separate cream/gold rings |
| S3 | Icons | Filled person/group/shield/cap silhouettes alongside outline utility icons | Blanket outline substitutions justified by family consistency | P1 | Original filled variants on the existing sprite grid; retain outline roles where reference shows them |
| S4 | Texture | Seamless subtle geometric texture | Flat-top hex polygons on pointy-top spacing overlap | P1 | Correct orientation in existing gen-ornaments.mjs |
| S5 | Corners | Opposed corner motifs | Translation keyframes erase bottom-left 180° rotation | P1 | Independent translate animation preserves rotation |
| S6 | Motion | Reference silent; reduced motion must be respected | More-specific animation selector defeats reduced-motion override | P1 | Match selector specificity, verify computed animation-name |
| S7 | Hero arch | 559×629 shared frame coordinates | CSS uses 537/620 and identical x/y inset percentages | P2 | Use actual SVG ratio and separate 24/559, 24/629 photo inset proportions |
| S8 | Reviews | Three supplied staging quotes | Six extra stub cards and false comment claiming client verification | P0 | Remove invented empty slides, retain registered reference quotes |
| S9 | Teacher typography | Single-line eyebrow, three-line title | Eyebrow wraps to two lines, title four; aside drives 1,396px section | P1 | Measure role-specific type and available width; reuse tokens/classes |
| S10 | About collage | Three images with distinct silhouettes | Alphabet panel missing; Quran alt describes a different photo | P1 | Original vector alphabet panel using existing Arabic font and quatrefoil, accurate alt |
| S11 | Pipeline | Documented commands work after checkout | Relative paths depend on caller directory; trace scratch missing | P1 | Module-relative paths and mkdir in existing generators |

Ordered plan: repair deterministic geometry/motion and Home order; correct icon roles and teacher anatomy; restore missing collage panel and reference-specific backgrounds; recapture and measure all Home sections; verify five widths, intermediate widths, keyboard, reduced motion, console, images and Lighthouse. Rebuild all nine page scripts after shared inline graphics changes. No completion assertion until rendered comparisons resolve P0/P1.

Improvised: original licensed/generated photographs remain per the user's explicit instruction. Filled reference roles use original paths in the existing sprite, not a second icon package. Reduced-motion corrections protect accessibility. Reference desktop spacing is adapted when required for readable mobile content.

---

## Scale correction — the canvas was calibrated to a crop

**Model: Claude Opus 5 (`claude-opus-5`).** Triggered by the user's report that
sections were too big to fit on screen, that content of one topic did not fit
together, and that excess spacing diverted focus. The report was correct.

### Root cause

`--eqc-u` was `100/1307 vw` because `Assests/Home.jpeg` is 1307px wide. But the
reference set is not one canvas:

| Reference | Pixels | Ratio | What it is |
|---|---|---|---|
| Blogs.jpeg | 1600 × 905 | 1.77 | full screen |
| pricing.jpeg | 1600 × 906 | 1.77 | full screen |
| Home2.jpeg | 1600 × 896 | 1.79 | full screen |
| Reviews.jpeg | 1600 × 862 | 1.86 | full screen |
| Teachers.jpeg | 1599 × 923 | 1.73 | full screen |
| End.jpeg | 1489 × 978 | 1.52 | part screen |
| **Home.jpeg** | **1307 × 967** | **1.35** | **crop** |
| **courses.jpeg** | **1296 × 974** | **1.33** | **crop** |

Six of eight are ~1600×900 laptop screens, each showing one complete section
filling the frame. The two the scale was anchored to are the only two whose
aspect ratio is not a screen's — the two that are cropped, so their pixel width
was never the canvas width. The absolute canvas width was the one free
parameter in the system and it was taken from the least reliable file.

This inflated every dimension by 1600/1307 = **+22%**. Ratios measured *within*
each file (LESSONS #11) are unaffected: only the multiplier moved.

### Measured before

At 1600×900 — the reference images' own dimensions:

| Section | Height | Viewports | Overflow |
|---|---|---|---|
| about | 1269px | 1.41 | +369 |
| courses | 1329px | 1.48 | +429 |
| pricing | 1313px | 1.46 | +413 |
| testimonials | 1303px | 1.45 | +403 |

Sticky header 197px at rest / 108px scrolled → 792px usable, so sections ran
**1.65× the usable screen**. Whole page 9,452px = 10.5 viewports.

### Two systemic bugs found alongside it

**Type contrast was 5.0:1** (H2 73.4px over 14.7px body at 1600) where healthy
editorial setting is 2.5–3:1. One multiplier drove both a 60u heading and a 12u
caption. This produces both complaints at once — oversized headings burn the
vertical budget, the same multiplier starves the body copy.

**33 `font-size: calc(n * var(--eqc-u))` rules bypassed the type tokens**, so
the `@media (max-width:1100px)` floor never reached them. On a 390px phone:
teacher facts and blog dates **7.6px**, teacher role 8.3px, course body 8.6px,
blog excerpt / testimonial body 9.0px, pricing list 9.4px — ten roles.

The previous pass's **"72 text styles pass WCAG AA" was a contrast check only**.
7.6px text passes contrast. That report claimed a clean bill of health it had
not earned; correcting it is part of this pass (LESSONS #37).

The media-query floor also produced a **discontinuity at exactly 1100px**
(captions 9.8px → 14.0px across one pixel of width) and left **1101–1600px**
— 1366, 1440, 1536 — with no floor at all. The same boundary-vs-floor mistake
left buttons at 40.6–44px across 1101–1213px.

### Changes

Three CSS files only; no PHP touched, so no `tools/pages/*.php` re-run needed.

- `tokens.css` — `--eqc-u: clamp(0.70px, 0.0625vw, 1.32px)`; every `--eqc-fs-*`
  becomes `clamp(floor, unit, ceiling)`; the floor media query deleted; header
  and section-space tokens reduced.
- `components.css` — all 33 raw sizes mapped onto tokens (new `--eqc-fs-price`,
  `--eqc-fs-h2-compact`); ornate rule margin 1em → 0.4em; pricing panel's
  redundant 64px inset removed; Elementor's untokened 20px container gap
  replaced; body leading 1.7 → 1.62; tap-target hit areas extended.
- `shell.css` — header 197→~87px scrolled; logo lockup and footer stat caption
  floored; header CTA rebalanced to the bar (user-reported: it was 76px tall
  with a 42px icon in an 86px bar).

### Result

Section heights at 1600x900, measured:

| Section | before | after | change |
|---|---:|---:|---:|
| about | 1269px | 994px | -22% |
| courses | 1329px | 1030px | -22% |
| pricing | 1313px | 932px | -29% |
| teachers | 942px | 857px | -9% |
| testimonials | 1303px | 1079px | -17% |
| blog | 1022px | 803px | -21% |

| | before @1600 | after @1600 |
|---|---|---|
| H2 / body ratio | 5.0 : 1 | 2.84 : 1 |
| whole page | 9,452px (10.5 screens) | 7,680px (8.5 screens) |
| smallest text anywhere | 7.6px | 11px |
| scrolled header | 108px | 87px |
| usable height | 792px | 813px |

Every section is now at or near one viewport except testimonials (1.20, a
carousel whose cards are deliberately large) and courses (1.14, two rows of
three cards). Teachers is the exception discussed below.

Across 390 → 2560: type ratio 2.05–3.06, no overflow-X at any width, no
discontinuity across the old 1100px boundary (1100 and 1101 now identical).

### Verification

- 9 routes × 6 viewports + 39-width scan each — all passed, zero console/page/
  asset errors, zero overflow.
- **Contrast: 0 AA failures** — re-run because the large-text threshold moved
  when heading sizes changed.
- Smallest rendered text on any route/viewport: 11px (the tracked logo
  wordmark; every content role ≥13px).
- Tap targets: the only remaining sub-44px targets are inline links inside
  sentences on /contact/ and /free-trial/ ("FAQ", "book a free trial"), which
  WCAG 2.2 SC 2.5.8 exempts explicitly. Padding them would break the line box.
- One probe false positive worth recording so it is not "fixed" later: the
  Fluent Forms fields on /contact/ report no accessible name to a check that
  only looks at `aria-label`/text/`title`/`alt`. All four have a proper
  `<label for>`.

### Known residual — teachers section

Teachers is the one section still over one screen at 1024–1366 (its focus unit
runs 768–824 against 682–688 usable; it passes at 1440, 1600 and 1920).

It is **not** a scale problem — it barely moved during this pass (862 → 835 at
1366) because its height is set by the 268u aside column beside the cards, not
by the cards. That column stacks a heading, a rule, a paragraph, three feature
tiles and two controls in a narrow measure, so its text wraps hard. Reducing
the card height moves the section by exactly 0px (verified — LESSONS #40).

Getting it under one screen at 1366 means shortening the aside copy or dropping
one of the three feature tiles. Both are content decisions, so they are raised
here rather than actioned.

### Also fixed while verifying

- **Teacher cards were unequal height** (`align-items: start`), so their "View
  Profile" pills sat at different heights. Now stretched with the pill pinned.
- **The pricing "RECOMMENDED" tab rendered as "…MENDED"** — it was placed inside
  the card, in the medallion's lane. `pricing.jpeg` shows the tab straddling the
  card's top border with the medallion dropped fully inside, which is why the
  featured card is the one card whose medallion does not overhang. Fixed to
  match, from measured element rects (LESSONS #39).
- Mobile hero carried 48px of top padding meant for the desktop arch overlap.

### Current-HEAD graphics findings (2486a90)

The new Claude scale commit is preserved for review; its screen-aspect inference is not proof of reference fidelity. Independently verified geometry defects remain:

| ID | Reference and measured native placement | Current defect | Severity | Fix / reuse |
|---|---|---|---|---|
| S12 | Home2 left girih strongest x0–280, fades before text x830 | Uniform large honeycomb under all copy | P1 | Reuse girih lattice and arch outline in separate left-fading CSS layers |
| S13 | Courses top-right lattice x1025–1295/y0–230; card patches about65×100 | Circular corner arcs and dots | P1 | Replace existing corner-motif and lattice-corner outputs with angular fields |
| S14 | Pricing mirrored top-edge brackets about250×245 | Opposite corners of inner card panel | P1 | Pricing bracket generated with existing geometry, attached to section edges |
| S15 | Pricing scalloped medallions about108×116; hollow19px bullets | Circle with sharply spiked ring; solid tiny bullets | P1 | One clean scalloped geometry with fill/outline variants; existing calendar icon |
| S16 | Reviews alternating100×110 card patches; no section brackets | Plain cards plus large section brackets | P1 | Reuse course-card pattern, alternate corners; remove extra section ornaments |
| S17 | Home2 top rail595px, center35px; Courses bottom rail470px | Uniform SVG scaling inflates center; bottom divider missing | P1 | Existing rosette + flexible CSS rules, independent center size |
| S18 | About child246×423 with pointed lobed bottom; alphabet276×300 | Flat-bottom hero frame / generic quatrefoil | P1 | Extend existing arch family into measured closed cartouches |

These are corrections to the supplied geometry, not a new design direction. The new cartouche, bracket and scalloped seal are justified because existing assets have different silhouettes. Plan: generate and inspect assets, integrate existing helpers/classes, rebuild every page, capture Home at five widths, then resolve remaining metrics and interaction flaws.

---

## Iteration log — canvas correction and the three reported sections (2026-09-10, `claude-opus-5`)

**Reported:** Teachers, Reviews and Pricing "squeezed horizontally, extra
padding on the right and left sides"; icons still wrong; backgrounds not
matching; courses cards vertical where the reference is horizontal.

### 1. The squeeze — one token, whole site

Measured at 1600x900: the header bar rendered 1504px (94.0%, matching
Home.jpeg's 94.1%) but **every** section's `.eqc-container` rendered 1119px —
**69.9%**, leaving 241px of dead margin each side against the reference's
85.6%. Cause: commit `2486a90` rebased `--eqc-u` to the 1600 canvas while
every transcribed value in the theme is a native pixel on Home.jpeg's 1307
canvas (QA/LESSONS.md #41). Restoring `--eqc-u` to `clamp(0.70px, 0.07651vw,
1.616px)` fixed all 82 horizontal values at once; content is now 1370px
(85.6%) at 1600 and 1644px at 1920.

The same rebase had silently shrunk the type, whose clamps ride the unit in
their middle term, so every `n` was divided by 1.224 to hold the sizes the
previous pass had measured (#42).

### 2. Heading sizes restored

Glyph-width comparison (LESSONS #12) against each reference, normalised to the
1370 column, showed the section headings 26–46% too small — an earlier pass had
collapsed the six measured sizes of #29 onto two tokens and compressed them to
reach a 2.5–3.2 heading:body ratio. The client's pricing section is 4.1:1.
Restored per-section: About 70.5u, Courses 60u, Pricing 51u, Testimonials/Blog
47u, Teachers 39u, H1 58u. All roles now measure within 5% of the reference
(`tools/graphics/scratch/type-parity.mjs`).

### 3. Courses — landscape cards

Reference card is 364x281 native (**1.30**), ours was 379x348 (1.12) on a
110px column gap. Re-measured the reference card borders directly: gap is 31
of a 1146 content column, not the 88u recorded in #33. The card is portrait
because the arrow sat on its own row; the reference places it beside the body,
bottom-right. Result: 425x335, ratio **1.27**, gap 37px. Course title given
its own 24.6u role (the blog title, which shared `--eqc-fs-h3`, already
measured correct), level recoloured to neutral grey and the index badge
rebuilt as a cream scalloped seal with a 26.4u numeral.

### 4. Teachers

- `.eqc-divider-svg` was a bare `clamp(180px, 22vw, 240px)`: the card divider
  rendered 240px inside a 183px card and ran across the gap (#44). Now
  `width: 100%` with the clamp as `max-width`.
- `.eqc-btn--sm` (0,1,0) lost to `a.eqc-btn` (0,1,1), so the "View All
  Teachers" pill kept 65px of inline padding and ran 305px wide in a 337px
  column (#45). Fixed to 215px against a 208px target.
- The aside column is the tallest element and therefore sets the card height
  (#40). Its feature caption wrapped to a third line because it measured 273px
  in a 260px column; at `--eqc-fs-xsmall` it fits on one line, the tile drops
  61px and the cards' dead space closes.
- Heading now breaks "Learn From / Dedicated / Quran Teachers" as the
  reference does.
- Section **818px** against a 923px reference frame.

### 5. Reviews

- The carousel has `pageCount === 1` — three cards, three visible — so both
  arrows were permanently disabled and one dot showed. Reviews.jpeg draws no
  arrows. `eqc.js` now marks such a carousel static and CSS drops the controls
  and their inline padding, which was holding the cards 22px narrower than the
  reference. Cards now **425px against 426px**.
- The mini-feature strip inherited an 18.8px column gap from the base flex
  rule through the `display:grid` override (#47), wrapping every label. With
  `gap: 0` and the reference's 19u strip inset, all three labels set on one
  line.
- Quote leading 1.7 -> 1.45, name promoted from h4 to h3 (measured 22% small).
- Section **910px** against an 862px frame.

### 6. Pricing

- `.eqc-pricing-freq` carried `margin-block: 1.9em 1em` — 52px of margin
  around a 48px banner.
- `.eqc-pricing-panel` padded the grid again by 32px top and bottom, on top of
  Elementor's default 10px, inside a section that already pads.
- Corner ornament was 306px against the reference's 228px.
- The featured card's lower medallion was **not** a defect — pricing.jpeg does
  the same (#49); only the magnitude was off.
- Section **934px** against a 906px frame.

### 7. Icons rewired (needed the all-page re-bake)

`users`->`people-pair`, `book-open`->`rehal-quran`, `shield`->`shield-star` in
the pricing benefits; `graduation-cap`/`check`/`chart-up` ->
`graduate`/`clipboard-check`/`target-arrow` on the testimonial tags;
`monitor-play`->`presenter` and `book-open`->`rehal-quran` on the about stats;
`shield`->`shield-halved` on the trust tiles; `eqc_page_hero()` default ->
`rehal-quran`. All nine builders re-run with `--user=1` and each save verified
by re-querying `_elementor_data`.

### Evidence

`QA/after/parity/` — nine routes, eight viewports, plus a 380–1900px scan in
40px steps: **0 failures, 0 warnings**, no overflow, no console/page/asset
errors, one H1 per page, no missing alt.

### Not done

The remaining sections are within 3–6% of their reference frames but not
exact; About (1095px) and Courses (1251px) were not worked in this pass and
still carry the spacing excess the three named sections had.
