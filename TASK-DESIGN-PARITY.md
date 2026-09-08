# TASK-DESIGN-PARITY — Reference-Exact Redesign of Easy Quran Classes

**Audience:** Codex CLI, working in this repository.
**Goal:** make the built site match the client's reference design in `Assests/`
*exactly* — layout, spacing, shapes, ornament, typography, colour, imagery
treatment and component anatomy — one page at a time, each preceded by a
written close review and followed by measured verification, and each **iterated
until it matches** rather than attempted once.

This is not a "make it look nicer" task. It is a **parity** task. The
reference is the specification. Where the current build and the reference
disagree, the reference wins unless a rule in §2 forbids it.

---

## 0. Read before you do anything

Read completely, in this order. Do not skim, do not start work partway
through:

1. `CLAUDE.md` — architecture, environment, branch policy, WP-CLI rules
2. `AGENTS.md` — your own operating notes for this repo
3. `DESIGN.md` — the design system (tokens, scale, spacing, components)
4. `.claude/rules/*.md` — implementation / design / git / security / wordpress
5. `tools/graphics/README.md` — the SVG asset pipeline and its two hard gotchas
6. `README-SETUP.md` §3, §5, §7 — bootstrap, Codex setup, known issues
7. `QA/LESSONS.md` — if it exists (see §8); it is the accumulated record of
   what already went wrong on this task

Then read every file you are about to change *before* changing it. This
codebase has non-obvious build-time-vs-runtime semantics (§4) that will
silently eat your work if you guess.

Before you start Phase 0, read **§4A** and actually set your tooling up — the
skills, the MCP servers, the screenshot harness and a one-command rebuild
loop. This task is a long series of measure-change-remeasure cycles; how fast
that cycle runs determines whether you finish it well.

---

## 1. Mission statement

The client supplied a finished visual design as flat images. A first-pass
implementation exists and is structurally close but **visually approximate** —
it reads as "inspired by" the reference rather than "is" the reference. Your
job is to close that gap to the point where a side-by-side of a local
screenshot and the reference image shows no meaningful difference in:

- section order, section rhythm and vertical spacing
- container widths, grid columns, gutters and gaps
- every ornament: its exact shape, size, stroke weight, colour and placement
- every card's internal anatomy (badge position, divider, button shape/place)
- type: family, size, weight, line-height, line-break points, colour splits
- the copy itself — same words, same capitalisation, same line breaks, because
  string length is what produces the geometry above (§2)
- the exact arch/mask silhouette used on every photographic element
- button geometry: height, radius, padding, icon treatment, colour
- shadow depth and border colour
- the floating/overlapping relationships (header, trust strip, avatars,
  badges) that give this design its character

"Close enough" is the failure mode here. Be exact.

---

## 2. Hard constraints — violating any of these fails the task

**Content: use the reference's own text, verbatim.**
Design parity is the first priority, and **content is part of the layout** —
string length is what determines where a heading breaks, how tall a card
grows, whether three columns balance, and how wide a price sits next to its
`Month` pill. Substituting shorter or longer text silently destroys the very
geometry you are trying to match. So:

- **Transcribe the reference's copy exactly** — every heading, eyebrow,
  sub-label, body paragraph, feature row, price, stat, name, location, button
  label and footer line, with the same capitalisation and the same line
  breaks.
- **Where the reference doesn't show content** (a page with no reference
  image, an expanded body paragraph cut off in the composite, a nav
  destination), write **dummy content in the same register and roughly the
  same length** as its neighbours. Realistic filler that holds the layout —
  not `Lorem ipsum`, and not a short stub where the design needs three lines.
- Do not "improve" the reference's wording, shorten it, or swap a specific
  claim for a vague one. `20+ Countries` stays `20+ Countries`; `$39` stays
  `$39`.

**The safeguard is a launch gate, not a design compromise.** Several of these
strings are unverified business claims — `5,000 students`, `10 / 20+
countries`, `$39 / $45 / $59 / $69`, `+1.5K Happy Students`, `7+ / 5+ / 6+ /
4+ Years Experience`, `Tajweed Certified`, the four teacher names, the three
testimonials and their authors/locations, `Random Address, USA 733898`,
`(406) 555-0120`. They are fine as staging content on the local build; they
must not reach the public site unconfirmed (`CLAUDE.md` → Content Integrity,
Production Gate). Therefore:

- Log **every** unverified claim in `QA/PLACEHOLDER-REGISTER.md` (create if
  absent): the page and section, the exact string as shipped, which reference
  image it came from (or "dummy — invented to hold layout"), and the precise
  question the client must answer to confirm or replace it.
- Keep each one a **single, obvious edit** — a value in a page script or a
  helper argument, never a number baked into three places or into CSS — so
  replacing it later is trivial and can't be missed.
- Note in the register the ones that are structurally load-bearing (prices,
  the four plan tiers, teacher count, testimonial count), since replacing
  those changes layout and needs a re-verify pass.
- Deployment to production stays blocked on the client clearing this register.
  That gate is `TASK-DEPLOY.md`'s problem, not this task's — do not let it
  slow the design work down.

**Environment.** All work is local-first against the Docker WordPress at
`http://localhost`. Production (`https://easyquranclasses.com`) is **never**
the design workspace and must not be touched by this task. Hosting has no
shell/SSH — do not probe for it.

**Branch.** Work on `feature/setup` only. `main` is production-deployment-only
and must not be checked out, merged into, or touched by this task.

**Never.** Modify WordPress core. Edit the Hello Elementor parent theme. Patch
third-party plugin source. Hand-edit `_elementor_data` via SQL or
`wp post meta update`. Run raw SQL search/replace over serialized data. Print,
log or commit any credential. Add an Elementor addon pack or a second CSS
framework.

**Reuse before you write.** §4 lists what already exists. For every new
helper, CSS class, SVG or icon you are about to create, first search for an
existing one that does the job. If you create something new, state in your
findings doc why the existing thing could not be reused. Minimal code that a
human can maintain — not clever code, and not duplicated code.

---

## 2A. Where you may — and should — improvise

Parity is the goal, but the reference is a set of flat desktop images, not a
finished product. It is silent on a great deal, and in a few places it is
simply weaker than it should be. Fill those gaps with judgement rather than
shipping a hole.

**Improvise freely where the reference says nothing.** It shows no hover,
focus, active, loading, disabled, empty or error states; no mobile or tablet
layout; no menu drawer; no form validation; no 404 or search results; no blog
single-post layout; no `prefers-reduced-motion` behaviour; no scroll or reveal
motion. Design all of these yourself, in the reference's own visual language,
using the tokens and components it establishes. `DESIGN.md` governs here.

**Improvise where the reference would actively harm the site.** If matching it
exactly would break accessible contrast, drop below a 44×44px tap target,
require an unlabelled form field, produce a heading order that makes no sense,
force a fixed height that clips real content, or hurt Core Web Vitals — adjust
it. Accessibility, semantics and performance outrank pixel fidelity. This is a
narrow exception, not a general licence: it covers the handful of places where
matching the reference would produce a genuinely broken page, and each one must
be logged as an `Improvised` departure with the specific standard it protects.
It does not extend to the reference's *copy* — §2 governs there, and the
reference's text ships verbatim.

**Add what a real site needs and a mock-up forgets.** Skip links, focus rings,
`aria-current` on the active nav item, real alt text, a working mobile menu,
sensible empty states for the blog and testimonials carousel, keyboard-operable
carousels, and honest microcopy where the reference used filler.

**Improve only with a reason you can state.** This is not licence to redesign.
Do not restyle something merely because you would have drawn it differently, do
not add sections the client didn't ask for, and do not introduce a new visual
motif the reference doesn't contain. If you catch yourself preferring your own
idea over the reference on aesthetic grounds alone, the reference wins.

**Log every departure.** Anything you add, change or deliberately don't match
goes in the page's findings doc under a clear `Improvised` heading: what the
reference showed (or didn't), what you did, and why it is better. The user must
be able to review each one and reverse it in a single, obvious edit.

---

## 3. The reference set

`Assests/` — the client's design. Treat each file as the authority for the
pages/sections it covers:

| File | Covers |
|---|---|
| `Website Layout.png` | **Full-page composite** — the master for section order, vertical rhythm, and how sections relate. Start every review here for global context. |
| `Home.jpeg` | Header, hero, trust strip — high enough resolution to read spacing and ornament detail |
| `Home2.jpeg` | About / "shouldn't depend on where you live" — image collage, stat row, dual CTA |
| `courses.jpeg` | Courses grid — card anatomy, numbering, ornament dividers |
| `pricing.jpeg` | Pricing — plan cards, ribbon banners, recommended treatment, benefits strip |
| `Teachers.jpeg` | Teachers — split layout, oval portraits, overlapping badge, meta rows |
| `Reviews.jpeg` | Testimonials — overlapping avatar, quote badge, inner feature strip, dots |
| `Blogs.jpeg` | Blog/news — date chip, category, carousel controls |
| `End.jpeg` | Final CTA panel + full footer + copyright bar |
| `Assests/Logo/Logo2.jpeg` | **The approved logo** (forest green). The other three colourways are rejected drafts — ignore them. |

Read these images at full resolution. If your tooling downsamples an image,
crop and re-read the region you are inspecting rather than guessing from the
thumbnail. **Detail you cannot see, you cannot match.**

---

## 4. The architecture you must work within

Do not invent a parallel system. This is what exists and what owns what:

**Page content — `tools/pages/*.php`**
The only sanctioned way to change a page's Elementor content. Each script
composes elements via helpers and calls `eqc_save_elementor_page()`.
Existing: `10-home`, `11-about`, `12-courses`, `13-teachers`, `14-pricing`,
`15-contact`, `16-faq`, `17-free-trial`, `18-kids`.

**Element factory — `tools/elementor-helpers.php`**
Already provides: `eqc_section` `eqc_inner` `eqc_container` `eqc_widget`
`eqc_html` `eqc_heading` `eqc_text` `eqc_image` `eqc_button` `eqc_icon_button`
`eqc_chip` `eqc_media_id` `eqc_page_id` `eqc_icon_str` `eqc_section_ornaments`
`eqc_section_heading_el` `eqc_course_card` `eqc_teacher_card`
`eqc_pricing_card` `eqc_testimonial_card` `eqc_trust_tile` `eqc_carousel`
`eqc_page_hero` `eqc_faq_item_html` `eqc_faq_group` `eqc_save_elementor_page`.
**Extend these** to match the reference; do not write a second card function
beside an existing one.

**Presentation — `wp-content/themes/easy-quran-classes-child/assets/css/`**
`tokens.css` (84 lines, the variables) → `shell.css` (271, header/footer/page
frame) → `components.css` (1474, everything else) → `motion.css` (118).
Colour, size and spacing values belong in `tokens.css` as variables; component
rules consume them. Do not hardcode a hex that duplicates an existing token.

**Runtime template code — `inc/template-tags.php`**
`eqc_icon` `eqc_get_svg_asset` `eqc_divider_svg` `eqc_logo_mark_svg`
`eqc_section_heading` `eqc_render_blog_cards` `eqc_whatsapp_url`
`eqc_reveal_attrs`. Plus `header.php` / `footer.php` / `single.php` /
`archive.php` / `home.php` / `page.php`.

**Icon sprite — `inc/icon-sprite.php`**, generated by
`tools/graphics/build-icon-sprite.mjs` from Lucide + Simple Icons. Current
symbols: `person users calendar chart-up shield headset globe certificate
book-open mail map-pin phone arrow-right check graduation-cap quote plus
chevron-down menu close star clock sparkle chevron-right monitor-play whatsapp
facebook twitter instagram youtube star-8`.
Need another icon? Add it to the generator and regenerate — never paste a
one-off inline SVG into a page script.

**Vector assets — `tools/graphics/gen-ornaments.mjs` + `lib/geometry.mjs` +
`lib/arches.mjs`**, output into `assets/svg/`. Built from real closed-form
geometry (girih 4.8.8 tiling, hex tessellation, quatrefoil, rosettes, and an
arch family with tangent-continuous joins). Current output includes
`arch-*`, `fourcentred-*`, `horseshoe-*`, `mandorla-*`, `multifoil-arch-*`,
`quatrefoil-*`, `mihrab-finial-*`, `girih-lattice-*`, `hex-tessellation`,
`rosette`, `rosette-12`, `star-8-filled`, `corner-frame`, `corner-motif`,
`lattice-corner`, `sparkle-dust`, and `divider-{section,card,accent,dot,
eyebrow,medallion}`.

### Two gotchas that will silently destroy your work

1. **`--user=1` is mandatory** on every `wp eval-file` run. Without it the
   command still prints `Success: Saved Elementor content for post #N` and the
   save **does not take**. Verify a save landed by re-querying
   `wp post meta get <ID> _elementor_data` and grepping for a distinctive
   string you just introduced — do not trust the success line.

2. **Divider/rosette SVGs are embedded as literal HTML at build time.**
   `eqc_divider_svg()` / `eqc_get_svg_asset()` snapshot the SVG's *file
   content* into `_elementor_data`. Regenerating the SVG does **not** update
   already-saved pages. After regenerating any such asset you must re-run
   **every** `tools/pages/*.php` (not just the page you think is affected —
   `eqc_section_heading_el()` embeds a divider and is used site-wide), then
   `wp elementor flush-css`.
   Assets consumed by CSS `mask-image` (`arch-mask`, `girih-lattice-*`,
   `corner-frame`, …) are read fresh by the browser and need no re-bake.

---

## 4A. Tooling — use what's already wired up, and iterate fast

You have a lot more than a text editor here. Using it is not optional: a
parity task is won by *measuring and re-measuring quickly*, and every tool
below exists to shorten that loop. Reach for the right one rather than
guessing from source code.

### Skills

`tools/codex/setup-codex.ps1` syncs this repo's skills into
`~/.codex/skills/` as `eqc-*`. Run it (or `-Verify`) if they're missing.
Invoke the relevant skill **before** the work it covers, not after:

| Skill | Use it for |
|---|---|
| `eqc-implementation-workflow` | Every page. Enforces the reuse-first / TODO / test / cleanup discipline this task requires. |
| `eqc-elementor-build` | Building or restructuring page sections in Elementor Free. |
| `eqc-visual-qa` | The responsive + visual sweep at the end of each page. |
| `eqc-wp-cli-safe` | Any WP-CLI invocation, especially the `eval-file` runs. |
| `eqc-wp-audit` | Establishing local WordPress/plugin/theme state before you change it. |
| `eqc-wordpress-debug` | When something renders wrong and you don't yet know why. |
| `eqc-performance-audit` | Verifying you haven't regressed LCP/CLS with new imagery or SVG. |
| `eqc-seo-review` | Heading hierarchy, alt text, metadata after content changes. |
| `eqc-git-checkpoint` | Each per-page commit. |

Not relevant to this task: `cpanel-audit`, `release-check`, `backup-verify`,
`plugin-evaluation` (unless you propose installing something — then use it).

### MCP servers

Registered globally by `setup-codex.ps1` in `~/.codex/config.toml`:

- **`chrome-devtools`** — your primary measuring instrument. Use it to read
  real computed values off the rendered page (`evaluate_script` +
  `getComputedStyle`/`getBoundingClientRect`), take full-page and element
  screenshots, emulate viewports and DPR, watch the console and network for
  404s on your new SVGs, run Lighthouse, and capture performance traces.
  **Measure with this rather than inferring spacing from CSS source** — the
  cascade, Elementor's own output and the parent theme all get a vote.
- **`context7`** — current documentation for anything you're unsure of:
  Elementor's data structures and widget schemas, WordPress APIs, SVGO
  plugins, potrace options, Playwright's API. Prefer it to recalling from
  memory; these libraries move.
- **`playwright`** — the interaction counterpart to `chrome-devtools`. Use it
  for the §9 accessibility pass: accessibility-tree snapshots, tab order and
  focus rings, form fill and submit on the trial/contact forms, and multi-tab
  flows. It runs `--isolated`, so it never touches your own Chrome session.
- **`novamira-localhost`** — REST-first inspection and content operations
  against the local WordPress. Handy for querying post/meta state (e.g.
  confirming an `_elementor_data` save actually landed, per §4 gotcha 1)
  without shelling into a container. Local-only; never point it at
  production. `tools/codex/novamira-mcp.cmd` supplies its credential from
  `local/.env` — never put that value anywhere else.

**Codex specifics that will trip you up** (all documented in `AGENTS.md`):
MCP tools here are deferred behind tool-search, so you must *search* for a
tool by name to get its schema — listing won't surface it. Docker is
unreachable inside both sandbox modes, so Docker and WP-CLI commands need to
run approved outside the sandbox. `npx.ps1` is execution-policy blocked, which
is why MCP servers launch as `cmd /c npx`.

### Local harnesses — extend these, don't rewrite them

- **`tests/visual/sweep.mjs`** (project-local Playwright 1.63) already
  screenshots a URL across the `DESIGN.md` §21 viewports —
  1440/1280/1024/768/430/390/360 — and flags horizontal overflow. **Extend
  it** for this task: add the reference's own 1920 width, let it take a list
  of routes, and have it write into `QA/baseline/` and `QA/after/<page>/`.
  Do not start a second screenshot script beside it.
  From Git Bash, prefix route arguments with `MSYS_NO_PATHCONV=1` (its own
  header explains why).
- **`tools/graphics/`** — `potrace` (tracing), `svgo` (optimisation),
  `lucide-static` + `simple-icons` (the icon sources), `pngjs`. `pixelmatch`
  is present transitively under `node_modules`; if you use it for the
  trace-vs-rebuild overlay check in §5.1, add it to `package.json`
  explicitly rather than relying on hoisting.
- **WP-CLI** — `local/wp.ps1 <args>` from PowerShell, or
  `docker compose -f local/docker-compose.yml --env-file local/.env run --rm
  wpcli <args>`. Same `MSYS_NO_PATHCONV=1` rule for `/`-leading paths.

### Web access

Use it deliberately: to study how a geometric construction actually works
before rebuilding it, to check a licence, to source an icon or photo per
§5.2, or to read documentation `context7` doesn't cover. Don't browse
aimlessly mid-iteration — it's slower than measuring the page in front of you.

### Make the loop fast

The single biggest time sink available to you is a slow rebuild cycle. Set it
up properly once, at the start:

- Leave the containers running for the whole session; don't tear down between
  iterations.
- Script the rebuild as one command — re-run the page script with
  `--user=1`, `wp elementor flush-css`, then the sweep — so an iteration is a
  single invocation, not five remembered steps.
- Batch the full `tools/pages/*.php` re-run into that same script for when a
  divider regeneration forces it (§4 gotcha 2).
- Diff screenshots programmatically where you can, so "did that actually
  change anything?" is answered in seconds rather than by eye.
- Keep a browser page open on the section you're working and reload it,
  instead of re-navigating from the homepage each time.

Fast iteration is what makes "iterate until it matches" (§8) achievable rather
than exhausting. Invest the first twenty minutes in the loop; it pays back
across every page.

---

## 5. Graphics and imagery — generate, source, or brief

Be honest about this split. Do not fake it in either direction.

### 5.1 What you generate yourself (preferred — always try this first)

Every vector element in the design. Ornaments, arch silhouettes and masks,
dividers, rosettes, medallions, corner frames, badge and ribbon shapes, the
numbered plan badges, the gold-rule-with-diamond-terminals, the girih and hex
background patterns, the icons, and all four logo variants. These are SVG,
resolution-independent, and belong in `tools/graphics/` → `assets/svg/`.

A generated shape you control beats a downloaded one you don't: it inherits
`currentColor`, scales cleanly, costs no licence, and can be tuned to the
reference exactly. Only go to §5.2 when this genuinely can't get there.

#### The method: trace to learn the truth, then rebuild it cleanly

Do not eyeball these shapes, and do not ship a raw bitmap trace either. Use
both steps, in order — this is exactly how the existing logo and rosette
assets in this repo were made (`trace-logo.mjs` → `build-logo.mjs`,
`trace-rosette.mjs` → `gen-ornaments.mjs`):

1. **Extract ground truth from the reference.** Crop the shape out of the
   reference image **at its native resolution** and trace it (potrace is
   already a dependency; `trace-rosette.mjs` is the working example) into
   `tools/graphics/scratch/`. That gives you the real silhouette, the real
   proportions, the real point count, the real stroke weight — measured, not
   guessed.

   **Do not upscale the crop before tracing.** `tools/graphics/README.md`
   records this the hard way: upscaling then re-thresholding bakes a jagged
   edge into the bitmap, which potrace then faithfully reproduces as thousands
   of tiny segments. Tracing at native resolution gave ~100 clean cubic curves
   instead. Crop tightly, threshold carefully, trace once.

2. **Rebuild it as real geometry fitted to that trace.** The trace is a
   reference measurement, not the deliverable. Derive the shape's actual
   construction — how many points on the star, what the polygon's `{n/step}`
   is, where the arc centres sit, what the tangent condition at each join is —
   and express it in `lib/geometry.mjs` / `lib/arches.mjs` as closed-form
   trigonometry, the way every existing ornament is built. Then emit it from
   `gen-ornaments.mjs`.

3. **Verify the rebuild against the trace.** Overlay the generated path on the
   traced path and confirm they agree; `pixelmatch` is already installed for
   exactly this. State the residual difference in the findings doc. If your
   reconstruction and the trace disagree by more than a hairline, your
   construction is wrong — re-derive it, don't nudge control points until it
   looks close.

Why both steps rather than just shipping the trace: a trace is a dead bag of
coordinates — it can't be recoloured through `currentColor`, can't be retiled
seamlessly as a background pattern, can't be resized without re-tracing, and
carries the source image's compression noise as permanent bumps in the path.
Rebuilt geometry tiles exactly, scales to any size, and stays editable. The
girih lattice in this repo tiles the plane by construction (135+135+90=360°) —
that property only exists because it was built, not traced.

#### Applying it to the reference's specific assets

- **Background patterns** (the girih/star-lattice watermarks behind the about,
  courses, pricing, testimonial and CTA sections): identify the tiling unit
  first, rebuild it as a seamless tile, and verify it repeats without seams by
  rendering a 3×3 grid before you use it. Ship it at the reference's opacity
  (roughly 3–10%, per `DESIGN.md` §9).
- **Arch silhouettes**: the reference uses more than one — the hero's multifoil
  arch is not the about-section's ogee arch, and neither is the teacher card's
  oval. Confirm which arch each element actually uses before reusing an
  existing mask. `lib/arches.mjs` already holds the family; extend it if the
  reference shows a member it lacks.
- **Ornament dividers**: each one is a composition (rule, diamond terminal,
  centred rosette, sometimes a second rule). Build the parts once and compose
  them; do not draw six near-identical divider files by hand.
- **Icons**: prefer extending `build-icon-sprite.mjs` from Lucide/Simple Icons
  and matching the reference's weight and terminals. Only hand-build an icon
  the libraries genuinely lack — and when you do, match the existing sprite's
  stroke weight, cap style and 24px grid so it sits consistently beside the
  rest.
- **The logo**: already traced and rebuilt from `Assests/Logo/Logo2.jpeg`. Do
  not re-trace it unless the review finds a real defect; if you do, follow
  `tools/graphics/README.md` exactly.

Remember §4 gotcha 2: after regenerating any divider/rosette/medallion SVG you
must re-run **every** `tools/pages/*.php` and then `wp elementor flush-css`, or
the already-saved pages keep the old embedded markup.

### 5.2 What you source from the web — allowed, with judgement

You may use web sources for icons, ornamental vectors, patterns and
photography where generating is impractical. Use them **wisely**: the point is
to match the reference, not to accumulate assets.

Reasonable sources, in rough order of preference:

- **Icons/vectors:** Lucide and Simple Icons (already wired into
  `build-icon-sprite.mjs` — extend that, don't bypass it), Tabler, Phosphor,
  Iconoir, Material Symbols, Font Awesome Free, Bootstrap Icons,
  SVG Repo, Flaticon (e.g. `https://www.flaticon.com/search/2?word=islamic`),
  Noun Project.
- **Islamic pattern/ornament references:** for *studying construction* so you
  can rebuild the geometry yourself — prefer this to importing a stranger's
  path data.
- **Photography:** Unsplash, Pexels, Pixabay, Openverse — searched for the
  actual subject the reference shows (1-to-1 online Quran tutoring, a child
  reading, a mosque interior, teacher portraits).

Rules for anything you bring in:

- **Check and record the licence** before use. Free-for-commercial-use or
  public-domain/CC0 only unless the user approves otherwise. Flaticon's free
  tier **requires attribution** — if you use a Flaticon asset, either satisfy
  the attribution requirement in a credits file and note it, or pick a
  CC0/MIT-licensed equivalent instead. Log every third-party asset in
  `QA/ASSET-SOURCES.md`: file, source URL, author, licence, attribution
  requirement, where it's used.
- **Download and self-host.** Never hotlink a CDN or remote image from the
  site. Assets go into the repo (`assets/svg/`) or `local/media-staging/`.
- **Normalise before shipping.** Run SVGs through the project's SVGO,
  strip `width`/`height` in favour of `viewBox`, convert fills to
  `currentColor` where the design wants the colour to come from CSS, and match
  the existing library's stroke weight (1.5–2px) and rounded geometry so a
  sourced icon is indistinguishable from a generated one.
- **One icon family.** `DESIGN.md` §11 forbids mixing icon libraries on a
  page. If a sourced icon can't be made to sit consistently beside the Lucide
  set, redraw it rather than shipping a visual mismatch.
- **No trademarked, watermarked or AI-slop-looking assets**, and nothing whose
  provenance you can't state.

### 5.3 What you cannot produce and must brief

You have no image model, so you cannot originate the photographs — the boy at
the laptop, the four teacher portraits, the testimonial and CTA avatars, the
illuminated Quran page, the Arabic alphabet chart, the three mosque interiors.
Do not crop them out of the reference JPEGs: those are ~1300px-wide composites,
so a crop is a low-resolution artefact that will look worse than what is
already in `local/media-staging/`.

So, for photography:

- **Audit** what `local/media-staging/` already holds (hero, about ×2, four
  teachers, three testimonials, three blog images, logo raster) against what
  each reference section actually needs.
- **Try §5.2 stock first** for anything missing, where a real photo of the
  right subject exists under a usable licence.
- **Match the treatment exactly** even when the photo itself differs: the arch
  or oval mask silhouette, the gold ring/stroke, the cream inner gap, the
  offset shadow, the crop ratio, the focal point.
- **Produce `QA/IMAGE-BRIEF.md`** — one row per slot still unfilled: page and
  section, required aspect ratio and minimum pixel dimensions *after* masking,
  subject direction, and a ready-to-use generation prompt written in the
  reference's visual language, so the user can supply a real asset.
- **Optimise everything you ship**: WebP (and AVIF where it wins), sized to the
  actual rendered box × 2 for DPR, correct `srcset`/`sizes`, `loading` and
  `fetchpriority` set intentionally (hero eager + high, below-the-fold lazy),
  and explicit `width`/`height` so nothing shifts layout.

---

## 6. Phase 0 — bring the environment up and capture the baseline

Docker is currently **not running**. Start Docker Desktop, then:

```
docker compose -f local/docker-compose.yml --env-file local/.env up -d
```

Note: Docker is unreachable from inside both Codex sandbox modes — Docker and
WP-CLI commands must be approved out of the sandbox (see `AGENTS.md`).

Then, before changing anything, capture a **baseline screenshot set** of every
page at the reference's own viewport (the composite is a 1920-wide desktop
capture; shoot at 1920×full-page, plus 1440, 1024, 768 and 390) into
`QA/baseline/`. Use the project-local Playwright in `tests/visual/` — do not
install a global one. These baselines are what you diff against to prove each
page actually improved rather than merely changed.

---

## 7. Phase 1 — the close review (do this before writing any code)

This is the part that determines whether the whole task succeeds. Review
**one page at a time**. Do not batch. Do not start page N+1's review until
page N is built, iterated and verified.

For the page under review:

1. **Open the reference image and study it properly.** Full resolution.
   Crop into each section and each individual component and look again. You
   are looking for the things a quick glance misses: how far a badge overlaps
   the card edge, whether a divider's terminals are diamonds or dots, whether
   a card's corner watermark is inside or outside its border, whether a
   heading's second line is bronze or gold, where exactly a line of type
   breaks, whether a shadow is centred or offset.

2. **Open the same page on `http://localhost` and screenshot it at the same
   viewport.** Put the two side by side.

3. **Measure, don't estimate.** Read real numbers off the rendered page with
   DevTools/`getComputedStyle`, and derive the reference's numbers from its
   own pixel geometry (the composite is 1920 wide — scale accordingly). Where
   `DESIGN.md` already fixes a value (content max-width 1240px, section
   spacing 88–104px desktop, card radius 16–20px, the type scale), the
   reference should agree; where it genuinely doesn't, follow the reference
   and record the divergence so `DESIGN.md` can be updated afterwards.

4. **Write the findings** to `QA/design-review/<page>.md`, one row per
   discrepancy:

   | # | Section | Reference shows | Build currently does | Severity | Fix (file + change) | Reuses |
   |---|---|---|---|---|---|---|

   Severity: **P0** structural (wrong section, wrong order, missing
   component) · **P1** anatomical (component exists but its shape, ornament,
   badge placement or geometry is wrong) · **P2** metric (spacing, size,
   weight, colour off) · **P3** polish (hover, focus, motion, micro-detail).

   The "Reuses" column is not optional — name the existing helper, token, CSS
   class or SVG each fix will use, or state why a new one is justified.

5. **End the findings doc with an explicit plan** for that page: ordered fix
   list, the files each touches, the edge cases you will test, and what you
   expect to be able to prove at the end.

A findings doc that says "spacing looks slightly off" is a failed review.
Say *which* spacing, *what* it is now, *what* it should be, and *where* the
change goes.

---

## 8. Phase 2 — build, then iterate until it matches

Only after the findings doc exists. **One page is not one pass.** Work it as a
loop and keep looping until the page matches:

```
review → fix → rebuild → screenshot → compare against the reference
   ↑                                            │
   └──────────── still differs? ────────────────┘
```

Each iteration:

- Make the change in the correct layer: content/structure → `tools/pages/`;
  reusable component markup → `tools/elementor-helpers.php`; styling →
  `components.css` (with any new values as tokens in `tokens.css`); a new
  shape → `gen-ornaments.mjs`; a new icon → `build-icon-sprite.mjs`;
  header/footer chrome → `shell.css` + `header.php`/`footer.php`.
- Re-run the page script with `--user=1`, then `wp elementor flush-css`.
- If you regenerated a divider/rosette/medallion SVG, re-run **all**
  `tools/pages/*.php`, per §4 gotcha 2.
- Re-screenshot and put it beside the reference again. Do not judge from the
  code you wrote — judge from the rendered pixels.
- Append the iteration to an **Iteration log** at the bottom of that page's
  findings doc: what you changed, what it fixed, what it did not fix, and what
  the next iteration targets.

Stop iterating a page when the remaining differences are only things you have
explicitly justified in writing (a forbidden fabricated fact, a photograph you
cannot source, a deliberate accessibility improvement). Three iterations with
no measurable improvement means your diagnosis is wrong — go back to the
reference image and re-read the section rather than trying a fourth variation.

### Learn as you go — `QA/LESSONS.md`

Maintain a single running file, `QA/LESSONS.md`, and treat it as required
reading before every page (§0) and required writing after every page.

Append to it whenever you learn something that will change how you work the
*next* page:

- a mistake you made and the correction (e.g. "I sized the arch mask from the
  displayed thumbnail; the composite is 1920 wide and needed a 4.07× scale
  factor")
- a repo behaviour that surprised you (the `--user=1` silent no-op, the
  divider re-bake, a helper that already did what you were about to write)
- a pattern the reference uses consistently, discovered on one page and
  applicable to all of them (e.g. how every eyebrow, every ornament divider or
  every overlapping badge is constructed)
- a token, helper or SVG you added that later pages should reuse instead of
  re-solving
- anything you had to redo, and why

Before starting each new page, read `QA/LESSONS.md` and state — in that page's
findings doc — which lessons apply to it. The later pages should be faster and
more accurate than the earlier ones; if they aren't, you are not using this
file.

Delete anything your changes made dead. Do not leave a superseded CSS block,
an orphaned SVG or an unused helper behind "just in case".

---

## 9. Verification — evidence, not assertion

A page is not done because you changed it. It is done when you can show it.

Per page:

- Screenshot at 1920 / 1440 / 1024 / 768 / 390, full-page, into
  `QA/after/<page>/`. Compare against both `QA/baseline/` and the reference.
- Confirm every P0 and P1 row in the findings doc is resolved; annotate any
  you deliberately did not fix with the reason.
- Browser console clean — no errors, no 404s on assets.
- Layout holds at every viewport between 360px and 1920px, not only at the
  five you shot. No horizontal scroll. No fixed heights that clip content.
- Keyboard: tab through the page; every interactive element reachable with a
  visible focus ring. Decorative SVGs `aria-hidden`. Images have real alt text
  or empty alt if decorative. One H1, sane H2/H3 order.
- Contrast checked on the combinations this design makes risky: gold on cream,
  gold on dark green, muted body text on cream.
- `prefers-reduced-motion` respected by anything you animate.
- Lighthouse on the page — no regression against the baseline run.
- PHP lint every file you touched.

Site-wide, at the end: run the full `tools/pages/*.php` sweep from a clean
bootstrap and confirm the site rebuilds identically from a fresh clone
(that is what `local/bootstrap.ps1|.sh` exists to prove).

**If you could not verify something, say so plainly.** Do not report a page
complete on the strength of having edited it.

---

## 10. Page order

Follow the composite top-to-bottom, because later sections inherit tokens and
components the earlier ones establish:

1. **Global chrome** — header + footer (`Home.jpeg`, `End.jpeg`). Do these
   first: they appear on every page, and getting the logo lockup, nav
   treatment, CTA button and footer panels right calibrates the tokens
   everything else consumes.
2. **Home** — hero, trust strip, about, courses, pricing, teachers,
   testimonials, blog, final CTA (`Home.jpeg`, `Home2.jpeg`, `courses.jpeg`,
   `pricing.jpeg`, `Teachers.jpeg`, `Reviews.jpeg`, `Blogs.jpeg`, `End.jpeg`,
   and `Website Layout.png` for the rhythm between them).
3. **Courses** · 4. **Pricing** · 5. **Teachers** · 6. **About** ·
7. **Contact** · 8. **Free Trial** · 9. **FAQ** · 10. **Kids** ·
11. **Blog** archive + single post.

For pages with no dedicated reference image (About, Contact, Free Trial, FAQ,
Kids, Blog single), derive the treatment from the established components and
`DESIGN.md` — do not invent a new visual language for them — and write their
copy as realistic dummy content in the same voice and length as the referenced
pages, per §2, registering it as invented.

---

## 11. Documentation and Git

- Keep each `QA/design-review/<page>.md` updated as you go — findings, then
  iterations, then evidence. This is the audit trail for the whole task.
- Keep `QA/LESSONS.md`, `QA/PLACEHOLDER-REGISTER.md`, `QA/IMAGE-BRIEF.md` and
  `QA/ASSET-SOURCES.md` current.
- Update `DESIGN.md` wherever the reference established a value the design
  system didn't have, or contradicted one it did.
- Update `CLAUDE.md` only with **durable** knowledge (a new pipeline step, a
  new gotcha) — not progress. Hard cap 1000 lines.
- Mark completed items in any existing checklist you advance.
- Commit per page, on `feature/setup`, short imperative subject:
  `fix: match hero ornament and spacing to reference`. No AI attribution
  trailers, no multi-paragraph bodies. Review `git status` and the actual
  diff before staging; stage deliberately, never `git add .`. Do not push
  without being asked.

---

## 12. Definition of done

- Every page reviewed in a `QA/design-review/*.md` with measured findings and
  a logged iteration history.
- Every P0 and P1 resolved; every remaining P2/P3 listed with a reason.
- Side-by-side evidence in `QA/after/` for each page at five viewports.
- `QA/LESSONS.md` shows genuine accumulation — later pages citing and reusing
  earlier findings.
- The reference's copy is on the page verbatim; any content the reference
  didn't show is realistic dummy text of the right length, not a stub.
- `QA/PLACEHOLDER-REGISTER.md` lists every unverified claim and every invented
  string now live on the local build, each traceable to one editable value, so
  the client can clear it before launch.
- `QA/IMAGE-BRIEF.md` specifies every photograph still needed.
- `QA/ASSET-SOURCES.md` licences every third-party asset used.
- Console clean, a11y checks pass, Lighthouse not regressed, PHP lints.
- Fresh-clone bootstrap reproduces the site.
- No dead code, no orphaned assets, no duplicated helpers.
- `main` untouched.

---

## Appendix A — calibration baseline

What follows is an independent read of the reference, provided so your own
review has something to check itself against. **It is a seed, not a
substitute.** Confirm or correct every line of it against the image itself —
where this appendix and the image disagree, the image is right.

**Header** — floating rounded-pill container on cream, detached from the
viewport top with cream visible above and to both sides; logo left (arch-framed
book-and-headphones mark + `EASY QURAN` in DM Serif + letterspaced
`C L A S S E S` flanked by short gold rules); nav centred; active item bronze
with a short bronze underline; bronze pill `FREE TRIAL` with a gift icon, right.

**Hero** — white rounded-pill eyebrow with a circular icon chip. H1 in DM Serif,
line 1 dark green, line 2 half bronze. A thin gold rule with diamond terminals
beneath. Two lines of body. A row of four small white feature cards (icon left,
two-line label). Bronze primary + white outlined WhatsApp secondary, both pill.
Five overlapping circular avatars, five gold stars, one trust line. The photo
sits in a **multifoil/ogee arch** with a double gold outline and a cream gap
between image and frame.

**Trust strip** — white rounded panel that overlaps the section boundary below;
four columns, outline icon left of a two-line title + two-line caption,
separated by full-height hairlines.

**About** — left: a collage of arch-masked images, overlapping, each with a
white outline stroke and soft shadow (large ogee-arch Quran page; small
quatrefoil-masked alphabet chart above-left; arch-masked child below-left) over
a large low-opacity girih watermark. Right: a horizontal ornament rail (rule –
centred gold rosette – rule) above the heading; DM Serif heading with the last
phrase bronze; a small centred ornament below it; two body paragraphs; three
stat tiles (rosette-badge icon, label, value) split by vertical hairlines;
bronze pill CTA with a circular arrow + outlined secondary with a circular
phone icon.

**Courses** — gold book icon + `OUR COURSES` eyebrow; two-line DM Serif heading
with the tail bronze; a left-aligned ornate divider (rule – diamond – rosette –
diamond – rule). Six cards, 3×2. Card: rosette-badge number `01` + short dash,
serif title, `(Beginner)` sub in muted, small centred ornament divider, body,
circular gold-outlined arrow bottom-right, faint girih watermark inside the
card's top-right. Large girih watermarks in the section's own corners. A wide
centred ornate divider closes the section.

**Pricing** — cream/white with ornate gold corner frames top-left and
top-right; gold-outlined pill eyebrow containing a rosette + `PRICING`; centred
two-line DM Serif heading; small diamond divider. Four plan cards. Each: a
dark-green rosette badge floating **above** the card's top edge; a dark-green
banner with clipped/chamfered ends holding `N Days/Week` in serif white; five
feature rows with small gold rosette bullets; an ornament divider; a large
serif price + a cream `Month` pill + a dark-green circular arrow button. The
recommended card adds a gold double border, a gold `RECOMMENDED` ribbon tab
riding its top-right corner, and small sparkles. Below: a white rounded strip,
four benefits with rosette-framed icons, vertical hairlines between.

**Teachers** — left column: pill eyebrow with icon; three-line DM Serif heading
with the third line bronze; a small rosette + rule; body; three stacked feature
tiles (icon, bold title, caption); a prev/next pair of circular outline buttons
and a dark-green pill `View All Teachers` with a circular arrow. Right: four
cards. Each: an **oval** portrait with a gold ring, overlapping the card's top
edge; a dark-green circular book badge centred on that overlap; a two-line bold
sans name; `Quran Teacher` in gold; a hairline with a centred dot; three
icon+text meta rows; an outlined pill `View Profile` with a circled chevron.

**Testimonials** — centred eyebrow: rule – circular quote icon –
`WHAT OUR FAMILIES SAY` – rule. Two-line heading, second line gold. Rosette
divider. Two lines of intro. Three cards; the circular avatar **overlaps the
card's top edge**, with a dark-green circular quote badge at its lower-right;
bold name; gold pin icon + location; hairline with a centred dot; centred
quote; a cream inset strip with three icon+label mini-features split by
hairlines. Faint girih watermarks between cards. Three carousel dots, the
active one gold.

**Blog** — eyebrow: rule – megaphone icon – `LATEST NEWS` – rule. Two-line
heading, second line gold. Prev/next circular buttons top-right (white
outline + solid gold). Three cards: inset rounded image, a white rounded date
chip (calendar icon, day, month) overlapping its top-left; gold uppercase
category; bold sans two-line title; three-line excerpt; `Read More` + a gold
circular double-chevron, bottom-right. Dots below, active one a pill.

**Final CTA** — dark-green rounded panel with a low-opacity geometric
watermark. Left: circled book icon + `START YOUR JOURNEY` over a gold rule;
two-line serif heading, line 1 white, line 2 gold; short gold rule; three lines
of body. Right: a white rounded pill containing four overlapping avatars, a
cream circle reading `+1.5K / Happy Students`, and a gold pill
`Book My Free Trial Class` with a circular double-chevron.

**Footer** — cream rounded panel. Three columns: logo lockup + about paragraph
+ four circular outline social buttons | `GET IN TOUCH!` with dark-green
circular icons, each row separated by a hairline | `QUICK LINKS` in two
sub-columns with gold chevron bullets. A short gold underline sits under each
column heading, and full-height hairlines divide the columns. A circular logo
medallion straddles the panel's bottom edge, above a dark-green rounded
copyright bar.
