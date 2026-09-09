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
