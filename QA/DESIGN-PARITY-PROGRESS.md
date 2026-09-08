# Design parity execution

Local target: `http://localhost`; branch: `feature/setup`. Supervisor: Codex; bounded harness assistant: Claude Code `claude-opus-5`. Existing user reference-image replacements and `local_SS/` are preserved.

## TODO / reuse / verification

- [x] Read task, project rules, design system, graphics pipeline and setup sections completely.
- [x] Inspect branch, working tree and installed local tooling.
- [x] Inspect local Docker and read-only WordPress state.
- [x] Extend existing screenshot harness; create one-command page rebuild/flush/sweep loop (PHP/JS syntax verified; first actual rebuild verification pending).
- [x] Capture all routes at 1920/1440/1024/768/390 before presentation changes, including Lighthouse baseline.
- [x] Global chrome: measured review, reuse plan, implementation, iterations, responsive/a11y/performance evidence (checkpoint being recorded).
- [~] Home: hero + trust strip measured, implemented, iterated and verified (`claude-opus-5`). Sections 3-10 (about, courses, pricing, teachers, testimonials, blog, final CTA) still to review against their own reference images.
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
