# QA pass 2026-09-10 — progress tracker

Source tasks: `QA/qa-9-10.md` (client, do not edit). Screenshots: `QA/qa-10092026/`.
Local only, branch `feature/setup`, `main` untouched.

Legend: `[ ]` todo · `[~]` code done, not pixel-matched · `[x]` done + measured in browser.

**Rule adopted this pass:** a task is only ticked once a browser measurement
proves it. Round 4 ticked several items on the strength of having written the
CSS, and three of those were measurably still broken. See "Round 5" below.

---

## Round 10 — 2026-09-10 (`claude-opus-5`)

Client review of round 9's build, plus two items raised mid-round. Evidence:
`QA/after-r10/` — 9 routes x 8 viewports + the 380-1900:40 scan, 0 console
errors, 0 page errors, 0 failed requests, 0 horizontal overflow.

- [x] **R10-1 Corner girih flush to the section edge** — client: "some gap has
  been added between the edges of the ornament and the page side edge."
  Root cause: `--_motif-inset: clamp(14px, 1.6vw, 30px)` was defending
  against a round-6 bug (a single shared drift keyframe carrying the motif
  outward and clipping it) that round 7 had already fixed a different way
  (four per-corner keyframes, each moving INWARD only) — the inset outlived
  the problem it solved. Set to `0`; drift amplitude trimmed 7px/5px ->
  4px/3px so the largest gap in the animation cycle stays small. The
  `.eqc-pricing-panel`'s small motifs sit at a rounded corner with no
  `overflow: hidden` (deliberate — the medallions/RECOMMENDED tab need to
  overhang), so flush ink now clips past the curve exactly as it did for the
  review cards in round 8 (QA/qa-10092026/23.png) — fixed the same way,
  `border-top-right-radius` on the motif itself, matching the panel's own
  radius. Confirmed flush (`top/left/right/bottom` = 0 against the section)
  at 1920 and 390, and the pricing-panel clip verified via computed style
  (`border-top-right-radius: 25.3735px`) plus a screenshot showing no spill.
- [x] **R10-2 Mobile teacher cards ("too big images and totally messed up",
  QA/qa-10092026/24.png; then "still too big" once proportionally correct)**
  — two-part root cause. First: the portrait's WIDTH was percentage-based
  (98% of the card) but its vertical placement was `--eqc-u`-based, which
  floors at 0.70px below ~915px viewport width — below that width the two
  systems disagreed and the portrait grew with the card while the space
  reserved for it stayed frozen, overlapping the card's own content by
  ~120px (measured at 390px). Fixed by putting every offset on the same
  percentage-of-card-width basis (measured constants: margin-block-start
  77.49%, padding-block-start 53.48%, seal offset 16.9%, seal size 24% —
  clean, near-identical ratios at 1920/1440/1280/1024, confirming the
  relationship rather than assuming it). That fix was proportionally
  correct but still read as oversized against the plainer `/teachers/` page
  card — client: "make this the same ... smaller teacher picture and
  formatting matched". Rather than further scale the overlap treatment down,
  the whole override now only applies at `min-width: 61.3125rem`; below that
  the card falls through to the exact same base `.eqc-card--teacher`/
  `.eqc-teacher-photo`/`.eqc-teacher-seal` rules the `/teachers/` page grid
  already used — an exact match (photo width 126.69px measured identical on
  both pages at 455px viewport), not an approximation. Desktop re-confirmed
  unchanged (photo still 97.3% of card, `position: absolute`).
- [x] **R10-3 Teachers scrollbar -> dot navigation** — client: "grey
  Horizontal scroll bar ... old and bad looking. It should be like the Dot
  scroll like the review cards have." `scrollbar-width` set to `none` (both
  the home row and the `/teachers/` page grid) and `initTeachersNav()`
  rewritten as `initTeacherRows()`/`setupTeacherRow()`, building a
  `.eqc-slider-dots` strip in JS (same markup/ARIA pattern the reviews
  carousel's `buildDots()` already uses) for each snap-scroll row, active
  dot tracked by nearest-centre card on scroll. The home row's dots were
  initially off-centre — its dots wrapper is inserted as a sibling inside a
  flex row (`.eqc-teachers-split`), where a flex item's default width is its
  own shrink-to-fit content, so `justify-content: center` had nothing to
  center within. Fixed with `width: 100%` on `.eqc-slider-dots` (a no-op
  everywhere else it's used). Verified: dot click scrolls to and activates
  the correct card; row-center and dots-center measured identical (both
  220.45px) after the fix; `/teachers/` page (6 dots) and home (4 dots) both
  confirmed with no regression to the existing prev/next buttons.
- [x] **R10-4 Contact "Get in touch" panel alignment** — client: "vertical
  size and position should be aligned with the form properly, slight
  difference right now." Measured: the aside column (green panel +
  reassurance list) was naturally ~640px tall against a ~564px form — TALLER
  than the form, the opposite of Free Trial's much longer form (~1166px vs a
  ~632px aside), which is what the shared `.eqc-form-grid`'s
  `position: sticky` aside was built for. With the aside already at least as
  tall as the form, sticky just detached it from the form as the page
  scrolled. New `.eqc-form-grid--balanced` modifier (Contact only, via
  `tools/pages/15-contact.php`) sets `align-items: stretch` and disables
  sticky, so the shorter box (the form) grows to match the taller one — top
  and bottom deltas both measured `0` after the fix, versus `+10px`/`-36px`
  before. Also zeroed an Elementor-default 10px top padding on the aside
  column that had the "Get in touch" panel starting below the form's own
  top edge even before this fix. Free Trial untouched, still sticky.
- [x] **R10-5 Pricing cards too close together on mobile** — client, mid-round.
  Measured: each card's medallion badge shell overhangs ~36px above its own
  card's top edge (`top: -1.9rem` plus the `::before` shell's further
  `-0.42em` inset), but the grid's row-gap (`calc(31 * var(--eqc-u))`) floors
  at ~21.7px below ~915px viewport width — so the next card's badge
  overlapped the previous card's bottom edge by 13-14px at both the 2-column
  and 1-column breakpoints. Fixed with a flat `row-gap: 2.75rem` (44px, not
  `--eqc-u`-based so it can't floor) below 61.25rem — measured 8-9px of clear
  space afterward at both breakpoints, confirmed with a screenshot.
- [x] **R10-6 FAQ content duplicated between home and /faq/** — same six
  question/answer strings hardcoded in both `10-home.php` and `16-faq.php`;
  an edit to one could silently drift from the other. Extracted to
  `eqc_faq_data()` in `elementor-helpers.php`; both pages now read from it.
  Re-baked both pages, verified the saved `_elementor_data` contains the
  expected strings, and confirmed the home page still renders the same six
  questions in the same order.
- [x] **R10-7 Minor cleanup** — removed an inert `-webkit-mask-composite:
  source-in` (evergreen WebKit/Blink read the unprefixed `mask-composite`
  once present and never consult the legacy keyword property); added
  cross-reference comments for `.eqc-pricing-list`/`.eqc-pricing-price-divider`
  (rules genuinely split ~1800 lines apart) so a future edit doesn't touch
  one half and miss the other; fixed `QA/GRAPHICS-PARITY.md`'s own
  regeneration command, which wrote to `QA/after` instead of the evidence
  path (`QA/graphics-parity-final`) the doc had just cited; renamed
  `QA/qa-10092026/7..png` (double dot) to `7.png`; pruned the superseded
  `QA/after/all-pages/` and `QA/after/global-chrome/` sweep subfolders
  (round 8 leftovers, no doc referenced either path).
- Not merged: `.eqc-card--pricing--featured`'s four occurrences turned out to
  already sit adjacent (lines 741-778), not scattered as the code-review's
  line-count grep suggested — no action needed there.

Full functional pass re-run after all of the above: stretched card links on
`/courses/` and `/pricing/` still hit-test correctly; FAQ accordion
opens/closes; mobile nav drawer opens, traps focus (`inert` on
`#eqc-content`), closes on Escape; WhatsApp CTAs resolve to a `wa.me` link.

## Round 9 — 2026-09-10 (`claude-opus-5`)

Free Trial and Contact page redesign (client: "no color Contrast, no
interesting or attention grabbing feel ... looking small and cards have empty
spaces ... the form is too plain"), followed by a code review of the diff.

- [x] **R9-1 Free Trial / Contact redesign** — both pages rebuilt around
  `.eqc-card--step` (icon in the site's gold seal disc, not a bare glyph),
  bronze `<span>` emphasis in section headings (matching the rest of the
  site's heading treatment), a dark green `.eqc-panel--invite` aside (same
  gradient/girih-watermark treatment as the footer CTA, replacing a second
  plain white box), and a `.eqc-reassure` list filling the ~600px of empty
  column the short aside used to leave beside the form.
- [x] **R9-2 Code review, two Critical findings, both fixed and verified:**
  - WhatsApp button contrast failed WCAG AA — white on `#25D366` measured
    **1.98:1** (the CSS comment claimed 2.4:1), applying on `:focus-visible`.
    Switched the label to `var(--eqc-heading)`: **8.23:1**, comment corrected.
  - `/teachers/` page prev/next buttons had an `aria-label` and focus ring but
    **no click handler at all** — a CSS comment claimed they scrolled the
    row. Wrote `initTeachersNav()` (superseded by `initTeacherRows()` in
    round 10) wiring them to the snap-scroll row; verified paging
    (`scrollLeft` 0 -> 262 -> 0) and disabled-at-ends state.
  - Also fixed two Important findings from the same review: two gold literals
    had drifted off the token ramp during the contrast fix (gold-500 ->
    gold-600 sparkle, an untokened dark motif) — added RGB channel tokens
    (`--eqc-gold-*-rgb`) so alpha variants derive from `tokens.css`; a stale
    keel-arch comment contradicting its own neighbouring rule was replaced.

Full sweep after: 11 route folders x 8 viewports, 0 console/page/request
errors, 0 overflow, one H1 per route, 0 images missing alt. `style.css`
1.8.0 -> 1.9.0.

## Round 8 — 2026-09-10 (`claude-sonnet-5`)

Ornament depth, favicon, repo cleanup and the first full-coverage QA pass.
Evidence: `QA/after/` — **11 routes x 8 viewports** (1920/1440/1280/1024/768/
430/390/360) plus the 380-1900:40 scan. Previous rounds only ever ran 5
viewports over 9 routes; `/blog/` and a single-post permalink had never been
captured at all. 0 console errors, 0 page errors, 0 failed requests,
0 horizontal overflow, one H1 per route, 0 images missing alt.

### Ornament depth (the design ask)

- [x] **R8-1 Pricing corner frame** — it still carried `opacity: 0.5` AND a
  single `drop-shadow(... / 0.28)`. Because `opacity` groups the already
  filtered result, the shadow landed at an effective ~0.14 alpha. Measured on
  a raster of the real paint: **5.6 luminance units** of darkening below the
  artwork's silhouette. The same trap was diagnosed and fixed on
  `.eqc-corner-motif` in round 4 and never carried across to its sibling
  (QA/LESSONS.md #51).
- [x] **R8-2 The first attempt was wrong and the client caught it**
  (`QA/qa-10092026/22.png`). Raising opacity to 0.8 with a four-layer dark
  shadow stack lifted the measured darkening to 31.5 units — but it turned the
  ornament grey. `corner-ornament.svg` is a *pierced* girih panel: cream tiles
  separated by fine gold rules, with real holes between them, so `drop-shadow`
  deposits ink through every hole. The cream tiles ended up floating on a grey
  ground, i.e. the tiles read as raised, when the reference
  (`Assests/pricing.jpeg`) has the gold **rules** standing proud.
  Corrected to `opacity: 0.62` with one tight low-alpha contact shadow, a fine
  light bevel, and `contrast(1.12) saturate(1.08)` to make the gold rules pop.
  Depth here is a lighting problem, not a cast-shadow problem.
- [x] **R8-3 Background layers softened** (client request) —
  `.eqc-section--about::after` keel watermark 0.11 -> 0.07,
  `::before` girih lattice 0.13 -> 0.10, `.eqc-corner-motif` tint 0.32 -> 0.26
  and `--sm` 0.34 -> 0.28. The motif tint lives in the paint's alpha, so
  lowering it does not weaken its drop-shadow — that is the whole point of
  LESSONS #51.

### Corner ornaments meeting the card corner

- [x] **R8-4** The review-card ornament sat a few pixels off the card corner.
  Root cause measured, not guessed: `corner-motif.svg` fills its viewBox with a
  girih lace **pattern tiled from the SVG origin**, and the canvas is not a
  whole number of tiles wide — so the dense corner landed in a tile void.
  **7px** of empty canvas along the top edge, alpha 0 at the corner pixel.
  Aligning a star *centre* on the corner made it worse (10px), because the
  stars are drawn as outlines and their centres are hollow. Swept every phase
  and scored ink in the block at the corner: `corner-motif` wants 11/12 of a
  tile, `lattice-corner` wants 5/6 — the fraction is not shared, so each asset
  carries its own. Both now measure **corner alpha 116 / 132, gap 0** on both
  edges.

### Favicon

- [x] **R8-5** The tab showed a generic icon because `site_icon` is 0 and
  **nothing in the theme emitted icon tags** — the assets existed
  (`assets/svg/logo/favicon.svg`, `favicon-32.png`, `apple-touch-icon-180.png`)
  but were never linked. Added `eqc_site_icons()` on `wp_head`, which defers to
  WordPress if an admin ever sets a Site Icon. Verified all three serve 200.
- [x] **R8-6** `favicon.svg` was generated at the mark's natural **770x692** —
  browsers scale a favicon into a square slot, so it rendered ~11% stretched.
  Now centred in a square 770x770 canvas.

### Two real generator bugs found while doing the above

- [x] **R8-7** `build-logo.mjs` read its input and wrote its output through
  **CWD-relative paths**, so running it from `tools/graphics` (where its
  sibling generators run) silently created a stray
  `tools/graphics/wp-content/themes/.../logo/` tree and left the real theme
  assets untouched. Both paths now resolve from `import.meta.url`.
- [x] **R8-8** `scratch/` is gitignored but holds two **required build inputs**
  (`rosette-traced.json`, `mark-path.txt`), so a fresh clone died on a raw
  ENOENT. Both reads now fail with the trace command to run.

### Cleanup

- [x] **R8-9 QA evidence** — 271MB -> **14MB**. Deleted superseded screenshots
  (`QA/baseline`, `QA/supervisor-review`, `QA/graphics-parity-final`, the six
  loose resolved-bug PNGs, old sweep output) while **keeping every JSON report
  and Lighthouse baseline** in them, plus `QA/qa-10092026/` (client
  annotations) and `QA/icon-parity/` (cited by CLAUDE.md). The two docs that
  cited pruned images were corrected in the same pass, and `.gitignore` now
  excludes sweep PNGs so a round costs ~40KB, not ~100MB.
- [x] **R8-10 scratch/** 67MB -> 682KB (throwaway PNG/HTML analysis output;
  scripts and build inputs kept).
- [x] **R8-11 Dead code** — `.eqc-pricing-divider` (emitter deleted in round 4)
  and `.eqc-cta-ornament` (zero hits in any PHP/JS/markup). Kept
  `.eqc-arch-media--fourcentred`/`--horseshoe` (a documented shape library) and
  `.eqc-carousel--static` (added at runtime by `eqc.js:302`).
- [x] **R8-12** `debug.log` (Chrome GPU noise from Playwright) deleted.
- [x] **R8-13 Version** — `style.css` 1.7.0 -> 1.8.0.

### Functional pass

All measured, not eyeballed: 6/6 course cards and 4/4 pricing cards hit
`.eqc-card-link` at three points each; 18 unique internal links all resolve
200; zero links with an empty scheme or an unresolved token (the class of bug
behind the dead `tel:` on "Call Any Time"); 7 FAQ toggles present.

### Noted, not "fixed"

- The footer's "Give Donation", "Education Support", "Our Campaign", "Privacy
  Policy" and "Terms & Conditions" links look like stray demo content but are
  **deliberate registered placeholders** transcribed from the client's
  `End.jpeg`, awaiting approved destinations — see
  `QA/PLACEHOLDER-REGISTER.md`. Left exactly as they are.
- Ten footer/contact inline links render 33px tall. That passes WCAG 2.2 AA
  (2.5.8, 24px minimum) and misses the AAA 44px guidance; they are inline text
  links in a list, not buttons. Recorded rather than changed.

## Round 7 — 2026-09-10 (`claude-sonnet-5`)

Client follow-ups during the round-6 pass (`21.png` plus four inline requests).
Evidence: `QA/after-r7/` — 9 routes x 1920/1280/768/430/390 plus a 380-1900:40
scan. 0 console errors, 0 page errors, 0 failed requests, 0 horizontal
overflow, one H1 per route, 0 images missing alt.

- [x] **R7-1 "Call Any Time" went nowhere** — the button's `tel:` target was
  built from the `eqc_phone_display` theme mod, an unset placeholder on this
  install, so the href resolved to a bare `tel:`. Repointed at
  `eqc_whatsapp_url()` on client instruction, with the `--whatsapp` hover so
  the destination is signalled before the click. Verified `href` is now
  `https://wa.me/...`.
- [x] **R7-2 Featured card outline + sparkles** — 2px gold border plus a
  `1px` gold `outline` at `outline-offset: 3px` (both paint outside the border
  box, so all four cards stay 377px wide and top-aligned). Sparkle tint moved
  into the paint alpha (0.45 element opacity -> `rgb(169 130 53 / 0.62)`) with
  a `drop-shadow` bloom, per LESSONS #51.
- [x] **R7-3 Keel arch taller + child over its left side** — main panel
  `aspect-ratio` 559/629 -> 559/706, and offset with `left: 12%`. The margin
  approach did NOT work: Elementor's own `figure { margin: 0 }` reset scores
  (0,4,1) and silently zeroed it — see LESSONS #63. Verified: the child
  cartouche now overlaps 187px into the main arch's left edge.
- [x] **R7-4 Vertical separation between the two overlays** — 94px at desktop.
  The overlays are sized as a fraction of the collage but stack against its
  height, so the desktop percentages collided at 390px (child covered the
  alphabet's bottom row by 28px); shrunk to 34%/33% below 900px, giving 50px
  of clear air.
- [x] **R7-5 Background keel watermark meets the section floor** — anchored to
  `bottom: -5%` with `mask-position: bottom` and `mask-size: 100% 100%`
  (`contain` would letterbox it and re-expose the base inside the box), so the
  arch's own bottom line falls outside the section's `overflow: hidden` and is
  never drawn.
- [x] **R7-6 Courses corner ornaments** — they were flush at `top/right: 0`
  inside an `overflow: hidden` section while a shared drift keyframe pulled
  every motif `-16px/-10px`, so the top-right one was clipped for most of its
  cycle and the pair never matched. Now inset `clamp(14px, 1.6vw, 30px)` (more
  than the drift amplitude) with per-corner keyframes drifting along their own
  diagonals. Verified both motifs fully inside the section at matched 30/31px
  insets. See LESSONS #64.
- [x] **R7-7 Pricing line spacing + banner headroom** (`21.png`) — feature-list
  gap 15u -> 7u (measured line gaps 10px), and the card's top padding raised to
  44u so the banner sits 102px below the card's top edge with 67px clear of the
  medallion.
- [x] **R7-8 Version** — `style.css` 1.6.0 -> 1.7.0.

## Round 6 — 2026-09-10 (`claude-sonnet-5`)

Client re-review of round 5. Screenshots `15.png`–`20.png`. Evidence:
`QA/after-r6/` — 9 routes x 1920/1280/768/430/390 plus a 380-1900:40 scan.
0 console errors, 0 page errors, 0 failed requests, 0 horizontal overflow,
one H1 per route, 0 images missing alt.

### What round 5 left wrong

| # | Symptom | Measured cause |
|---|---|---|
| R6-a | Empty band across the pricing cards (`15.png`) | TWO stacked "pin to the bottom" rules: `.eqc-pricing-price-divider { margin-block-start: auto }` and the shared `.eqc-card__foot { margin-top: auto }`. Between the divider and the price row sat **84px** of nothing inside a 343u min-height card. |
| R6-b | "RECOMMENDED" still overflowing (`15.png`) | The base `.eqc-pricing-badge` is the old diagonal ribbon with a FIXED `width: 8.5rem`. Round 5 replaced its padding and position but never the width, so a 158px label was laid out in a 136px box with `white-space: nowrap`. |
| R6-c | Hero arch tip clipped (`17.png`) | The point sat **14px above the nav bar's lower edge** — inside the bar's band. Invisible at rest (transparent header), sliced flat the moment `.is-scrolled` gave the header an opaque ground at z-index 200. |
| R6-d | Photo not meeting the frame's bottom vertices (`16.png`) | `.eqc-arch-media--keel img` still carried `mask-size: 95.7066% 96.1844%`, an inset that existed only for the old DOUBLE-line frame. Centred, the slack landed unevenly: the foot stopped **20px** short. |
| R6-e | About keel frame missing its bottom edge (`18.png`) | Round 5 applied the open-bottom `d.replace(/Z$/,'')` to BOTH keel frames. Only the hero was meant to lose its base. |
| R6-f | About collage main image too small, bare background (`19.png`) | Main panel at 72% read as one of three equal images. And the section carried `--ornamented` but **never called `eqc_section_ornaments()`**, so it had no corner arabesque at all. |

### Round 6 fixes

- [x] **R6-1 Pricing dead space** — dropped `margin-block-start: auto` from the
  price divider, zeroed `.eqc-card--pricing .eqc-card__foot`'s `margin-top`,
  and gave the card `justify-content: center` with a 16u gap. Measured: the
  divider-to-price gap fell 84px -> 16px, and the block now sits 63px from the
  card top / 47px from the bottom. All four cards' banners, lists, prices and
  medallions still share one top.
- [x] **R6-2 Body copy bigger** — list items to `calc(var(--eqc-fs-body) * 1.09)`
  (18.53px at 1920), list gap 9u -> 15u.
- [x] **R6-3 RECOMMENDED tab** — `width: auto` so the pill sizes to its label.
  Verified `scrollWidth === clientWidth` (158px), straddling the featured
  card's top-right edge and inside the card's width.
- [x] **R6-4 Banner lobes + 3D** — `closedCartouche()` gained a `lobeDepth`
  option; the banner passes 1.85 while the eyebrow frame keeps the measured
  1.0, so the shared cartouche family is untouched. Depth comes from a vertical
  green gradient plus mask-following `drop-shadow`s (a dark cast below, a pale
  bevel above) and a text-shadow.
- [x] **R6-5 Hero tip** — hero media dropped 20u so the point clears the nav
  bar. Verified: tip 16px below the bar at rest (was 14px inside it).
- [x] **R6-6 Photo meets the frame** — keel photo mask to `100% 100%`. The
  photo's bottom now sits within **3px** of the frame's base (was 20px).
- [x] **R6-7 About keel frame closed** — `keel-arch-frame-round.svg` keeps its
  trailing `Z`. Verified by raster: 203px of ink along the bottom edge, jambs
  still even at 4px/4px. The hero's square-footed frame stays open.
- [x] **R6-8 Collage main image bigger** — 72% -> 88% width, collage padding
  4%/9%, `margin-inline: auto 2%`.
- [x] **R6-9 About background mesh** — `eqc_section_ornaments()` gained a
  `$corners` argument (default unchanged); the home About section now emits
  `tl`/`br` motifs, with new `--tl`/`--br` CSS. Girih lattice opacity .08 ->
  .13 carried to 52% of the width (was 33%), and the keel watermark grown to
  52% x 104% at .11 to sit behind the enlarged collage.
- [x] **R6-10 About-page approach list -> icon chips** — the ticked
  `.eqc-teacher-facts` list is now the home page's `eqc_chip()` card component
  in a new 3-up `.eqc-chip-row--3` (3 -> 1 on phones, per the symmetry rule).
- [x] **R6-11 Version** — `style.css` 1.5.0 -> 1.6.0.

### Still open

- [~] Pricing banner lobe depth is now clearly sculpted but still reads a touch
  shallower than `pricing.jpeg`'s; the shape family and 3D treatment are right.
- No git commit made — awaiting user review.

## Round 5 — 2026-09-10 (`claude-sonnet-5`)

Client re-review after round 4. Evidence: `QA/after-r5/` — 9 routes x
1920/1280/768/430/390 plus a 380-1900:40 overflow scan. 0 console errors,
0 page errors, 0 failed requests, 0 horizontal overflow, one H1 per route,
0 images missing alt.

### What round 4 got wrong

| # | Reported as done | What was actually true (measured) |
|---|---|---|
| R1 | Course cards clickable | Every `.eqc-card-link` was **413x0px**. `elementFromPoint()` at a card's centre returned a `<p>`. The featured pricing card's link was **338x0** for the same reason. Root cause: `.eqc-card--course > *` / `.eqc-card--pricing--featured > *` set `position: relative` on the **Elementor widget wrapper**, making it the anchor's containing block; the wrapper's only child is the absolute anchor, so it collapsed to zero height. |
| R2 | Keel arch frame = single even border | The jambs rendered at **3px against the crown's 4px**. `keel-arch-frame.svg` was generated pad-0, so the path ran along `x=0`/`x=559` and half of each vertical stroke fell outside the viewBox and was clipped. |
| R3 | Hero arch tip rises over the nav | The **whole image** was pulled up 135px, putting its top at `y=-40` — the tip was cut off above the viewport — and it sat *behind* the nav (z 1 vs 100). |
| R4 | (not caught at all) | The same containing-block bug displaced the featured card's **medallion by 38px** and put the RECOMMENDED badge **49px clear of the card**. |
| R5 | (not caught at all) | The mobile teachers carousel was only applied to `.eqc-grid--teachers` (the /teachers/ page). The **home** section uses `.eqc-teachers-cards` and was still a plain vertical stack at 390px. |
| R6 | (not caught at all) | `.eqc-section--hero`'s negative top margin was unscoped, leaving a **2px** gap between the nav bar and the mobile eyebrow. |

### Round 5 fixes

- [x] **R5-1 Stretched card links** — new `eqc-card-link-widget` wrapper class from
  `eqc_html()`'s `$classes` arg; CSS stretches the wrapper chain instead of
  fighting the `> *` rules. Verified: all 6 course cards + all 4 pricing cards
  hit `.eqc-card-link` at 3 points each; a real click navigates to `/free-trial/`.
- [x] **R5-2 Keel arch even stroke** — generator pads the viewBox by `ceil(3/2)=2`
  a side, and `.eqc-arch-media--keel::before` grows its own box by the same
  ratio (`-2/629`, `-2/559`) so the outer half of the stroke has somewhere to
  paint. Verified by rasterising the mask: left jamb == right jamb (5px/5px
  hero, 4px/4px collage), `crown@0.38` = 4.24px against a 4.25px nominal.
- [x] **R5-3 Hero position + tip in front of the nav** — negative margin removed;
  hero media `z-index: 120` with `pointer-events: none`; `.eqc-header.is-scrolled`
  takes `z-index: 200` so the bar returns to the front on scroll. Verified: arch
  top `y=95` (was -40), overlaps the bar, FREE TRIAL still clickable.
- [x] **R5-4 Pricing card family** — `scale(1.07)` dropped (client chose to match
  `Assests/pricing.jpeg`, where all four cards are the same size). Badge and
  medallion get `eqc-pricing-anchor-widget`, stretched like the link so they
  anchor to the card. Verified: banners/lists/prices/medallions all share one
  top across the four cards; badge straddles the top-right edge (267-307 vs card
  top 288), stays inside the card width, and clears the medallion (1629 > 1618).
- [x] **R5-5 Pricing content alignment** — list is `width: fit-content;
  margin-inline: auto` (bullets left-align to each other inside a centred block);
  `.eqc-pricing-price-divider` full-width and centred. Verified: both offsets
  from the card's centre are **0** (divider was 38px left).
- [x] **R5-6 "Month" tag prominence** — solid `#F0E7D3` fill, gold-300 border,
  `--eqc-fs-body`, heading colour.
- [x] **R5-7 Gap to the benefits strip** — `.eqc-benefits` margin 6u -> 14u.
  Measured 23px before.
- [x] **R5-8 Mobile hero gap** — hero's negative margin scoped to >=56.25rem, with
  `padding-block-start: 26u` below it. Verified at 390px: **40px** (was 2px).
- [x] **R5-9 Keel arch rounded foot, About only** — new `bottomRadius` option on
  `cuspedArchPanel()`; new `keel-arch-mask-round.svg` + `keel-arch-frame-round.svg`;
  `--keel-round` modifier on the home collage and both About-page images. The
  hero keeps its square jambs. Verified: photo mask sits >=11px inside the frame
  contour all round and stops 11px above the box bottom.
- [x] **R5-10 About collage nudged left** — `margin-inline: auto 0` -> `auto 6%`.
- [x] **R5-11 Course cards** — index dash removed, `.eqc-card-divider` centred
  (offset 0 measured), level restyled to `--eqc-gold-700` weight 600.
  gold-700 is the only gold that clears WCAG AA on cream (6.3:1).
- [x] **R5-12 Home teachers mobile carousel** — snap-scroll applied to
  `.eqc-teachers-split > .eqc-teachers-cards` at <=61.25rem. Verified at 390px:
  `overflow-x: auto`, scrollWidth 1135 > clientWidth 324, all 4 cards on one row.
- [x] **R5-13 Review card divider** — 90px -> `calc(100 * var(--eqc-u))` (min 120px).
- [x] **R5-14 Footer CTA mesh** — the hard-edged 44% band is replaced by the
  girih mask intersected with a horizontal gradient, so it dissolves into the
  green. Full width, opacity .14 -> .2.
- [x] **R5-15 Teachers-page step cards** — inline styles replaced by
  `.eqc-card--step`: 34u icon, h4 title, body-size copy in `--eqc-text`, card
  hugs its content. Fixes the dead band in `11.png`.
- [x] **R5-16 WhatsApp button** — `eqc-btn--whatsapp` modifier on all four call
  sites; brand `#25D366` with white label and icon on hover/focus-visible/active.
  Verified computed `rgb(37, 211, 102)`.
- [x] **R5-17 Version** — `style.css` 1.4.0 -> 1.5.0.

### Still open

- [~] About collage geometry vs `Assests/Home2.jpeg` — structurally right and
  nudged left as asked, but not pixel-matched.
- [~] Pricing banner cartouche lobes are subtler than `pricing.jpeg`'s. Correct
  shape family.
- No git commit made — awaiting user review.

---

## Round 4 — 2026-09-10 (earlier the same day)

Items below were completed in round 4 and re-confirmed still good in round 5's
sweep, except where round 5 re-opened them above.

### Phase A — Icons (sprite generator)
- [x] A1 certificate thinner — ORIGINAL_OUTLINE stroke 2.1->1.6
- [x] A2 clock inner ticks thinner — 2.1->1.3, shorter ticks
- [x] A3 megaphone thinner — 2.1->1.8
- [x] A4 phone -> clean outline handset (client-picked drawing)
- [x] A5 all book icons -> rehal-quran; archive eyebrow -> megaphone;
  free-trial form step -> clipboard-check

### Phase B — Home hero / first section
- [x] B1 keel arch frame: single border, open bottom — *stroke evenness
  re-opened and fixed as R5-2*
- [x] B2 arch tip over the nav — *re-done as R5-3*
- [x] B3 mobile chips 2 per row — verified again at 390px (2 per row)
- [x] B4 trust strip height

### Phase C — Pricing
- [x] C1 banner -> cusped cartouche (`pricing-banner-mask.svg`)
- [x] C2 RECOMMENDED tab — *re-done as R5-4*
- [x] C3 "Month" tag — *strengthened again as R5-6*
- [x] C4 feature list size/colour
- [x] C5 divider under banner removed
- [x] C6 hover pop — *scale interaction removed in R5-4; hover is now a clean
  `translateY(-10px)` with no `translateX` and no card scale*

### Phase D — Testimonials
- [x] D1 contrasting `#F1EBDF` ground
- [x] D2 avatar centred
- [x] D3 corner ornament both tops, darker
- [x] *divider width re-opened as R5-13*

### Phase E — About / second section
- [x] E1 bg pattern density (girih 30u tile, left-weighted fade)
- [~] E2 collage positioning — *nudged left in R5-10, still not pixel-matched*
- [x] E3 About-page arch frames -> keel — *now the rounded-foot variant, R5-9*
- [x] E4 About copy em dashes removed

### Phase F — Teachers
- [x] F1 6 cards 3x2 (4 real + 2 marked placeholders)
- [x] F2 mobile side-scroll — *only half-done; home section fixed as R5-12*
- [x] F3 centre-aligned

### Phase G — Courses
- [x] G1 equal gap (30u both axes)
- [x] G2 centre content
- [x] G3 cards -> free trial — *the link was dead; fixed as R5-1*
- [x] G4 guide rows clickable — re-verified: all 6 `#course-NN` targets resolve

### Phase H — Footer CTA banner
- [x] H1 stat fits its circle (verified `scrollWidth <= clientWidth`)
- [x] H2 overlaps the avatar stack
- [x] H3 bg darker + golden — *hard edge re-done as R5-14*

### Phase I — Ornament depth
- [x] I1 girih-corner depth (tint in `background-color` alpha so the dual
  drop-shadow renders full strength)
- [x] I2 corner-frame depth

### Phase J — Global symmetry rule
- [x] J1 auto-fit grids -> explicit counts
- [x] J2 card grids aligned on 61.25rem / 36rem
- [x] J3 rule added to `DESIGN.md` §7

### Phase K — Copy cleanup
- [x] K1 em dashes + AI tells removed across 9 builders + `footer.php`
- [x] K2 re-baked all 9 pages

### Phase L — FAQ on home
- [x] L1 FAQ section after the blog preview (6 items + "View All FAQs")
- [x] L2 accordion verified
