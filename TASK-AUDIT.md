# TASK-AUDIT — Deep Production Readiness Audit
## Easy Quran Classes — WordPress / Elementor / Git `main`

## Goal
Perform a deep, evidence-driven audit of the Easy Quran Classes website and production code on `main`.
Audit and remediate:
- functionality, forms, buttons, links and user journeys;
- meaningful unit/integration/E2E coverage;
- visual/UI quality and UX;
- responsive behavior, accessibility and motion;
- content accuracy, credibility and AI-slop/residue;
- technical SEO, SEO content and ranking readiness;
- performance/Core Web Vitals;
- WordPress/code security, data leakage and privacy;
- plugin/theme/dependency risk;
- production hardening and Git hygiene.
Every important conclusion must follow:

```text
Hypothesis → Test → Evidence → Root Cause → Severity
→ Smallest Correct Fix → Verification → Regression Test
```

Do not guess. Do not call something fixed without re-testing.
Do not promise rankings. Improve crawlability, relevance, technical SEO, content quality, internal linking and Core Web Vitals.
---

# 0. Known State — Do Not Re-Derive, Do Not "Fix"
Verified 2026-09-08. Treat every item below as given. Log each once (in `AUDIT-REPORT.md`'s Known
State section, see §40) with the disposition shown. Do not spend audit time rediscovering these,
and do not change them.

1. **Release parity gap.** Production runs child theme `v1.0.0`; `feature/setup` (= local) is
   `v1.2.6`. 46 files differ (`+801/-422`). Production 404s on feature-only assets (`arch-frame.svg`,
   `divider-medallion.svg`, `logo/eqc-logo.svg`).
   → Logged as one P1, closed by the §47 gated release. Never re-report per page. See §2 for the
   environment split this implies.
2. **Site-wide `noindex, nofollow` on production is intentional** pre-launch state.
   → NOT a P0 (see the amended severity line in §5). Do **not** change `blog_public` or any robots
   setting on production. Flipping the site to indexable is a separate, client-approved launch step
   outside this audit.
3. **Missing canonical tags are intentional** Rank Math behavior tied to (2), and resolve
   automatically once indexing is approved. See `CPANEL-WORKFLOW.md` §19.
   → Not a finding.
4. **Email delivery is confirmed broken and deferred by client decision.** A real Contact
   submission stores correctly but no notification arrives; host PHP `mail()` does not deliver. The
   fix needs an Elementor account plus the real client mailbox. See `TASK-DEPLOY.md:985` and
   `CPANEL-WORKFLOW.md` §17.
   → Log as accepted/deferred. Do not attempt to fix. Do still verify submissions store correctly
   and that notification settings are configured correctly.
5. **Placeholder contact data is live:** `tel:+10000000000` and `wa.me/10000000000`. Real values are
   not yet available.
   → P1, parked under Remaining Client Inputs. Do **not** invent a number (Content Integrity, §21).
6. **Five `[DEMO]`-titled posts are published and listed in the production sitemap**
   (`/demo-*/`). No real articles exist yet.
   → Correct disposition is to unpublish or noindex them before launch, **not** to write
   replacement content.
7. **The privacy policy page is a draft** while two forms collect PII.
   → Real finding, but a policy/legal item. Flag it; do not auto-write legal text.
8. **Novamira is active locally only and never deployed.** It emits
   `[ERROR] WordPress ability 'mcp-adapter/...' does not exist` into the output of every WP-CLI
   call. That noise is benign — do not investigate it. Exclude `novamira`, `vibe-ai` and `akismet`
   from the production-parity plugin inventory and from all dynamic security testing (§30).
---

# 1. Read First
Before any test/change, read the latest approved:
1. `CLAUDE.md`
2. `DESIGN.md`
3. `TASK-WEBSITE.md` — **completed history**, not open work
4. `TASK-DEPLOY.md` — completed except line 985; **read lines 965-1019 (Definition of Done)
   specifically** — it records what is already known broken, deferred or intentional
5. `CPANEL-WORKFLOW.md`
6. `README-SETUP.md`, `CONTRIBUTING.md`, `AGENTS.md`, `TOOL-INVENTORY.md`
7. `.gitignore`
8. `.claude/rules/*.md`
9. relevant `.claude/skills/*/SKILL.md`, including `elementor-build` and `cpanel-audit`
10. build/deployment reports
11. existing tests/checklists/docs
12. `AUDIT-REPORT.md`, if it already exists — read its Phase Log (§40) and resume from there
`TASK.md` is also completed history — reference only, not open work.
If `/superpowers:using-superpowers` or equivalent exists, use it.
Prefer existing skills such as `implementation-workflow`, `git-checkpoint`, `visual-qa`, `seo-review`, `performance-audit`, `wp-audit`, `wp-cli-safe`, `wordpress-debug`, `plugin-evaluation`, `backup-verify`, `release-check`, `elementor-build`, `cpanel-audit`.
Do not install tools/MCPs/plugins merely to make the audit look sophisticated.
Expect `.claude/settings.json` `ask` prompts on `wpcli db *`, `rm *`, `git push` and similar —
that needs an approval, not a workaround.
---

# 2. Scope / Environment
**Environment split (see §0 item 1 — release parity currently fails):**
- **Local (`http://localhost`, = `feature/setup`) is the release candidate.** Audit here: all
  PHP/JS code, Elementor content, visual design, UX, responsive, motion, accessibility, forms
  (including active/benign injection testing), content accuracy, internal links, edge states,
  cross-browser.
- **Production (`https://easyquranclasses.com`) is infrastructure only.** Audit here: HTTPS/
  certificate/redirects, security headers, cookie flags, directory listing, exposed
  `.git`/`.env`/backups/logs/phpinfo, REST/XML-RPC posture, user enumeration, version leakage,
  robots.txt/sitemap serving, status codes, TTFB. Normal user journeys and benign low-impact input
  only.
- Production is **behind an NGINX cache** — clear it before any production check, or explicitly
  note the result may be cached.
- Local and production run **different plugin sets** (§0 item 8 — Novamira is local-only). Do not
  compare local vs. production performance numbers directly; note the difference instead.
Confirmed hosting constraint: **no Shell / SSH / Terminal access on cPanel**.
Before testing, report:
- repo root, branch/status/remotes;
- local and production URLs;
- WordPress/PHP/Elementor versions on both;
- theme/plugins on both, noting the difference;
- cache/CDN;
- current deployment method;
- the release-parity statement from §0 item 1.
Use local/staging for active/destructive edge/security tests. Never on production.
---

# 3. Clarifying Questions
Ask only when still unknown and materially blocking.
Potential questions:
- canonical production domain;
- priority SEO countries/audiences;
- approved prices/teachers/testimonials/business claims;
- Search Console/Analytics availability;
- real form destination email/WhatsApp;
- authorization for active production security tests beyond passive checks;
- staging availability.
Do not ask for information already documented/inspectable.
Never request passwords/secrets in project docs/chat.
---

# 4. Mandatory Audit Loop
Maintain one living TODO.
For every issue:
- [ ] Inspect current implementation.
- [ ] Form a specific hypothesis.
- [ ] Define exact test/evidence.
- [ ] Reproduce; reject false positives.
- [ ] Find root cause.
- [ ] Assign severity/affected scope.
- [ ] Check reusable fix first.
- [ ] Explain why new code is needed if reuse is unsuitable.
- [ ] Implement smallest maintainable fix.
- [ ] Test normal + edge cases.
- [ ] Run regression.
- [ ] Remove obsolete/debug/duplicate code in scope.
- [ ] Update tests/docs/checklists.
- [ ] Keep `CLAUDE.md` durable/current and <1000 lines.
- [ ] Review Git diff.
- [ ] Re-test production where safe.
Close issues only with verification evidence.
---

# 5. Severity
**P0 Release Blocker:** credential/private-data exposure, exploitable SQLi, persistent XSS, arbitrary code/file execution, outage, serious corruption/loss, entire site *unintentionally* noindexed. **The current site-wide `noindex,nofollow` on production is confirmed intentional pre-launch state (§0 item 2) — it is explicitly NOT a P0, and must not be changed as part of this audit.**
**P1 Critical:** broken primary CTA/form, major mobile failure, realistic auth/security weakness, widespread broken assets/URLs, serious accessibility blocker, major canonical/indexation failure.
**P2 High:** major UX friction, serious performance regression, credibility/content issue, meaningful security-hardening weakness, important SEO/schema/internal-link issue.
**P3 Medium:** secondary UI/responsive/accessibility/SEO/content issue.
**P4 Low:** polish/microcopy/minor consistency.
Do not inflate severity.
---

# 6. Evidence Standard
For every confirmed finding record:
- ID/severity/category;
- page/component/file/environment;
- reproduction;
- expected vs actual;
- screenshot/log/test evidence;
- root cause;
- fix;
- verification;
- regression coverage.
For meaningful rejected hypotheses, record test + why rejected.
---

# 7. Repository Audit — `feature/setup` (working branch) + `main` (what ships)
`main` holds only 26 files (the child theme, `.gitignore`, `.github/workflows/`) — nearly
everything interesting (`tools/`, `.claude/`, `local/`, all docs, both `package.json`) lives on
`feature/setup`. Audit **`feature/setup`'s working tree and reachable history on both branches**
as the primary surface for:
- merge markers, dead/duplicate implementation, debug code;
- test credentials, secrets, API keys, private keys;
- `.env`, `wp-config.php`, SQL dumps/backups;
- FTP/cPanel/SMTP credentials;
- browser auth state;
- local/staging URLs in shipped code;
- sensitive source maps/config;
- huge unnecessary binaries;
- nulled/unlicensed software;
- WordPress core edits;
- Hello Elementor parent edits;
- undocumented third-party plugin patches.
Run on `feature/setup`:

```bash
git status
git diff --check
git diff
```

Inspect tracked files, not only `.gitignore`.
Where practical, inspect Git history for secret leakage across both branches.
Do not rewrite history until a real leak is confirmed and remediation is planned.

**Separately, verify what `main` actually ships is clean** — `git ls-tree -r --name-only main`,
confirm it is exactly the child theme + `.gitignore` + `.github/workflows/` and nothing else (no
`.claude/`, docs, `tools/`, `local/`), per `.claude/rules/git.md`'s branch policy.
---

# 7A. Dead Code, Legacy Artifacts and Version Currency
Measured 2026-09-08 — start from these leads rather than a blind sweep; re-verify each is still
true before acting on it.

**Already current — verify, then move on, do not churn:**
WordPress core (`wp core check-update` confirms latest), `hello-elementor` 3.5.1, all installed
themes, Elementor 4.2.4, FluentForm 6.2.13, Playwright 1.63.0.

**Genuinely pending:**
- `seo-by-rank-math` has an update available (a site plugin — test locally first, never update
  production blindly).
- `pngjs` 3.4.0 → 7.0.0 (four majors behind) and `lucide-static` 1.42.0 → 1.43.0, both in
  `tools/graphics/package.json` — **dev-only, never deployed**, so severity is local-tooling, not
  site.
Report currency honestly ("mostly current, one plugin and two dev deps behind") rather than
manufacturing a modernization backlog.

**Confirmed dead:**
`wp-content/themes/easy-quran-classes-child/assets/css/motion.css:104` styles
`.eqc-divider-flower`, but `divider-flower.svg` was deleted on `feature/setup` and nothing emits
that class. This dead CSS ships to production today.

**Needs the method below, not a literal-string grep:** `assets/svg/girih-lattice-dense.svg` and
`assets/svg/girih-lattice-fine.svg` have zero references in theme PHP/CSS/JS, but
`tools/graphics/gen-ornaments.mjs:161` **does** still actively produce them
(`` save(`girih-lattice-${name}.svg`, svg) `` — a template literal a plain `grep -rn
"girih-lattice-fine.svg"` will not match, which is exactly why step 3 of the method below exists).
So they are not orphaned generator leftovers; they are intentional generator output that ships in
the theme's `assets/svg/` folder with **no consumer in the theme's own code**. Determine whether
they were meant to be wired in and aren't, or are scratch/precursor output that should stop being
generated — do not delete either the `.svg` files or the generator's block on a grep-miss alone.

**Likely dead, requires the method below:** the theme ships 36 SVGs; theme PHP/CSS/JS names only
13 concrete files plus two dynamic patterns (`eqc_get_svg_asset()`, `eqc_divider_svg()`). The
remainder are candidates, not confirmed.

**Dead weight:** `akismet` and `vibe-ai` plugins, both inactive (`vibe-ai` has no working path in
this environment at all per `CLAUDE.md`); three unused core themes installed locally
(`twentytwentythree/four/five`) — check whether production carries them too.

**The trap — read before deleting any asset.** `eqc_divider_svg()`
(`inc/template-tags.php:88`) is called only from `tools/elementor-helpers.php`, with variants
`card`, `dot`, `accent`, `section` — and those calls **snapshot the SVG's file content into
`_elementor_data`** at page-build time (see `tools/graphics/README.md`). Those files therefore look
unreferenced at runtime but are required for any future `tools/pages/*.php` rebuild. Deleting them
silently breaks the page builders.

**Required method before removing any asset or CSS rule:**
1. Trace every `eqc_get_svg_asset()` / `eqc_divider_svg()` call site and enumerate every
   `$variant`/`$name` value actually passed, including dynamically constructed ones.
2. Check `tools/pages/*.php` and `tools/elementor-helpers.php` for build-time consumers.
3. Check `tools/graphics/gen-ornaments.mjs` — a file it still produces is intentional output, not
   an orphan.
4. Check WordPress-side usage (site icon, custom logo, Elementor kit).
5. Only then delete, and re-run the affected page builders to prove nothing broke.
Never delete on a grep miss alone.

Also sweep for, and remove within scope: unused CSS rules/selectors in `components.css` (1,474
LOC), unreachable JS branches in `eqc.js`, commented-out blocks, debug statements, duplicate
implementations, superseded helpers, and any file that ships but is never served.
---

# 8. Secret / Public Data Leakage
Search repo + production-accessible surface for:
- credentials/tokens/private keys;
- `.env`, `.git`, SQL, ZIP backups, `.bak/.old/.orig/~`;
- `phpinfo`, debug logs, stack traces;
- filesystem/local paths;
- localhost/staging URLs;
- sensitive hidden form data;
- private comments/internal notes;
- credentials in HTML/JS/source.
Use an existing trusted secret scanner if available; otherwise targeted scanning is fine.
If a real secret is found: stop exposure, rotate/revoke where authorized, remove from active files, assess history, update ignore/workflow, verify remediation.
Never print secret values in reports.
---

# 9. Test Inventory
Classify the implementation before adding tests.
**Measured code surface:** child theme is 3,268 LOC total (`components.css` 1,474 ·
`eqc.js` 358 · `shell.css` 271 · `inc/template-tags.php` 215 · `functions.php` 165 · others
smaller). Testable PHP logic is ~550 LOC; all of it presentation helpers. Do not stand up
PHPUnit/the WordPress test suite for this — it would be a larger artifact than the code it tests
(§10 already covers the right-sized alternative).
**Existing harness:** `tests/visual/sweep.mjs` — a Playwright screenshot + horizontal-overflow
sweep at DESIGN.md §21's 7 viewports. There is no assertion-based E2E anywhere in the repo. Extend
`sweep.mjs`'s viewport array for §16 rather than building a parallel screenshot tool; add one
assertion-based script beside it for §12 (status, title, H1 count, console errors, failed
requests).
| Component | Test Level | Existing? | Needed? | Tool | Status |
|---|---|---:|---:|---|---|
Use:
- unit tests for isolated PHP/JS logic;
- integration tests for WordPress behavior;
- E2E for user journeys;
- visual tests for CSS/Elementor;
- security tests for trust boundaries.
Do not create meaningless tests only to increase test count.
---

# 10. PHP Unit / Static Tests
For meaningful custom PHP test:
- sanitization/escaping helpers;
- capability checks;
- nonce/state-change logic;
- URL generation;
- helper/template conditionals;
- empty/null/invalid input;
- Unicode/Arabic;
- long values.
Run syntax checks on custom PHP.
If no test harness exists, add one only if custom logic justifies it. Otherwise use focused assertions/integration tests and document limitation.
---

# 11. JavaScript Unit Tests
For meaningful custom JS test:
- missing elements;
- repeated init;
- multiple instances;
- keyboard/click/touch;
- reduced-motion condition;
- rapid interaction;
- malformed DOM;
- duplicate listeners/cleanup.
Do not add a full JS testing stack for trivial animation code unless justified.
CSS/Elementor should be validated by browser/visual tests, not fake unit tests.
---

# 12. Full E2E Audit
Use Playwright/browser automation where reliable.
Test:
1. Home
2. About
3. Courses
4. Teachers
5. Pricing
6. Contact
7. FAQ
8. Blog
9. Free Trial
10. Online Quran Classes for Kids
11. single Blog post
12. category/archive if enabled
13. 404
14. search if exposed
Note: local currently has only 5 posts, all titled `[DEMO] …` (§0 item 6) — pagination will not
trigger at that count. Either seed additional posts locally to test pagination, or record it
explicitly as untested; do not skip it silently.
For each validate:
- response/status, title, one H1;
- critical content visible;
- images/fonts load;
- no significant console errors;
- no failed critical requests;
- header/footer/active nav;
- CTA behavior;
- keyboard flow;
- responsive layout;
- no overflow;
- no local/staging links.
---

# 13. Every Link / Button Audit
Inventory/test:
- logo;
- desktop/mobile nav;
- footer;
- course/teacher/pricing CTAs;
- Contact/Free Trial CTAs;
- WhatsApp/email/phone;
- Blog cards/Read More;
- FAQ controls;
- form submits;
- social links;
- breadcrumbs/pagination;
- icon-only controls.
Verify label, destination/action, expected vs actual, focus, keyboard, touch, loading/disabled state, new-tab behavior.
Reject `#` placeholders, empty hrefs, wrong domains, localhost/staging links, inaccessible controls and unexpected 404s.
---

# 14. Forms Deep Audit
Audit Contact + Free Trial.
Normal:
- valid submission;
- success state;
- delivery/storage;
- expected notification.
Validation:
- empty required fields;
- invalid email/phone;
- long values;
- whitespace;
- Unicode/Arabic/emoji;
- duplicate/rapid submit;
- refresh/back.
Security, using benign markers only:
- HTML/script-like input;
- SQL metacharacters;
- encoded input;
- newline/header injection;
- unexpected/missing fields.
Never attempt destructive extraction.
Verify server-side validation where required, sanitization, output escaping, no stored/reflected execution, no mail-header injection, no PII in URLs/analytics.
---

# 15. Visual / Design Fidelity
Use client references + `DESIGN.md`.
Critically audit:
- hierarchy, typography, spacing, color, alignment;
- cards, imagery, arches/masks, ornament;
- CTA hierarchy, header/footer, forms;
- Blog/pricing/courses/teachers;
- whitespace, motion, density.
For every major section ask:
1. intentional or template-like?
2. hierarchy clear?
3. spacing consistent?
4. CTA obvious but not aggressive?
5. brand-consistent?
6. anything unfinished?
7. anything conflicting with references?
8. motion helping or distracting?
Do not preserve weak UI merely because it already exists.
---

# 16. Responsive / Cross-Device
Test at minimum:
Phones: `320x568`, `360x800`, `390x844`, `430x932`  
Tablet: `768x1024`, `820x1180` where practical, `1024x768`  
Laptop/Desktop: `1280x800`, `1366x768`, `1440x900`, `1920x1080`  
Wide: `2560x1440` where practical
Also resize intermediate widths.
This list is wider than DESIGN.md §21's approved 7-viewport QA set (which `sweep.mjs` already
implements). Reconcile explicitly: extend `sweep.mjs`'s viewport array to cover the full list
above rather than diverging silently or building a second tool (§9).
Check overflow, clipping, wrapping, hero, CTA groups, nav collapse, mobile menu, cards, pricing, forms, footer, sticky UI, touch targets, image crop, ornament overflow, motion simplification.
Where practical test portrait/landscape, 200% zoom, keyboard, touch-like behavior, long text.
Do not approve based only on Elementor preview.
---

# 17. Motion / Interaction
Audit hero/section reveals, cards, FAQ, sticky header, mobile menu, CTA microinteractions, reduced-motion.
Verify:
- content never remains hidden;
- no duplicate observers/listeners;
- no jank/animation CLS;
- no important hover-only behavior;
- focus/tap equivalents;
- `prefers-reduced-motion`;
- H1/LCP not delayed.
Remove motion that hurts usability/performance.
---

# 18. UX Audit
Test realistic journeys:
**New parent:** service → course → pricing → trial  
**Adult beginner:** beginner suitability → course → teacher approach → trial  
**Returning visitor:** pricing/contact/trial quickly  
**Blog visitor:** article → relevant service → course/trial
Evaluate decision load, next-step clarity, dead ends, CTA conflicts, trust gaps, missing info, friction, labels, excessive scrolling, form burden and mobile friction.
Audit information architecture, navigation, conversion flow, trust, content order, consistency, footer, FAQ, error recovery and empty states.
---

# 19. UI Quality
Review as a senior product/UI designer:
- type hierarchy/line length;
- component/color/button/icon consistency;
- radii/shadows/alignment/grid;
- section rhythm;
- imagery/ornament density;
- template feel;
- mobile composition;
- ultrawide containment.
Flag visually weak/amateur/inconsistent/noisy sections.
Every proposed change needs a reason.
---

# 20. Content Accuracy / Credibility
Create:
| Claim | Page | Source | Verified? | Action |
|---|---|---|---:|---|
Check prices, teachers, qualifications, certifications, years, students, ratings, testimonials, guarantees, scheduling, male/female teacher claims, progress tracking, free-trial terms, payments/refunds, contacts, address, accreditation.
Unsupported claims must be removed, rewritten or flagged.
---

# 21. AI Slop / AI Residue
Audit customer-facing copy, metadata and production-visible text for:
- “As an AI”;
- “Generated by Claude/AI”;
- `Co-Authored-By: Claude`;
- prompt fragments/internal notes/placeholders;
- lorem ipsum;
- fake proof;
- repetitive generic intros;
- “unlock / transformative / seamless / elevate / world-class” filler;
- adjective stacking;
- vague marketing claims;
- robotic FAQs;
- keyword stuffing;
- repetitive sentence patterns;
- excessive em-dash use;
- copied inspiration-site wording.
Do not delete legitimate internal project docs merely because they mention Claude.
Requirements:
- no AI residue in customer-facing output;
- no AI attribution in commit messages;
- no prompt/debug text in production;
- natural, specific, restrained copy.
Rewrite weak copy and re-read in page context.
---

# 22. Accessibility
Target WCAG 2.2 AA-oriented quality.
Audit:
- one H1/heading hierarchy/landmarks;
- semantic controls;
- labels/required/error states;
- keyboard/focus order/focus visibility;
- mobile menu/accordion;
- contrast/link distinction;
- alt text/decorative images;
- touch targets;
- reduced motion;
- zoom/reflow;
- no color-only meaning;
- no keyboard traps;
- accessible names.
Manually test keyboard-only primary journeys.
Use axe or similar if already available; do not rely only on automated tools.
---

# 23. Technical SEO
Audit live production rendering.
Indexability:
- robots.txt/sitemap;
- WordPress indexing setting;
- meta robots/X-Robots-Tag;
- canonical;
- status codes;
- redirect chains/loops;
- HTTP→HTTPS;
- www/non-www;
- trailing slashes;
- 404/soft 404.
Every indexable page:
- unique title/meta;
- one H1/logical headings;
- clean URL/canonical;
- internal links;
- useful alt text;
- Open Graph/social metadata if implemented.
Schema:
- validate rendered JSON-LD;
- no fake LocalBusiness/ratings/reviews;
- verified Organization/WebSite only;
- Breadcrumb only if real;
- FAQ schema only when appropriate.
Thin/duplicate:
- tag/category/author/attachment/search pages;
- demo posts;
- placeholders/duplicates.
Index/noindex intentionally.
---

# 24. SEO Content / Ranking Readiness
Do not guarantee rankings.
Primary intents:
- Home: online Quran classes
- Courses: online Quran courses
- Teachers: online Quran teachers / Quran tutors
- Pricing: online Quran classes pricing
- Kids: online Quran classes for kids
- Free Trial: Quran trial / Quran class enrollment
For each page assess primary/secondary intent, topical completeness, uniqueness, cannibalization, natural keywords, user questions, internal links, CTA relevance, title/H1 alignment and usefulness vs fluff.
Use current public SERPs/competitors where available to understand intent, expected content, SERP features and gaps.
Do not copy competitors or add keywords mechanically.
---

# 25. Internal Linking
Crawl links.
Find orphan pages, weak important pages, excessive depth, broken links, poor/generic anchors, excessive exact-match anchors, confusing competing links.
Ensure natural paths:
- Home → Courses/Pricing/Teachers/Trial
- Blog → relevant Course/Trial
- Courses → Pricing/Teachers/Trial
- Kids → relevant Course/Trial
- FAQ → detailed pages where useful
---

# 26. Content Quality
For every main page ask:
- offer clear above the fold?
- audience clear?
- specific/useful?
- objections answered?
- duplicated/contradictory?
- unsupported?
- too long/too vague?
- CTA appropriate?
Mark each section: `KEEP`, `EDIT`, `REMOVE`, `VERIFY WITH CLIENT`.
Do not add words just for SEO volume.
---

# 27. Performance / Core Web Vitals
Audit representative pages: Home, Courses, Pricing, Blog, one Blog post, Free Trial, Kids.
Use Lighthouse/browser profiling where available.
Check:
- LCP, CLS, interaction responsiveness/INP proxies;
- TTFB where measurable;
- image weight/responsive images/WebP/AVIF;
- hero loading/lazy loading;
- fonts;
- CSS/JS;
- Elementor DOM;
- third-party scripts;
- motion JS/long tasks;
- cache/compression/CDN.
Fix root causes before hiding issues behind caching.
Re-test after meaningful fixes.
---

# 28. WordPress Security — Static Code
Audit custom PHP for:
- unsanitized input/unescaped output;
- missing nonce/capability checks;
- unsafe file/include operations;
- raw/unprepared SQL;
- unsafe AJAX/REST callbacks;
- unrestricted uploads;
- open/unsafe redirects;
- hardcoded credentials;
- debug output;
- unsafe user-controlled URLs.
For SQL: first determine whether custom SQL exists. If yes, verify parameterization/`$wpdb->prepare()`. Do not invent SQLi findings where no SQL trust boundary exists.
Audit custom JS for unsafe `innerHTML`, DOM insertion, query-string injection, untrusted HTML and browser-side secrets.
---

# 29. Plugin / Theme / Dependency Security
Inventory WordPress core, Elementor, Hello, child theme, plugins, Node dependencies if any, Composer dependencies if any.
Check versions, maintenance, known advisories, abandoned packages, unused plugins/themes and duplicate functionality.
Run `npm audit` only if a relevant package manifest exists; `composer audit` only if Composer dependencies exist — there are currently none in this repo.
The two existing `package.json` files (`tests/visual/`, `tools/graphics/`) are both devDependencies-only and **never deployed** — advisories there are local-tooling severity, not site severity. See §7A for the measured version-currency state.
Use current trusted advisory/vulnerability sources.
Do not update production blindly. Test updates locally/staging first.
---

# 30. Dynamic Security Testing
Perform active testing primarily on local/staging, with Novamira deactivated first so the local plugin set matches production (§0 item 8).
Production active tests must be authorized, rate-limited and non-destructive.
For user-controlled inputs use benign markers for:
- SQL metacharacters/quotes;
- HTML/reflected/stored/DOM XSS;
- encoding/double encoding;
- newline/header injection;
- overlong values;
- unexpected fields/types.
Never use payloads designed to extract files/data/credentials.
Verify validation, sanitization, escaping, no code execution, no stack trace and unchanged DB behavior.
Also test when applicable:
- CSRF/nonces on state-changing endpoints;
- logged-out/lower-role authorization;
- open redirects;
- upload type/size/filename/executable rejection.
No password guessing.
---

# 31. Passive Production Security
Safely check:
- HTTPS/certificate/HTTP→HTTPS;
- mixed content;
- HSTS appropriateness;
- security headers;
- cookie flags;
- directory listing;
- `.git/.env`;
- backups/SQL/logs/phpinfo/debug pages;
- stack traces;
- unnecessary version leakage;
- REST/XML-RPC posture;
- user enumeration;
- cache leakage of private/admin content.
Do not disable REST/XML-RPC merely because visible; determine actual use/risk.
For CSP, test Elementor compatibility first; prefer report-only evaluation before enforcement.
---

# 32. Privacy / Data Handling
Audit Contact/Free Trial fields, storage, email delivery, analytics, query params, logs, embeds, cookies and known retention.
Check minimum necessary data, no sensitive data in URLs/public source, no public caching of submissions, no accidental analytics/debug leakage.
Do not invent legal/privacy claims. Flag legal/policy items separately.
---

# 33. WordPress Hardening
Review:
- HTTPS admin/login;
- production debug disabled;
- roles/accounts where available;
- inactive plugins/themes;
- updates/backups;
- file permissions/DB privileges;
- backup exposure;
- cache behavior;
- login protection/2FA/rate limiting where appropriate.
Do not stack multiple security plugins.
If a plugin is proposed, prove host/current stack does not already solve the need and use `/plugin-evaluation`.
---

# 34. Browser Compatibility
Test practical latest Chrome/Chromium, Firefox, Edge and WebKit/Safari simulation if available.
Check layout, fonts, forms, menus, motion, sticky UI, SVG masks and focus states.
If real Safari testing is unavailable, state limitation.
---

# 35. Error / Edge States
Test where relevant:
- JS failure/disabled for essential content;
- broken image;
- zero/many Blog posts;
- long Blog/course titles;
- long form input;
- slow network;
- repeated submit;
- back/refresh;
- 404/malformed URL;
- tracking query params;
- small phone;
- zoom;
- reduced motion.
Site should degrade gracefully.
---

# 36. Conversion / Trust
Critically assess:
- first-screen clarity;
- course discovery;
- pricing;
- teacher credibility;
- Trial visibility;
- Contact confidence;
- form friction;
- FAQ usefulness;
- footer trust;
- contact consistency;
- reassurance without fake proof.
Find trust gaps, duplicate/conflicting CTAs, aggressive sales language, missing next steps and unclear form expectations.
---

# 37. Root Cause / Fix Loop
For every confirmed P0–P3:
1. reproduce;
2. isolate;
3. identify root cause;
4. inspect reusable fix;
5. choose smallest fix;
6. implement;
7. unit/integration test as appropriate;
8. E2E;
9. visual test if UI affected;
10. security regression if trust boundary affected;
11. SEO regression if URL/content/meta affected;
12. performance regression if frontend asset affected;
13. review Git diff;
14. close with evidence.
Prioritize `P0 → P1 → P2 → P3 → P4`.
Do not bundle unrelated fixes into one giant change.
Every fix lands on `feature/audit`, merges into `feature/setup` — never `main` — until the single
gated release step in §47.
---

# 38. Full Regression Gate
After remediation run:
**Functional:** all 10 pages, nav/footer, key CTAs, Blog, FAQ, Contact, Free Trial, 404.
**Responsive:** phone/tablet/laptop/desktop/intermediate widths.
**Accessibility:** automated where available + keyboard journeys, focus, labels, contrast, reduced motion.
**SEO:** indexability, canonicals, titles, H1, sitemap, robots, links, schema.
**Security:** secret scan, custom-code review, benign injection regression, CSRF/auth where applicable, public sensitive-file exposure.
**Performance:** representative Lighthouse + image/network verification.
No release approval with P0/P1 open.
Unresolved P2 requires explicit acceptance/documentation.
---

# 39. Git Workflow
Work on `feature/audit`, cut from `feature/setup` (see §47 for the full release mechanics and why
`main` is never touched except through the single gated step).
Before commits:

```bash
git status
git diff --check
git diff
```

Review staged files. Do not blindly `git add .`.
Examples:

```text
test: add production regression checks
fix: harden form input handling
fix: resolve mobile navigation overflow
fix: correct canonical metadata
fix: improve homepage content clarity
perf: optimize hero media
chore: complete production audit
```

No AI attribution, giant AI commit messages or force push.
Merge/push only via approved workflow.
---

# 40. Audit Report
Create/update one report: `AUDIT-REPORT.md`.
Do not create redundant reports.
Required:

## Known State
Carry forward §0's items with their dispositions, so a reader never mistakes them for open findings.

## Phase Log
One row per phase from §46: phase number, date, what completed, exactly where the next session
should resume. This is what makes the audit resumable across sessions — read it first on every
run.

## Executive Summary
Verdict, critical risks, overall assessment.

## Scorecard
| Area | Score /100 | Status |
|---|---:|---|
| Functionality | | |
| UI Quality | | |
| UX | | |
| Responsive | | |
| Accessibility | | |
| Content Quality | | |
| Technical SEO | | |
| SEO Content | | |
| Performance | | |
| Security | | |
| Privacy | | |
| Code Quality | | |
| Production Readiness | | |
Scores must be evidence-based; explain deductions.

## Findings
For each: ID, severity, evidence, root cause, fix, verification.

## Rejected Hypotheses
Meaningful suspected issues disproven by tests.

## SEO Findings
Indexability, metadata, content, links, schema, gaps, ranking-readiness limitations.

## Security Findings
Never include secret values.

## Visual / UX Findings
Reference screenshots/viewport evidence where useful.

## Test Coverage
Unit, integration, E2E, visual, accessibility, SEO, performance, security.

## Remaining Risks
Separate fixed, accepted, client-input, hosting-change and policy/legal items.
---

# 41. Critical Review Standard
Be candid and evidence-based.
Do not:
- praise mediocre work to be polite;
- hide unresolved issues;
- call site production-ready with P0/P1;
- call SEO optimized with indexation/content failures;
- call site secure because no obvious error appeared;
- call responsive done after three presets;
- call unit tests complete when no unit-testable logic exists;
- call forms working without validation/error/delivery tests;
- call copy human only because “As an AI” is absent.
State limitations clearly.
---

# 42. Production Safety
Active/destructive tests belong on local/staging.
Production is limited to passive scans, normal user journeys, benign low-impact inputs and controlled checks unless deeper testing is explicitly approved.
Do not brute-force, DoS/load stress, mass-submit, delete records, extract DB data, upload malicious executables, attempt persistence or probe unrelated sites/accounts.
If deeper active testing is necessary, state exact test, risk, environment and rollback, then ask approval.
---

# 43. Definition of Done
Do not finish until:
- [ ] latest instructions read;
- [ ] release parity confirmed;
- [ ] `main` audited;
- [ ] secret/data-leak audit complete;
- [ ] meaningful test matrix exists;
- [ ] custom PHP/JS tested appropriately;
- [ ] PHP syntax/static checks complete;
- [ ] all 10 pages E2E-tested;
- [ ] all meaningful links/buttons tested;
- [ ] Contact + Free Trial deeply tested;
- [ ] visual/UI/UX review complete;
- [ ] responsive QA complete at/between widths;
- [ ] motion/reduced-motion audited;
- [ ] factual claims reviewed;
- [ ] AI-slop/residue audit complete;
- [ ] accessibility audit complete;
- [ ] technical SEO complete;
- [ ] SEO content/ranking-readiness complete;
- [ ] internal-link crawl complete;
- [ ] performance/CWV audit complete;
- [ ] custom-code security audit complete;
- [ ] dependency/plugin/theme review complete;
- [ ] local/staging benign injection tests complete;
- [ ] production passive security complete;
- [ ] privacy/data handling complete;
- [ ] WordPress hardening complete;
- [ ] browser compatibility checked;
- [ ] edge/error states tested;
- [ ] conversion/trust review complete;
- [ ] P0/P1 resolved;
- [ ] unresolved P2 accepted/documented;
- [ ] regression suite passes;
- [ ] `AUDIT-REPORT.md` complete;
- [ ] docs/checklists updated;
- [ ] `CLAUDE.md` current and <1000 lines;
- [ ] Git diff reviewed;
- [ ] no secrets/test junk introduced.
---

# 44. Final Verdict
Use exactly one:

## `NO-GO — RELEASE BLOCKED`
Use if P0 exists, P1 remains, critical test areas are impossible, release parity is uncertain, or security/indexation failure blocks safe release.

## `CONDITIONAL GO`
Use if no P0/P1 remains, limited P2 remains, risks are understood/accepted and core regression passes.

## `GO — PROFESSIONALLY PRODUCTION READY`
Use only if no P0/P1 remains, important P2 is fixed/accepted, regression passes, security checks pass within scope, technical SEO/content quality pass, responsive/visual/accessibility/functionality pass, no secret/data leakage is found, and remaining limitations are minor/documented.
Never use GO because of deadline pressure.

Note: §0 items 4-7 (email delivery, real contact number, real blog content, privacy policy) all
depend on client input confirmed unavailable at the time this audit was written. A `GO` verdict is
therefore unlikely to be honest until those are resolved — `CONDITIONAL GO` with those four
explicitly listed under Remaining Client Inputs is the expected best outcome of this audit.
---

# 45. Final Response Format
Report:

## Verdict
## Executive Summary
## Audit Scope
## Environment / Release Parity
## Tests Run
- unit
- integration
- E2E
- visual
- accessibility
- SEO
- performance
- security
## Scorecard
## P0/P1 Findings
## P2 Findings
## P3/P4 Findings
## Root Causes
## Fixes Implemented
## Rejected Hypotheses
## Visual / UX Review
## Content / AI-Slop Review
## SEO Review
- technical
- content
- ranking readiness
- limitations
## Security Review
- code
- dynamic tests
- production exposure
- privacy
- secrets
## Performance / Core Web Vitals
## Regression Results
## Git Status / Commits
## Remaining Client Inputs
## Remaining Risks
## Final Verdict
Never include passwords, private keys, secret values or sensitive submission data.
---

# 46. Phases and Resume Protocol
This audit is too large for one session. Run it in six phases; end each with a commit and an
`AUDIT-REPORT.md` + Phase Log update, then stop. A new session resumes by reading the Phase Log
(§40) and continuing from the next unstarted phase — it does not restart from Phase 0.

| Phase | Covers |
|---|---|
| 0 — Baseline (no changes) | §1, §2, §3, §9; create `AUDIT-REPORT.md` (§40) |
| 1 — Repo, secrets, static code, dead code | §7, §7A, §8, §10, §11, §28, §29 |
| 2 — Functional | §12, §13, §14, §35 |
| 3 — Visual, UX, accessibility, content | §15, §16, §17, §18, §19, §20, §21, §22, §26, §36 |
| 4 — SEO + performance | §23, §24, §25, §27 |
| 5 — Security, production exposure, gated release | §30, §31, §32, §33, §34, §47 |
| 6 — Regression + verdict | §38, §43, §44, §45 |

Cross-cutting — apply in every phase, not just once: §4 (method), §5 (severity), §6 (evidence
standard), §37 (fix loop), §39 (git workflow), §41 (critical review standard), §42 (production
safety).

Do not skip ahead to a later phase's sections. Do not re-run an already-completed phase's checks
from scratch — the Phase Log exists specifically so that doesn't happen.
---

# 47. Release Mechanics and Order
**Order is not negotiable: every fix, deletion and version bump lands and is fully verified on
`feature/setup` first. `main`/production is updated strictly afterwards, once, as an explicit
approval-gated step — not as each fix is made.**

Per `.claude/rules/git.md`:
- `main` is production-deployment-only — child theme + `.gitignore` + `.github/workflows/` and
  nothing else. Pushing to `main` triggers `.github/workflows/deploy.yml`, which auto-deploys the
  child theme over FTPS. **A push to `main` is a production deployment**, not a checkpoint.
- Never `git merge` a feature branch into `main`.
- If this working copy may be shared with another session, do not `git checkout main` here — use
  `git worktree add ../<name> main`.

Therefore:
1. All audit work happens on `feature/audit`, cut from `feature/setup`.
2. Merge back via PR into `feature/setup` — never into `main`.
3. Do not deploy to production to verify a fix mid-audit; production verification happens once, in
   Phase 5, after every prior phase's P0/P1 is resolved.
4. The gated release, only after explicit approval:
   a. Take a `backup-verify` checkpoint.
   b. Run `release-check` on the audited theme.
   c. Copy production paths only onto `main`:
      `git checkout feature/setup -- wp-content/themes/easy-quran-classes-child` (in a worktree if
      this working copy is shared).
   d. Push. Confirm the FTPS pipeline ran.
   e. Clear the NGINX cache, then re-verify production: all 10 pages, the assets that previously
      404'd (§0 item 1), HTTPS, no mixed content, forms render, smoke test.
   f. Rollback is the same pipeline in reverse — have it ready before pushing.
5. Do **not** change the `noindex` setting as part of this release (§0 item 2).
