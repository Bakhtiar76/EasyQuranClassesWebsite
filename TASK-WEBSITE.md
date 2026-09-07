# TASK-WEBSITE — Build the Complete Easy Quran Classes WordPress Website

## Purpose
Build the complete **Easy Quran Classes** website locally in WordPress using the approved local-first workflow.
This task begins only after the environment/setup task is complete and the local WordPress site is healthy.
Deliver a polished **10-page**, responsive, dynamic, accessible, SEO-ready, performance-conscious WordPress website with:
- shared header/footer;
- Elementor-based page design;
- native WordPress blog architecture;
- Contact and Free Trial forms;
- engaging but restrained motion/microinteractions;
- cross-device QA;
- local backups/checkpoints.
**Do not deploy to cPanel in this task.**

---

# 1. Read First
Before editing anything, read:
1. `CLAUDE.md`
2. `DESIGN.md`
3. `CPANEL-WORKFLOW.md`
4. `.gitignore`
5. all `.claude/rules/*.md`
6. relevant `.claude/skills/*/SKILL.md`
7. completed setup report/task
8. this file
If instructions conflict, follow the safer rule and report it.
`DESIGN.md` is the detailed visual/motion/responsive source of truth.  
`CLAUDE.md` is the architecture/security/implementation source of truth.  
This file is the execution plan.

---

# 2. Confirm the Target
Allowed:
- approved **local WordPress** only.
Not allowed:
- production edits;
- cPanel deployment;
- remote SSH/WP-CLI;
- DNS/SSL/email-routing changes;
- production credentials in project files.
Before WordPress writes, confirm:
- local URL;
- WordPress root;
- active theme;
- Elementor status;
- Git branch/status;
- target is local, not production.
If uncertain, stop.

---

# 3. Mandatory Implementation Loop
Maintain one task TODO.
For every phase/page:
- [ ] Inspect repo + current WordPress/Elementor state.
- [ ] Find reusable code, styles, templates, components, plugins or helpers.
- [ ] Reuse when suitable; otherwise state briefly why new work is needed.
- [ ] Choose the smallest maintainable solution.
- [ ] Prefer native WordPress/Elementor before new code/plugins.
- [ ] Define relevant edge cases before coding.
- [ ] Put logic in the correct existing file/location.
- [ ] Implement only the required scope.
- [ ] Test normal + relevant edge cases.
- [ ] Remove obsolete/debug/duplicate code within scope.
- [ ] Update relevant docs/checklists.
- [ ] Keep `CLAUDE.md` durable, current and under 1000 lines.
- [ ] Review Git status/diff.
- [ ] Keep secrets/backups/DB dumps/uploads/release artifacts out of Git.
Do not create unnecessary files/folders or unrelated refactors.

---

# 4. Reference Priority
Use this order:
1. **Client reference images/content in `Assets/`**
2. Easy Quran Classes logo/brand assets
3. `DESIGN.md`
4. `https://riwaqalquran.com/` for UX/content/motion inspiration
5. Curated design/motion references
6. Official WordPress/Elementor documentation
7. General best practice
Client screenshots are the primary visual reference.
Do not:
- clone Riwaq;
- copy its text/images/code;
- copy pricing/testimonials/statistics/teacher credentials;
- reproduce a signature interaction one-to-one.
Study patterns, then reinterpret them for Easy Quran Classes.

---

# 5. Phase A — Assets & Content Audit
Recursively inspect `Assets/`.
Classify assets:
- `REFERENCE ONLY`
- `LOGO / BRAND`
- `CONTENT IMAGE`
- `DECORATIVE`
- `UNSURE — DO NOT PUBLISH`
Record verified:
- copy;
- course names/details;
- pricing;
- teacher info;
- testimonials;
- contact details;
- WhatsApp;
- social links;
- business facts.
Reference screenshots must be analyzed for:
- section order;
- hero/header/footer;
- typography/colors;
- cards;
- pricing/teacher/course layouts;
- image masks/arches;
- Islamic motifs;
- CTA hierarchy;
- spacing;
- button treatment;
- responsive clues.
Do not use reference screenshots themselves as website content.

---

# 6. Phase B — Inspiration Research

## Riwaq
Inspect:
`https://riwaqalquran.com/`
Study only useful patterns:
- navigation;
- free-trial journey;
- course discovery;
- How It Works;
- teacher trust;
- pricing explanation;
- FAQ;
- blog;
- repeated CTA flow;
- internal linking;
- motion/interaction.

## Additional design/motion research
Purposefully inspect a small set of relevant references from:
- Land-book
- Awwwards Inspiration
- Lapa Ninja
- Recent Design
- Mobbin if already accessible
- official Elementor documentation
Do not browse aimlessly.
Create a concise **Motion Reference Matrix** with 6–10 high-value patterns:
| Pattern | Source | EQC use | Benefit | Method | Mobile | Risk |
|---|---|---|---|---|---|---|
Only keep ideas that improve:
- hierarchy;
- storytelling;
- orientation;
- feedback;
- conversion;
- perceived quality;
- brand personality.

---

# 7. Phase C — Content Integrity
Create a concise factual-content matrix:
| Item | Source | Verified? | Publish? | Action |
|---|---|---:|---:|---|
Cover at least:
- courses/descriptions;
- teachers/qualifications;
- pricing;
- testimonials;
- experience/ratings/student counts;
- class format/trial/scheduling;
- certificates;
- contact details;
- guarantees/accreditation;
- payment information.
Never invent factual claims.
If a screenshot shows unverified facts:
1. omit;
2. rewrite around verified generic information; or
3. use a clearly marked local placeholder.
Never disguise placeholder content as fact.

---

# 8. Sitemap — Build These 10 Pages
1. Home — `/`
2. About — `/about/`
3. Courses — `/courses/`
4. Teachers — `/teachers/`
5. Pricing — `/pricing/`
6. Contact — `/contact/`
7. FAQ — `/faq/`
8. Blog — `/blog/`
9. Free Trial / Enrollment — `/free-trial/`
10. Online Quran Classes for Kids — `/online-quran-classes-for-kids/`
Do not add:
- LMS;
- payments;
- student portal;
- individual course-detail pages unless explicitly approved.

---

# 9. Phase D — WordPress Foundation
Inspect/reuse the existing setup.
Default stack:
- WordPress;
- Hello Elementor;
- Easy Quran Classes child theme where needed;
- Elementor Free Containers/Flexbox;
- native WordPress Posts/menus/media/archives;
- minimal shared CSS;
- minimal vanilla JS;
- SVG motion/decorative assets;
- only essential approved plugins.
Configure locally:
- page structure;
- static homepage;
- blog archive behavior;
- permalinks;
- site title/tagline;
- navigation;
- footer navigation;
- favicon if approved;
- local indexing disabled.

## Theme rule
Use Hello Elementor unless a **measurable blocker** exists.
Do not:
- mix themes;
- theme-hop;
- switch mid-build for one convenient widget.
A theme change requires:
1. exact limitation;
2. evidence native WordPress/Elementor/child theme cannot solve it cleanly;
3. migration/risk assessment;
4. approval.

## Elementor rule
Prefer:
- Site Settings globals;
- Containers/Flexbox;
- shared classes;
- reusable patterns;
- native widgets.
Avoid:
- spacer layouts;
- random one-off margins;
- excessive negative margins;
- primary-layout absolute positioning;
- addon-pack bloat;
- direct `_elementor_data` editing;
- raw SQL manipulation.

## Header/footer
Preferred order:
1. reuse existing clean solution;
2. native WordPress/Hello where sufficient;
3. small child-theme implementation;
4. addon only after plugin evaluation.
Keep future admin management practical.

## Blog
Use native WordPress Posts.
If Elementor Free cannot build required archive/single templates cleanly, use lightweight child-theme templates.
Never convert blog posts into static Elementor pages.

---

# 10. Plugin Policy
Audit first.
### Forms
If no suitable plugin exists:
- evaluate current maintained free options;
- install one lightweight solution;
- use it for Contact + Free Trial where possible;
- document why it was necessary.
### SEO
Use one SEO solution only.
### Caching/security
Do not add production-oriented caching/security plugins during local design without hosting justification.
### Elementor addons
Default: **none**.
Do not install a large addon bundle for one design detail.

---

# 11. Phase E — Global Design System
Implement `DESIGN.md` before building disconnected pages.
Establish shared:
- colors;
- fonts;
- type scale;
- max widths;
- spacing;
- card gaps/radii;
- buttons;
- forms;
- borders/shadows;
- icon style;
- Islamic ornament style;
- responsive rules;
- motion tokens.
Reuse shared tokens/classes instead of repeating CSS per page.

---

# 12. Phase F — Responsive & Dynamic Architecture
Responsiveness is **continuous**, not three Elementor presets.
Build for roughly **320px → 2560px+**.
Use:
- Flexbox;
- CSS Grid where useful;
- `repeat()`;
- `minmax()`;
- `auto-fit/auto-fill`;
- wrapping;
- `clamp()`;
- `min()/max()`;
- bounded `max-width`;
- `aspect-ratio`;
- WordPress `srcset/sizes`;
- content-driven breakpoints.
Avoid:
- fixed desktop canvases;
- fixed section heights;
- fixed card heights that clip content;
- duplicate desktop/mobile content;
- many device-specific patches;
- arbitrary negative-margin fixes.
Dynamic WordPress behavior should include:
- WordPress-driven menus;
- WordPress Posts for Blog;
- dynamically queried latest posts;
- archive/category/single templates;
- shared header/footer;
- WordPress site URLs/settings instead of hardcoded domains.
Do not add CPTs/headless/React/Vue/custom DB architecture without a real requirement.
### Cross-device behavior
Support:
- mouse;
- trackpad;
- touch;
- keyboard;
- portrait/landscape.
No important content/function may depend only on hover.

---

# 13. Phase G — Motion & Interaction System
The site should feel **alive, premium and engaging**, but calm and respectful.
Preferred implementation order:
1. CSS transitions/transforms/keyframes
2. Elementor Free native effects where available
3. small reusable vanilla JS / `IntersectionObserver`
4. lightweight SVG motion
5. animation library only when clearly justified and reviewed
Do not assume Elementor Pro features.
### Motion language
Choose a coherent subset such as:
- staged hero reveal;
- arch/image reveal;
- subtle Islamic SVG line/ornament motion;
- section-heading reveal;
- staggered course cards;
- restrained card hover/focus lift;
- How It Works path progression;
- teacher/pricing microinteractions;
- smooth FAQ accordion;
- blog image/arrow interaction;
- sticky-header state transition;
- subtle final-CTA motion.
Use roughly **one signature motion idea per major section**.
Avoid:
- scroll hijacking;
- cursor replacement;
- blocking preloaders;
- aggressive parallax;
- constant text movement;
- autoplay hero video by default;
- heavy particle/canvas effects;
- excessive 3D tilt;
- constant CTA pulsing.
Prefer animating:
- `transform`;
- `opacity`;
- selective `clip-path`;
- SVG strokes/transforms.
All custom motion must support `prefers-reduced-motion`.
Reduced-motion visitors must receive immediately visible, usable content.
Simplify motion on mobile.

---

# 14. Phase H — Shared Site Shell

## Header
Build:
- logo;
- desktop navigation;
- active state;
- Free Trial CTA;
- responsive mobile menu;
- keyboard accessibility;
- scroll state only if useful.
Recommended visible nav:
- Home
- About
- Courses
- Teachers
- Pricing
- Contact
- Free Trial
Place Blog/FAQ/Kids logically in secondary navigation/footer if needed.

## Footer
Build:
- dark forest treatment;
- logo/about summary;
- verified contact info only;
- quick links;
- course links;
- Blog/FAQ/Kids;
- Free Trial CTA;
- social links only if supplied;
- copyright.
Do not invent contact/address/social details.

---

# 15. Phase I — Homepage First
Build Home first and use it to prove the visual + motion system.
Recommended structure:
1. Hero
2. Trust/benefits
3. About/accessibility
4. Courses
5. How It Works
6. Teachers
7. Pricing
8. Testimonials only if verified
9. FAQ preview
10. Latest Blog posts
11. Final Free Trial CTA
Initial course names unless Assets says otherwise:
- Noorani Qaida
- Quran Reading with Tajweed
- Tajweed Course
- Quran Tafseer
- Quran Memorization
- Islamic Studies
Write original concise descriptions without unverified claims.
### Home acceptance
Before continuing:
- compare visually against client references;
- compare motion against selected Motion Reference Matrix;
- verify hierarchy/spacing;
- verify desktop/tablet/mobile;
- verify reduced motion;
- verify no overflow;
- verify navigation/CTAs;
- verify image cropping;
- verify no unverified facts;
- verify no significant console errors;
- verify no obvious motion-induced CLS/jank.
Do not stop after Home unless genuinely blocked.
Then continue through all remaining pages.

---

# 16. Phase J — Remaining Pages
Reuse the validated global components and motion language.

## About
Include:
- hero;
- mission/purpose;
- who the academy serves;
- accessibility;
- teaching approach;
- learning journey;
- factual trust section;
- CTA.
Do not invent history/founder stats.

## Courses
Include:
- hero;
- course intro;
- six course cards;
- choose-the-right-course guidance;
- beginner pathway;
- learning approach;
- FAQ preview;
- Free Trial CTA.

## Teachers
Include:
- hero;
- verified teacher/matching explanation;
- real profiles only where supplied;
- teaching approach;
- student expectations;
- CTA.
If teacher data is missing, use a polished structure without fake names/photos/qualifications.

## Pricing
Include:
- hero;
- pricing explanation;
- verified plans only;
- verified inclusions only;
- plan guidance;
- pricing FAQ;
- Free Trial CTA.
If screenshot prices are unverified, use clear placeholders or inquiry-led presentation.

## Contact
Include:
- hero;
- verified contact options;
- accessible form;
- WhatsApp only if verified;
- relevant FAQ/trial links.
No map without verified public location.

## FAQ
Create accessible grouped FAQs for:
- classes;
- beginners;
- children;
- courses;
- teachers;
- scheduling;
- Free Trial;
- device requirements;
- pricing/payment only when factual.
Use minimal JS if custom accordion behavior is needed.

## Blog
Use WordPress Posts.
Build:
- archive;
- responsive cards;
- featured image;
- category/date/excerpt;
- pagination where needed;
- empty state;
- readable single-post template;
- sensible internal links.
Demo posts must be clearly local/demo and not accidentally released as client-authored content.

## Free Trial / Enrollment
Include:
- concise value proposition;
- what happens next;
- accessible form;
- minimal necessary fields;
- success/error states;
- alternative verified contact;
- FAQ.
Potential fields:
- student name;
- age range;
- current level;
- course;
- availability;
- country/timezone;
- guardian name if relevant;
- email;
- phone/WhatsApp;
- notes.
Collect only what the business genuinely needs.
Do not send real emails during local QA.

## Online Quran Classes for Kids
Include:
- parent-focused hero;
- who classes suit;
- beginner pathway;
- age-appropriate learning language;
- relevant courses;
- online learning experience;
- teacher matching;
- How It Works;
- kids FAQ;
- Free Trial CTA.
Do not claim safeguarding checks, specific ages, guaranteed progress or certifications unless verified.

---

# 17. Content & Media Rules
Client-provided copy:
- preserve factual meaning;
- improve grammar/clarity where useful.
Missing copy:
- write original, natural, concise, respectful, welcoming content.
Avoid generic AI-style language such as:
- “unlock your potential”;
- “transformative journey”;
- “world-class”;
- “revolutionary”.
Do not copy Riwaq wording.
### Images
Use approved assets first.
For published images:
- meaningful filenames;
- correct aspect ratio;
- useful alt text;
- proper dimensions;
- compression;
- WebP/AVIF where practical;
- below-fold lazy loading;
- no lazy-loading likely hero LCP.
Prefer SVG/CSS for decorative motion.
Third-party animated assets require:
- verified source/license;
- suitable file weight;
- attribution check;
- static/reduced-motion fallback.

---

# 18. Responsive QA
Test representative widths at minimum:
### Phones
- 320x568
- 360x800
- 390x844
- 430x932
### Tablet
- 768x1024
- 1024x768
### Laptop/Desktop
- 1280x800
- 1366x768
- 1440x900
- 1920x1080
### Wide
- 2560x1440 when tooling permits
Also resize through intermediate widths.
For every page verify:
- header/menu;
- typography wrapping;
- content width/gutters;
- cards/grids;
- CTA groups;
- forms;
- image crops;
- SVG/ornament overflow;
- footer;
- touch targets;
- horizontal overflow;
- sticky/fixed UI;
- hover/focus/tap equivalents;
- mobile motion simplification.
Where practical check:
- phone/tablet portrait + landscape;
- keyboard;
- touch-like behavior;
- 200% browser zoom for key pages/components;
- long labels/text.
A page is not responsive merely because Elementor preview looks correct.

---

# 19. Browser, Motion & Visual QA
Use Playwright/browser tools where reliable against localhost.
For each page:
- inspect real rendered desktop/mobile layouts;
- compare with client-reference visual language;
- observe motion in real time;
- check reveal timing/stagger;
- test hover/focus/tap states;
- test reduced-motion when possible;
- check console errors;
- check broken links/images;
- test navigation;
- test forms where relevant;
- look for CLS/scroll jank.
If Elementor editor automation is brittle, use safer manual/admin editing and keep browser automation for QA.
Never claim a test that was not run.

---

# 20. Accessibility
Target WCAG 2.2 AA-oriented implementation.
Verify:
- one H1/page;
- logical heading order;
- visible labels;
- readable contrast;
- keyboard nav/forms/FAQ;
- focus-visible states;
- useful link text;
- meaningful alt text;
- decorative assets ignored by assistive tech;
- practical 44x44 targets;
- no color-only meaning;
- reduced-motion support;
- no essential content hidden if JS/animation fails;
- no flashing/strobing.
Accessibility outranks exact screenshot imitation.

---

# 21. SEO
For all pages:
- clean slugs;
- useful H1;
- logical H2/H3;
- natural search intent;
- title/meta description;
- internal links;
- image alt text;
- canonical;
- Open Graph basics;
- sitemap;
- robots handling;
- factual schema only.
Suggested intents:
- Home: online Quran classes
- Courses: online Quran courses
- Teachers: online Quran teachers / Quran tutors
- Pricing: online Quran classes pricing
- Kids: online Quran classes for kids
- Free Trial: Quran trial class / Quran class enrollment
No keyword stuffing.
No fake LocalBusiness schema/address.
Keep local indexing disabled.

---

# 22. Blog Admin Usability
Confirm future admins can:
- create Posts;
- assign categories;
- add featured images;
- edit excerpts;
- edit SEO metadata;
- schedule/publish;
- preview/update.
Document a concise publishing procedure in handover notes.

---

# 23. Functional QA
Verify:
- all 10 URLs resolve;
- homepage assignment;
- menus/logo;
- Free Trial and Contact CTAs;
- course CTA behavior;
- Blog archive/single/category behavior;
- Contact form validation;
- Free Trial form validation;
- success/error states;
- no unwanted live email during local QA;
- FAQ behavior;
- mobile navigation;
- 404 behavior;
- no hardcoded production/local URL mistakes.

---

# 24. Performance Pass
Focus on high-value issues:
- LCP hero;
- oversized images;
- unnecessary plugins;
- font weights/requests;
- excessive Elementor DOM;
- render-blocking assets;
- unnecessary JS;
- third-party embeds;
- CLS;
- animation JS weight;
- scroll observers/listeners;
- expensive effects;
- continuous mobile animation.
Run Lighthouse or equivalent if available.
Do not install a cache plugin only to improve localhost scores.

---

# 25. Git & Checkpoints
Before commits:
1. `git status`
2. `git diff --check`
3. review diff
4. run relevant checks
5. stage intended files only
6. review staged diff
Use short human commit messages, e.g.:
- `feat: add global site styles`
- `feat: build homepage layout`
- `feat: add core marketing pages`
- `feat: add blog templates`
- `feat: add contact and trial forms`
- `fix: refine responsive layouts`
- `chore: complete local site qa`
Never add AI attribution.
Do not push unless authorized.
Because Elementor/database state is not fully in Git, create local DB/filesystem backups at major milestones.
### Checkpoints
1. Foundation
   - assets/content audit;
   - inspiration + Motion Matrix;
   - design/motion system;
   - header/footer/navigation.
2. Home
   - Home complete + visual/motion/responsive QA.
3. Core pages
   - About, Courses, Teachers, Pricing.
4. Conversion/content
   - Contact, FAQ, Blog, Free Trial, Kids.
5. Full-site QA
   - responsive;
   - motion/reduced-motion;
   - accessibility;
   - forms;
   - SEO;
   - performance;
   - links/content integrity.
At each checkpoint:
- backup local DB/filesystem;
- review Git;
- update checklist.
Do not ask approval at every checkpoint unless required by risk/dependency/factual blockage.

---

# 26. Explicitly Do Not
Do not:
- deploy to cPanel;
- modify production;
- use production credentials;
- create LMS/payments/student portal;
- scrape/copy Riwaq;
- invent facts/pricing/testimonials/teachers/certifications/contact details;
- copy another site's signature animation one-to-one;
- install large Elementor addon packs without need;
- add GSAP/Lottie/etc. without a specific reviewed reason;
- directly manipulate Elementor data;
- use raw SQL search/replace;
- create competing implementations;
- leave debug/dead/temp code;
- use scroll hijacking, cursor replacement, heavy particles or constant CTA pulsing.

---

# 27. Definition of Done
Complete only when:
- [ ] asset/reference audit done;
- [ ] Riwaq/design/motion research done without copying;
- [ ] Motion Reference Matrix done;
- [ ] factual-content matrix done;
- [ ] global design system done;
- [ ] global responsive architecture done;
- [ ] coherent motion system done;
- [ ] header/footer/navigation done;
- [ ] all 10 pages done;
- [ ] blog archive + single-post template done;
- [ ] Contact + Free Trial forms work locally;
- [ ] native WordPress Posts used for Blog;
- [ ] dynamic WordPress content is not unnecessarily hardcoded;
- [ ] phone/tablet/laptop/desktop/wide QA done;
- [ ] intermediate-width fluidity checked;
- [ ] mobile/touch/hover behavior handled;
- [ ] reduced-motion checked;
- [ ] no obvious animation CLS/jank;
- [ ] accessibility pass done;
- [ ] SEO foundation done;
- [ ] performance pass done;
- [ ] no unverified facts published as true;
- [ ] no broken links/images;
- [ ] no significant frontend console errors;
- [ ] Git reviewed;
- [ ] local backup checkpoint created;
- [ ] docs/checklists updated;
- [ ] `CLAUDE.md` remains concise/current;
- [ ] no cPanel/production deployment occurred.

---

# 28. Final Report
Report:

## Build Summary

## Local Environment
- local URL
- WordPress
- theme
- Elementor
- relevant plugins

## References Used
- client Assets
- Riwaq patterns used
- other inspiration sources
- what was intentionally not copied

## Pages Completed
List all 10 URLs/status.

## Shared Components
- design system
- header/navigation
- footer
- forms
- reusable components

## Motion / Interaction
- selected patterns
- implementation method
- mobile simplification
- reduced-motion behavior
- any external dependency and why

## Content Integrity
- verified facts used
- placeholders/client inputs still needed

## Custom Code / Plugins
- files changed
- reuse decisions
- custom-code reasons
- plugins added + reason

## Testing
- responsive
- browser
- forms
- links
- console
- accessibility
- SEO
- performance
- motion

## Blog Verification

## Backups / Checkpoints

## Git
- branch
- commits
- uncommitted changes
- final suggested concise commit if needed

## Remaining Client Inputs

## Known Risks / Limitations

## Production Readiness
State exactly:
`LOCAL BUILD COMPLETE — NOT YET DEPLOYED TO CPANEL`
Do not prepare/deploy the cPanel release until a separate release task is approved.
