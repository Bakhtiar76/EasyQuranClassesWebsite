# Design parity execution

Local target: `http://localhost`; branch: `feature/setup`. Supervisor: Codex; bounded harness assistant: Claude Code `claude-opus-5`. Existing user reference-image replacements and `local_SS/` are preserved.

## TODO / reuse / verification

- [x] Read task, project rules, design system, graphics pipeline and setup sections completely.
- [x] Inspect branch, working tree and installed local tooling.
- [x] Inspect local Docker and read-only WordPress state.
- [x] Extend existing screenshot harness; create one-command page rebuild/flush/sweep loop (PHP/JS syntax verified; first actual rebuild verification pending).
- [x] Capture all routes at 1920/1440/1024/768/390 before presentation changes, including Lighthouse baseline.
- [x] Global chrome: measured review, reuse plan, implementation, iterations, responsive/a11y/performance evidence (checkpoint being recorded).
- [~] Home: hero + trust strip matched to the reference within 9px worst / 2.9px mean across 32 landmark checks (`claude-opus-5`), on a new proportional scale system (`--eqc-u`, one unit = one px on the 1307px reference canvas). All Home sections now reviewed and matched against their own reference images (`claude-opus-5`): hero + trust strip, about, courses, pricing, teachers, testimonials, blog; the final CTA was already complete from Codex's global-chrome pass. Two extra sections in the build ("From first message to first class", FAQ) and a pricing/teachers order swap are documented in `QA/design-review/home.md` and await a user decision.
- [ ] Courses: review/build/verify/commit.
- [ ] Pricing: review/build/verify/commit.
- [ ] Teachers: review/build/verify/commit.
- [ ] About: review/build/verify/commit.
- [ ] Contact: review/build/verify/commit.
- [ ] Free Trial: review/build/verify/commit.
- [ ] FAQ: review/build/verify/commit.
- [ ] Kids: review/build/verify/commit.
- [ ] Blog archive and single: review/build/verify/commit.
- [ ] Finish placeholder register, image brief, asset sources and accumulated lessons.
- [ ] Prove clean-bootstrap reproduction in isolated local volumes.
- [ ] Review final diff, remove newly obsolete code/assets and verify `main` untouched.

Reuse `tools/pages`, `elementor-helpers.php`, child-theme tokens/shell/components, SVG generators and `tests/visual/sweep.mjs`; each page review records exact ownership and any justified additions. Edge cases: 360–1920 intermediate widths, wrapping, image masks/loading, keyboard navigation, menu, forms/errors, carousel states, contrast and reduced motion. No extra framework or plugin is needed.

## Initial state

Docker was already running despite the task's dated startup note. Read-only audit: WordPress 7.1, PHP 8.3.33, child theme over Hello Elementor, Elementor/Fluent Forms/Novamira/Rank Math active, pretty permalinks enabled. WP-CLI reports environment `production` even though URL is localhost: the CLI service lacks the web service's `WORDPRESS_CONFIG_EXTRA`. Investigate and correct local runner context before page writes; do not interpret this as a remote site. Novamira emits three existing missing-ability registration errors in CLI; retain this baseline evidence and inspect browser impact.

Resolved CLI environment mismatch by matching the web service configuration. Saved `local/backups/pre-design-parity-20260909.sql`. Baseline55 full-page screenshots: zero capture/overflow/console/asset failures. Lighthouse baselines saved per route; desktop accessibility94–100, best practices100. Local noindex depresses SEO scores intentionally. `main` not checked out or modified.

## Handover (2026-09-09, `claude-opus-5` -> Codex)

Codex hit its usage limit after page 1 (global chrome) with that work staged but
uncommitted. Claude Code took over as implementer, keeping Codex's conventions.

**Committed on `feature/setup`** — Codex's checkpoint was split into three
reviewable commits rather than one blob, so each can be audited or reverted
independently:

| Commit | Author | Contents |
|---|---|---|
| `chore: extend visual sweep harness and capture design parity baselines` | Codex | `tests/visual/sweep.mjs`, `local/iterate.ps1`, compose env fix, all `QA/baseline/` |
| `feat: replace reference photo crops with licensed source imagery` | Codex | `tools/06-media.php`, `local/media-staging/`, image brief/prompts/sources |
| `feat: match global header and footer to reference` | Codex (patch by `claude-opus-5`, reviewed by Codex) | theme shell, logo lockup, footer, drawer, `QA/design-review/global-chrome.md` |
| `fix: match home hero and trust strip to reference` | `claude-opus-5` | this session's Home work |

**What Codex should check first**, since these are the judgement calls most
worth a second opinion — all flagged `[claude-opus-5 — review]` in
`QA/design-review/home.md`:

1. **The scaling convention.** The H1 is now 79px at 1920, derived from glyph
   width against the reference's own line width. An earlier ascender-based
   estimate gave 86px. If Codex reads `Home.jpeg` as a native 1307px viewport
   render rather than a downscaled 1920 capture, the whole type scale changes.
2. **`--eqc-content-max` 1400px -> 1708px**, a site-wide token affecting all ten
   pages, derived from the hero's own bounds in `Home.jpeg` (1644px content
   column). The trust panel gives 1620px; the 24px difference is treated as the
   panel's own inset. `DESIGN.md`'s 1240px is now superseded twice over.
3. **The keel arch.** `lib/arches.mjs` gained `cuspedArchPanel()` — a measured
   three-lobe profile, verified against the trace at RMSE 4.19px (0.75% of arch
   width) by `tools/graphics/scratch/fitcheck.mjs`. The existing
   `scallopedArchPanel` semicircle construction does NOT fit this reference and
   the reasons are recorded in the function's comment.
4. **`divider-rule.svg`** is a new asset rather than a second terminal on
   `divider-accent.svg`, because that one is also consumed by
   `eqc_pricing_card()` and pricing has not been reviewed. Merge them if the
   pricing pass finds the same symmetric form.

**Resume point:** Home sections 3-10, then line 14 (Courses). `main` untouched.


## Scale system note (2026-09-09, `claude-opus-5`)

`--eqc-u` in `tokens.css` now drives every size token. A value measured off a reference image is written `calc(<native px> * var(--eqc-u))` and is correct at every width — which is what makes the remaining seven sections a transcription job rather than seven rounds of guess-and-check.

**This touches every page, not just Home.** The other nine pages inherit the new scale, have changed appearance, and are NOT re-reviewed. Expect to re-check them after Home is finished.

## Canvas correction pass (2026-09-10, `claude-opus-5`)

The 1600-canvas recalibration (`2486a90`) was half wrong. Its vertical
diagnosis was right and stands; rebasing the horizontal unit was not, because
every transcribed value in the theme is a native pixel on the 1307 canvas.
`--eqc-u` is back on that basis and every type token was rescaled to hold the
sizes the previous pass measured. Content is 85.6% of the viewport again at
every width — the user-reported "squeezed, extra padding left and right".

Worked this pass, each measured against its own reference at 1600x900:
**Courses** (landscape cards, 1.27 vs 1.30), **Teachers** (818 vs 923),
**Reviews** (910 vs 862), **Pricing** (934 vs 906). Section headings restored
to the six measured sizes of LESSONS #29. Icon call sites rewired to the
rebuilt sprite and all nine pages re-baked.

Evidence: `QA/after/parity/` — 0 failures, 0 warnings over 9 routes x 8
viewports plus a 380-1900px scan.

**Still open:** About (1095px) and the Courses section height (1251px) carry
the same spacing excess the three named sections had and were not worked;
`--eqc-section-space`, the eyebrow block height and the section padding are
the shared levers. Pages 2-10 inherit the corrected scale but are not
individually re-reviewed.

---

## Client review pass — 2026-09-10 (`claude-sonnet-5`)

Executed the 22-item client review in `QA/qa-9-10.md` (annotated shots in
`QA/qa-10092026/`). Progress + per-task notes: `QA/qa-9-10-progress.md`.
Plan: `~/.claude/plans/prancy-wiggling-key.md`.

**Icons** (`build-icon-sprite.mjs`): `certificate` / `megaphone` / `clock`
strokes lightened (own `stroke-width` on the paths, family stays 2.1);
`phone` moved from `FILLED_UI` to `ORIGINAL_OUTLINE` with the client-picked
line drawing; every `book-open` call site swapped to `rehal-quran` (archive
eyebrow -> `megaphone`, free-trial form step -> `clipboard-check`). Sprite
regenerated; all 9 pages re-baked for the call-site changes.

**Ornaments** (`gen-ornaments.mjs`): `keel-arch-frame.svg` is now a single
even stroke open at the bottom (drop the closing `Z` from the cusped panel) —
serves the hero and the About page, whose two images moved from
`--arch-media--masked` to `--keel`. New `pricing-banner-mask.svg` (solid
horizontal cusped cartouche) drives the green "N Days/Week" banner shape.

**CSS**: hero arch rises over the nav (header background transparent at rest,
cream on `.is-scrolled`; media column `margin-block-start: -92u` >=900px);
trust panel de-padded; mobile hero chips forced 2-up and de-padded (media
query moved below the base rule — LESSONS #50); pricing RECOMMENDED tab
re-anchored inside the card corner, banner divider removed, feature list up
to body size, hover reduced to a clean vertical pop (no icon rotate / arrow
drift; `motion.css` reduced-motion updated); testimonials band given its own
`#F1EBDF` ground, avatars centred with `left:50%+translateX`, corner motif on
both card tops at 0.32; About girih finer + left-weighted; teachers page 6
cards 3x2 with a mobile scroll-snap row; course cards centred with equal
gaps; the "choose a course" rows now link to `#course-NN` anchors on the
cards (`eqc_course_card()` gained an `$anchor` param -> `_element_id`); footer
stat circle enlarged + overlapping the avatars, its girih watermark darker
and golden; corner-ornament depth via mask drop-shadows with the tint carried
in the paint alpha (LESSONS #51); `--blog` / `--2col` / `--trust` grids moved
off `auto-fit` to explicit counts (DESIGN.md §7 now carries the symmetry
rule). Home page gained a 6-item FAQ section after the blog preview
(`eqc_faq_group()` + "View All FAQs" -> `/faq/`).

**Copy**: every user-visible em/en dash removed across `tools/pages/*.php`,
`tools/04-seo-meta.php` and `footer.php`, plus the "not one more app to
abandon" / "not the other way around" style tells (LESSONS #53).

**Evidence** `QA/after/`: 8 routes x 4-5 viewports (1920/1280/768/430/390),
0 console / page / asset errors, no horizontal overflow, one H1 per route,
FAQ accordion + course-anchor links click-tested. `style.css` 1.3.0 -> 1.4.0.

**Not pixel-tuned:** the About collage geometry vs `Home2.jpeg`, and the
pricing banner's cartouche lobe depth vs `pricing.jpeg` (shape family right,
lobes subtler). No git commit — awaiting client review.

## Client re-review, round 5 — 2026-09-10 (`claude-sonnet-5`)

The client re-reviewed round 4 and found several items implemented wrongly
rather than left undone. New annotations `9.png`–`14.png` in `QA/qa-10092026/`.
Full item-by-item status: `QA/qa-9-10-progress.md`.

Three round-4 items were ticked on the strength of the edit and were measurably
still broken:

- **Course/pricing card links had zero area.** `.eqc-card--course > *` and
  `.eqc-card--pricing--featured > *` set `position: relative` on the Elementor
  HTML-widget wrapper, which then became the stretched anchor's containing
  block and collapsed to 0px tall. Every course card measured 413×0 and
  `elementFromPoint()` at the centre returned a `<p>`. Same root cause put the
  featured card's medallion 38px low and its RECOMMENDED tab 49px outside the
  card. Fixed with explicit widget classes (`eqc-card-link-widget`,
  `eqc-pricing-anchor-widget`) that are stretched instead.
- **Keel arch jambs rendered at half weight.** The generated frame ran its
  verticals along the viewBox edge, so half of each stroke was clipped —
  measured 3px jambs against a 4px crown. Fixed by padding the generated
  viewBox *and* growing the masked pseudo-element by the same ratio; a mask
  raster now measures left == right == the 4.25px nominal.
- **The hero arch had been moved, not overlapped.** A −135px margin put its top
  at y=−40 with the tip off-screen, behind the nav. Restored to its natural
  position with `z-index: 120` + `pointer-events: none`, and the header takes
  `z-index: 200` once scrolled.

Also this round: the featured pricing card dropped `scale(1.07)` to match
`Assests/pricing.jpeg` (all four cards the same size — this is what put every
row back in line); pricing list/divider centred; "Month" pill solidified; gap
to the benefits strip 6u→14u; mobile hero gap 2px→40px; home teachers row got
the snap-scroll it was supposed to have; review divider 90px→~147px; footer CTA
mesh now a composited gradient fade instead of a hard-edged 44% band; a
rounded-foot keel variant for the About contexts only; course-card dash removed
and the level restyled gold-700; teachers-page step cards given a real
`.eqc-card--step`; WhatsApp buttons take brand `#25D366` on hover.

Evidence: `QA/after-r5/` — 9 routes × 1920/1280/768/430/390 plus a
380–1900:40 overflow scan. 0 console errors, 0 page errors, 0 failed requests,
0 horizontal overflow, one H1 per route, 0 images missing alt. Interaction and
geometry claims were each verified with their own assertion (hit-testing, rect
comparison, mask rasterisation) rather than by re-reading the CSS —
`QA/LESSONS.md` #54–57.

Theme version 1.4.0 → 1.5.0. Not pixel-matched: the About collage geometry vs
`Home2.jpeg`, and the pricing banner's cartouche lobe depth vs `pricing.jpeg`.

## Client re-review, round 6 — 2026-09-10 (`claude-sonnet-5`)

Six further items after round 5 (`QA/qa-10092026/15.png`–`20.png`). Full
item-by-item status: `QA/qa-9-10-progress.md`.

Each was a leftover from an earlier edit rather than something never attempted:

- **Pricing dead band.** Two independent "pin to the bottom" rules — the price
  divider's own `margin-block-start: auto` plus the shared
  `.eqc-card__foot { margin-top: auto }` — stacked inside a 343u min-height
  card and left 84px of measured nothing. Fixed by distributing with
  `justify-content: center` instead. Now 16px, with the block sitting 63/47px
  off the card's top/bottom, all four cards still row-aligned.
- **RECOMMENDED overflowing.** The base rule is the old diagonal ribbon with a
  fixed `width: 8.5rem`; round 5 changed its padding, position and radius but
  not its width, so a 158px label sat in a 136px nowrap box. `width: auto`.
- **Hero tip clipped.** The point sat 14px above the nav bar's lower edge — in
  the bar's own band, invisible at rest but sliced flat once `.is-scrolled`
  made the header opaque. Dropped the media column 20u; the tip now clears the
  bar by 16px.
- **Photo not meeting the frame.** A `mask-size: 95.7% 96.18%` inset left over
  from when this frame was a double line; centred, the slack landed unevenly
  and the foot stopped 20px short. Now `100% 100%` — within 3px all round.
- **About keel frame's missing base.** The open-bottom `.replace(/Z$/,'')` was
  applied to the rounded variant too; only the hero was meant to lose its base.
- **About collage.** Main panel 72% → 88% so it anchors the group as
  `Home2.jpeg` draws it, and the section — which carried `--ornamented` but
  never emitted the motif spans — now gets `tl`/`br` corner arabesques via a
  new `$corners` argument on `eqc_section_ornaments()`, with the girih lattice
  and keel watermark strengthened to match the reference.

Also: pricing body copy up ~9%, banner given deeper lobes (a new `lobeDepth`
option on `closedCartouche()`, 1.85 for the banner only) plus a gradient and
mask-following drop-shadows for real relief, and the About page's ticked list
replaced by the home page's icon chip cards in a new 3-up row.

Evidence: `QA/after-r6/` — 9 routes × 1920/1280/768/430/390 plus a 380–1900:40
scan. 0 console errors, 0 page errors, 0 failed requests, 0 horizontal
overflow, one H1 per route, 0 images missing alt. Theme 1.5.0 → 1.6.0.

## Client follow-ups, round 7 — 2026-09-10 (`claude-sonnet-5`)

Seven further items raised while round 6 was in flight (`QA/qa-10092026/21.png`
plus inline requests). Full detail: `QA/qa-9-10-progress.md`.

- **"Call Any Time" was a dead link.** Its `tel:` href was assembled from the
  `eqc_phone_display` theme mod, which is an unset placeholder here, so it
  resolved to a bare `tel:`. Repointed at WhatsApp on client instruction.
- **Featured plan emphasis.** With the card no longer enlarged, the frame is
  the emphasis: a 2px gold border plus an offset gold outline (both paint
  outside the border box, so the four cards stay dimensionally identical), and
  a brighter sparkle field carried in the paint's alpha with a soft bloom.
- **About collage.** Keel arch made taller (559/629 → 559/706) and pushed right
  so the child cartouche lands over its left side. The margin that was supposed
  to do this was being zeroed by Elementor's own `figure { margin: 0 }` reset,
  which scores (0,4,1) against our (0,2,0) — offset with `left` instead.
- **Background watermark.** Anchored to the section floor and run past it so
  its own base line is clipped away rather than drawn.
- **Courses corner ornaments.** They were flush to the corners of an
  `overflow: hidden` section while a single shared drift keyframe pulled every
  motif in the same direction — clipping the top-right one for most of its
  cycle. Now inset beyond the drift amplitude, with per-corner keyframes
  drifting along their own diagonals.
- **Pricing spacing.** Feature-list gap 15u → 7u; card top padding to 44u so
  the banner clears the medallion.

Evidence: `QA/after-r7/` — 9 routes × 5 viewports plus a 380–1900:40 scan, all
clean. Theme 1.6.0 → 1.7.0.

## Round 8 — ornament depth, cleanup, full-site QA — 2026-09-10 (`claude-sonnet-5`)

The first full-coverage QA pass: **11 routes × 8 viewports** plus the
380–1900:40 width scan, where every previous round ran 5 viewports over 9
routes and never captured `/blog/` or a single-post permalink at all. Clean on
every check. Item detail: `QA/qa-9-10-progress.md`.

**Ornament depth.** The pricing section's corner frame had never received the
round-4 fix its sibling got: `opacity: 0.5` on top of a single `drop-shadow`
meant an effective ~0.14 alpha (measured 5.6 luminance units of darkening).
The first correction over-shot — a heavy shadow stack turned the ornament grey,
because a *pierced* girih panel casts shadow through every hole, so the cream
tiles read as raised when the reference has the gold rules proud. Settled on
`opacity: 0.62`, one tight contact shadow, a light bevel, and a contrast/
saturate lift that sharpens the rules themselves. Background layers came down
with it at the client's request (keel watermark 0.11→0.07, girih 0.13→0.10,
corner motifs 0.32→0.26).

**Corner ornaments now meet the card corner.** They were tiling a lace pattern
from the SVG origin onto a canvas that is not a whole number of tiles wide, so
the dense corner fell in a tile void — 7px of empty canvas, alpha 0 at the
corner pixel. Fixed by phase-shifting each asset (found by sweeping and scoring
ink at the corner; the right fraction differs per tile size).

**Favicon.** The tab icon was missing because `site_icon` is 0 *and* nothing in
the theme emitted icon tags — the assets existed but were never linked. Also
`favicon.svg` was generated 770×692, which browsers stretch into their square
slot; it is square now.

**Two generator bugs surfaced.** `build-logo.mjs` used CWD-relative input and
output paths, so running it from `tools/graphics` wrote a stray
`tools/graphics/wp-content/…` tree and never touched the theme. And both
generators read gitignored build inputs that a fresh clone lacks, dying on a
raw ENOENT; they now name the trace command to run.

**Cleanup.** QA evidence 271MB → 14MB and `tools/graphics/scratch/` 67MB →
682KB, keeping every JSON report and Lighthouse baseline, the client's
annotated review images, and the icon-parity crops CLAUDE.md cites. Docs that
referenced pruned screenshots were corrected in the same pass. `.gitignore` now
excludes sweep PNGs, so a QA round costs ~40KB instead of ~100MB. Dead
`.eqc-pricing-divider` and `.eqc-cta-ornament` rules removed; `debug.log`
deleted. Theme 1.7.0 → 1.8.0.
