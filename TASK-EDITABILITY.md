# TASK-EDITABILITY — WordPress / Elementor No-Code Editability Audit & Remediation
## Easy Quran Classes
## Goal
Audit the complete Easy Quran Classes WordPress website and ensure normal website editing does not require HTML, PHP, CSS, or JavaScript editing.
The website must remain visually and functionally the same while becoming easy for a normal WordPress administrator to maintain through:
- Elementor drag-and-drop editing;
- WordPress page/post editing;
- WordPress menus;
- Media Library;
- Elementor global styles/site settings;
- normal plugin form/settings screens;
- WordPress theme/customizer/settings controls where appropriate.
The goal is editorial and design maintainability, not eliminating all custom code.
Custom code may remain only when it is true infrastructure/behavior that a normal editor should not need to modify.
Do not overengineer. Do not rebuild working sections unless editability requires it.
---
# 1. Read Before Doing Anything
Read completely:
1. `CLAUDE.md`
2. `DESIGN.md`
3. `TASK-WEBSITE.md`
4. `TASK-AUDIT.md`
5. `TASK-DEPLOY.md`
6. `CPANEL-WORKFLOW.md`
7. `.gitignore`
8. all `.claude/rules/*.md`
9. all relevant `.claude/skills/*/SKILL.md`
10. previous build/audit/deployment reports
11. current repository and WordPress/Elementor implementation
Use the latest approved versions.
If `/superpowers:using-superpowers` or equivalent project skill exists, use it.
Prefer relevant existing skills such as:
- `implementation-workflow`
- `elementor-build`
- `visual-qa`
- `wp-audit`
- `wordpress-debug`
- `plugin-evaluation`
- `git-checkpoint`
- `backup-verify`
- `release-check`
Use browser automation for WordPress Admin / Elementor verification when reliable.
Do not add MCPs/plugins/connectors merely because they exist.

---

# 2. Confirm the Environment
Before any change, report:
- local WordPress URL;
- production URL;
- WordPress version;
- active theme;
- Elementor version;
- Elementor Free/Pro status;
- active plugins;
- Git branch/status;
- relevant custom theme/plugin paths;
- whether local represents the current production release.

Perform implementation/refactoring on local/staging first.

Do not modify production until the approved deployment workflow is used.

Confirmed hosting constraint:
- no Shell;
- no SSH;
- no cPanel Terminal;
- no remote WP-CLI.

---

# 3. Core Requirement — Define “Editable”
A component passes when a normal WordPress administrator can make expected day-to-day changes without opening source files.

## Content must be editable without code
Examples:
- headings;
- paragraphs;
- labels;
- CTA text;
- button links;
- images;
- icons where practical;
- course names/descriptions;
- prices when approved;
- teacher information when approved;
- FAQs;
- Blog content;
- Contact information;
- WhatsApp links;
- form labels/options/messages;
- SEO page metadata through the chosen SEO UI.

## Layout/design should be editable through WordPress/Elementor where reasonably expected
Examples:
- section order;
- container/column layout;
- spacing;
- typography;
- colors;
- backgrounds;
- cards;
- images;
- CTA placement;
- responsive Elementor settings.

## Global site controls should have an editor UI
Examples:
- logo;
- navigation menus;
- site title/favicon;
- global colors;
- global typography;
- reusable header/footer;
- repeated contact/navigation information where practical.

## Infrastructure code may remain code
Examples:
- asset enqueueing;
- accessibility helpers;
- WordPress hooks;
- dynamic query implementation;
- sanitization/escaping;
- lightweight motion behavior;
- template fallbacks;
- performance/security code.

The user must not need to edit infrastructure code for normal content/design changes.

---

# 4. Important Non-Goal
Do not convert every line of CSS/PHP/JS into an editor setting.

That would overengineer the site.

The requirement is:
> A normal administrator should be able to maintain the website, content, and expected visual settings without editing HTML/source code.

Keep implementation code where it is the correct abstraction.

---

# 5. Mandatory TODO / Evidence Loop
Create one living TODO before implementation.

For every component:
- [ ] Identify the content/design owner.
- [ ] Locate exactly where it is edited today.
- [ ] Test the actual editing workflow.
- [ ] Determine whether source-code editing is required.
- [ ] Record evidence.
- [ ] Identify reusable WordPress/Elementor solution.
- [ ] Explain why a new solution is required if reuse is insufficient.
- [ ] Choose smallest maintainable remediation.
- [ ] Back up before structural Elementor/DB changes.
- [ ] Implement locally.
- [ ] Test editor workflow after fix.
- [ ] Test frontend visual parity.
- [ ] Test functionality.
- [ ] Test responsive behavior.
- [ ] Remove obsolete duplicate implementation.
- [ ] Update audit matrix/checklists.
- [ ] Review Git diff.
- [ ] Keep `CLAUDE.md` durable/current and under 1000 lines.

Do not mark a component editable because it could theoretically be changed.

Actually open the appropriate WordPress/Elementor UI and verify it.

---

# 6. Create an Editability Matrix
Create/update:

`EDITABILITY-REPORT.md`

Use:

| Page / Component | Current Owner | Current Edit Path | Code Required? | Target Edit Path | Issue | Fix | Parity Verified? | Status |
|---|---|---|---:|---|---|---|---:|---|

Statuses:
- `PASS`
- `FIXED`
- `BLOCKED`
- `NOT APPLICABLE`

Do not hide limitations.

---

# 7. Audit All 10 Pages
Audit:
1. Home
2. About
3. Courses
4. Teachers
5. Pricing
6. Contact
7. FAQ
8. Blog
9. Free Trial / Enrollment
10. Online Quran Classes for Kids

For every page inspect:
- page editable through Elementor or appropriate WordPress editor;
- page title;
- hero;
- headings;
- body copy;
- images;
- buttons;
- links;
- cards;
- section order;
- backgrounds;
- spacing;
- responsive controls;
- page-specific CTA;
- media;
- SEO metadata UI.

Identify every visible element whose normal edit requires:
- HTML widget;
- PHP file;
- theme file;
- JavaScript;
- raw shortcode editing;
- hardcoded CSS content;
- database manipulation;
- direct Elementor JSON manipulation.

Remediate only when normal editing should be possible from the admin UI.

---

# 8. Elementor Structure Audit
For every Elementor-built page verify that editable sections use normal Elementor:
- Containers;
- Heading widgets;
- Text Editor widgets;
- Image widgets;
- Button widgets;
- Icon widgets;
- native responsive controls;
- reusable classes/global styles.

Flag page content hidden inside:
- one large HTML widget;
- raw HTML blocks;
- embedded page-wide markup;
- hardcoded headings in CSS pseudo-elements;
- hardcoded URLs in JS;
- hardcoded text in PHP templates;
- giant shortcode blobs where normal widgets would be more maintainable.

Preferred remediation:
Convert user-editable content to native Elementor widgets/containers.

Preserve:
- visual appearance;
- responsive behavior;
- accessibility;
- motion;
- performance.

Do not rebuild decorative/infrastructure SVG or JS merely for ideological no-code purity.

---

# 9. HTML Widget Audit
Find every Elementor HTML widget.

Classify:

## A. User-editable content hidden in HTML
Examples:
- headings;
- copy;
- buttons;
- images;
- pricing;
- card content.

These should be converted to normal Elementor/native WordPress controls unless a concrete technical reason prevents it.

## B. Infrastructure/decorative implementation
Examples:
- compact SVG decoration;
- helper markup;
- specialized lightweight behavior.

May remain if:
- editor never needs to modify it;
- it is documented;
- it is safe;
- visual/functionality is better than a heavier dependency.

Do not convert a small decorative SVG into a large plugin dependency.

---

# 10. Hardcoded Content Audit
Search project-owned:
- PHP;
- JS;
- CSS;
- templates;
- shortcodes.

Find customer-facing hardcoded:
- text;
- prices;
- names;
- emails;
- phones;
- WhatsApp numbers;
- course content;
- CTA labels;
- CTA URLs;
- images;
- Blog labels;
- footer copy;
- social links.

For every hardcoded value ask:
> Is this something a normal site administrator could reasonably need to change?

If yes, move it to an appropriate editable WordPress/Elementor control.

If no, keep it in code.

Do not build a custom options framework for trivial immutable strings.

---

# 11. Global Styles Audit
Verify editors can control expected global design settings through Elementor Site Settings or existing WordPress UI.

Audit:
- primary colors;
- text colors;
- typography;
- heading fonts;
- body fonts;
- link/button styles where supported;
- content width;
- site backgrounds where appropriate.

Prefer existing Elementor global tokens.

Avoid duplicated hardcoded values across many widgets.

Do not remove useful child-theme CSS when it is shared infrastructure that normal editors should not need to modify.

---

# 12. Navigation Audit
Verify navigation is managed through WordPress, not hardcoded HTML.

Editors must be able to:
- add/remove menu links;
- reorder items;
- change labels;
- update destinations;
- manage footer navigation.

Header navigation must consume the editable menu.

No source-file editing should be needed for navigation.

---

# 13. Logo / Site Identity Audit
Verify normal UI exists for:
- logo;
- site title;
- favicon/site icon;
- homepage assignment.

No code edit should be necessary for routine identity updates.

Preserve current visuals.

---

# 14. Header Editability Audit
Inspect current header implementation.

Test whether an administrator can change:
- logo;
- menu;
- CTA text/link;
- expected header colors/background;
- relevant spacing/layout.

Decision order:
1. Reuse existing editable Elementor/header solution.
2. Use native WordPress/Hello settings if sufficient.
3. If current header is hardcoded and routine edits require code, convert it to an Elementor-editable header using the smallest reliable existing solution.
4. Only if no suitable solution exists, evaluate one focused maintained plugin through `plugin-evaluation`.

Do not install a large Elementor addon pack.

Do not silently require Elementor Pro.

If full drag-and-drop header editing cannot be achieved with the approved free stack without a dependency, document the exact limitation and evaluate the smallest maintained free solution before changing architecture.

---

# 15. Footer Editability Audit
The administrator should be able to change expected footer content without PHP/HTML:
- logo;
- descriptive copy;
- contact details;
- navigation;
- social links;
- CTA;
- copyright where appropriate.

Use:
- Elementor;
- WordPress menus;
- existing theme controls;
- minimal shared settings only where genuinely helpful.

Avoid duplicated hardcoded contact information.

---

# 16. Blog Editability Audit
Separate content editability from template editability.

## Blog content must be editable
Editors must manage Posts through WordPress:
- title;
- content;
- categories;
- featured image;
- excerpt;
- publish date;
- SEO metadata.

No HTML editing for normal Blog publishing.

## Blog archive/single layout
Audit current implementation.

If layout customization requires code, determine whether this is a real administrator requirement.

Preferred order:
1. reuse existing editable WordPress/Elementor solution;
2. use theme settings/native controls where sufficient;
3. keep a clean coded template if normal publishing needs no code and layout is intentionally developer-owned;
4. if drag-and-drop template editing is explicitly required, evaluate the smallest maintained compatible theme-builder solution.

Do not add a large addon ecosystem merely to expose a rarely changed template.

Document this distinction clearly.

---

# 17. Course / Teacher / Pricing Editability
Verify currently published content is editable from WordPress/Elementor without code.

## Courses
Test:
- names;
- descriptions;
- cards;
- icons/images;
- CTA;
- order.

## Teachers
Test:
- names;
- portraits;
- qualifications;
- specialties;
- card content.

## Pricing
Test:
- price;
- frequency;
- duration;
- features;
- CTA;
- highlighted plan.

Do not introduce CPTs unless editorial requirements genuinely justify them.

For the current ~10-page scope, prefer the existing simpler architecture unless repetition has become a real maintenance problem.

---

# 18. FAQ Editability
Editors must be able to:
- change question;
- change answer;
- reorder items;
- add/remove items through the existing editable component where practical.

Do not leave FAQ copy embedded in JS/PHP.

Custom JS may control accordion behavior but not own the content.

---

# 19. Contact / Free Trial Form Editability
Verify form editing happens through the installed form plugin/WordPress UI.

Editors should manage expected configuration without HTML:
- field labels;
- required fields;
- select options;
- success/error messages;
- recipient configuration where safely supported;
- form placement.

Do not expose credentials/secrets as ordinary page content.

Do not convert secure implementation logic into editable HTML.

Test forms after any refactor.

---

# 20. Repeated Global Information
Find repeated values such as:
- phone;
- WhatsApp;
- email;
- primary CTA URL;
- business name;
- social links.

If repetition creates real inconsistency risk, centralize using the smallest existing editable mechanism.

Preferred:
1. existing theme/WordPress setting;
2. existing Elementor global/template mechanism;
3. existing plugin setting;
4. minimal project-owned settings UI only if genuinely necessary.

Do not introduce ACF/options-framework complexity for a few values unless it materially improves maintainability.

---

# 21. Theme / Plugin Decision Rules
Do not change theme merely to make one component easier to edit.

Hello Elementor remains default unless a real architecture blocker exists.

Do not mix:
- multiple page builders;
- multiple parent themes;
- large Elementor addon suites;
- competing theme builders.

If an additional plugin is necessary:
1. prove current stack cannot satisfy the requirement cleanly;
2. check existing installed plugins first;
3. run `plugin-evaluation`;
4. choose one focused, maintained, lightweight solution;
5. test compatibility;
6. document dependency;
7. preserve performance/security.

Do not use nulled/pirated software.

---

# 22. Preserve Visual Parity
This task must not redesign the website.

Before changing each affected component:
- capture/reference current desktop view;
- capture/reference current mobile view;
- record interaction behavior.

After remediation compare:
- spacing;
- typography;
- colors;
- dimensions;
- images;
- buttons;
- motion;
- responsive behavior;
- focus states.

New editable implementation should be visually equivalent unless fixing a confirmed bug.

If a visual change is unavoidable, document and minimize it.

---

# 23. Preserve Functional Parity
After every refactor test:
- links;
- CTAs;
- mobile menu;
- forms;
- Blog;
- FAQ;
- motion;
- keyboard;
- reduced motion;
- dynamic WordPress content;
- SEO output.

Editability is not a reason to break behavior.

---

# 24. Editor Workflow Verification — Mandatory
For each major editable component, actually test editing in WordPress.

Use safe temporary changes on local/staging.

Example:
1. open page in Elementor;
2. select heading;
3. change text to a temporary marker;
4. update;
5. verify frontend;
6. revert;
7. verify original content returns.

Repeat appropriate tests for:
- text;
- image;
- button URL;
- section layout;
- menu;
- header;
- footer;
- FAQ;
- forms;
- Blog post.

Do not claim editability without testing the real editor workflow.

---

# 25. Drag-and-Drop Verification
For Elementor-managed layouts verify:
- containers selectable;
- widgets selectable;
- sections/widgets reorderable;
- spacing editable through controls;
- responsive settings available;
- text/images/buttons not trapped in raw HTML;
- no broken/unknown widgets;
- changes save correctly.

Do not perform destructive permanent rearrangements during testing.

Use temporary change/revert where needed.

---

# 26. Responsive Editor Verification
Ensure editability changes do not create desktop-only layouts.

After conversion verify:
- desktop controls;
- tablet controls;
- mobile controls;
- wrapping;
- image sizing;
- button wrapping;
- container ordering;
- visibility states;
- no horizontal overflow.

Test real frontend widths after editor verification.

---

# 27. Accessibility Preservation
When replacing custom markup with Elementor/native widgets, verify:
- heading levels;
- link/button semantics;
- alt text;
- labels;
- keyboard navigation;
- focus;
- accordion semantics;
- form labels;
- reduced-motion behavior.

Do not trade accessibility for editability.

---

# 28. SEO Preservation
Verify editability changes do not break:
- page titles;
- H1/H2 hierarchy;
- canonical;
- metadata;
- internal links;
- schema;
- sitemap;
- Blog URLs;
- image alt text.

Preserve intended copy unless fixing a separately confirmed issue.

---

# 29. Performance Preservation
Audit before/after:
- DOM size;
- CSS/JS weight;
- plugin count;
- network requests;
- image behavior;
- LCP;
- CLS;
- motion performance.

Do not replace lightweight code with a large addon/plugin if the cost is disproportionate.

Minimal maintainable editability is the goal.

---

# 30. Security Preservation
Do not make sensitive settings more exposed merely to make them editable.

Never place in Elementor/page content:
- passwords;
- SMTP secrets;
- API secrets;
- DB credentials;
- FTP/cPanel credentials;
- private keys.

Keep infrastructure settings in protected configuration.

Any new admin settings code must use:
- capability checks;
- nonces;
- validation;
- sanitization;
- escaping.

---

# 31. Root-Cause / Remediation Process
For every non-editable component:

```text
Find component
→ reproduce editing limitation
→ identify why it is hardcoded
→ check existing editable solution
→ choose smallest migration
→ back up
→ implement locally
→ test editor
→ test frontend
→ compare screenshots
→ test responsive/function/accessibility
→ remove obsolete implementation
→ document
```

Work component-by-component.

Do not convert unrelated systems at once.

---

# 32. Git Rules
Work on a focused branch unless repo rules specify another safe workflow.

Before every commit:

```bash
git status
git diff --check
git diff
```

Stage intentionally.

Do not blindly run:

```bash
git add .
```

Commit examples:

```text
fix: make homepage content editable
fix: move header to editable template
fix: make faq content editor-managed
fix: expose footer content to wordpress
refactor: replace hardcoded page content
docs: add wordpress editing guide
```

No AI attribution.

Do not force push.

Do not include DB dumps/backups/secrets.

Remember: Elementor layout changes live in the database and require approved backup/deployment handling; Git alone does not capture them.

---

# 33. Documentation
Maintain one primary report:

`EDITABILITY-REPORT.md`

Include:

## Executive Summary

## Editability Scorecard

| Area | Status | Notes |
|---|---|---|
| Pages | | |
| Header | | |
| Footer | | |
| Navigation | | |
| Global Styles | | |
| Courses | | |
| Teachers | | |
| Pricing | | |
| FAQ | | |
| Forms | | |
| Blog Content | | |
| Blog Templates | | |
| Responsive Editing | | |
| SEO Editing | | |

## Editability Matrix

Use the matrix from Section 6.

## Findings
For each:
- ID;
- current edit method;
- problem;
- root cause;
- remediation;
- verification.

## Changes Implemented

## Intentionally Code-Owned Components
List components intentionally left in code and explain why the administrator does not need to edit them.

## Plugin / Dependency Decisions

## Visual Parity Results

## Functional Regression Results

## Remaining Limitations

---

# 34. Create a Simple Editor Handover Guide
Create/update:

`WORDPRESS-EDITING-GUIDE.md`

Explain:
- edit a page with Elementor;
- change text;
- change an image;
- edit a button/link;
- reorder a section;
- edit mobile/tablet settings;
- edit navigation;
- edit header;
- edit footer;
- edit FAQ;
- edit pricing;
- edit teacher/course content;
- publish/edit Blog post;
- edit forms;
- edit SEO metadata;
- what must not be edited manually.

Keep it concise and practical.

---

# 35. Definition of Done
Do not finish until:
- [ ] all 10 pages audited;
- [ ] editability matrix complete;
- [ ] every visible content area has a documented edit path;
- [ ] normal page content requires no HTML/PHP editing;
- [ ] text/image/button edits work through WordPress/Elementor;
- [ ] page sections are drag-and-drop editable where Elementor owns them;
- [ ] navigation is WordPress-managed;
- [ ] logo/site identity is admin-editable;
- [ ] header editability audited/corrected where required;
- [ ] footer editability audited/corrected where required;
- [ ] courses editable;
- [ ] teachers editable;
- [ ] pricing editable;
- [ ] FAQ editable;
- [ ] Contact form editable through admin UI;
- [ ] Free Trial form editable through admin UI;
- [ ] Blog publishing requires no code;
- [ ] Blog template editability limitation/solution documented;
- [ ] global visual settings use appropriate editor controls where expected;
- [ ] every HTML widget classified;
- [ ] unnecessary content-bearing HTML removed;
- [ ] hardcoded customer-facing content audited;
- [ ] repeated global content audited;
- [ ] real editor workflow tests complete;
- [ ] desktop/mobile visual parity verified;
- [ ] functionality regression passes;
- [ ] responsive regression passes;
- [ ] accessibility preserved;
- [ ] SEO preserved;
- [ ] performance not materially degraded;
- [ ] security not weakened;
- [ ] obsolete duplicate code removed;
- [ ] `EDITABILITY-REPORT.md` complete;
- [ ] `WORDPRESS-EDITING-GUIDE.md` complete;
- [ ] docs/checklists updated;
- [ ] `CLAUDE.md` current and under 1000 lines;
- [ ] Git diff reviewed;
- [ ] no secrets/backups/DB dumps introduced.

---

# 36. Final Verdict
Use one:

## `NOT READY — CODE EDITING STILL REQUIRED`
Use if routine administrator changes still require HTML/PHP/CSS/JS editing.

## `PARTIALLY EDITABLE — DOCUMENTED LIMITATIONS`
Use if normal content is editable but a meaningful visual/template area still requires developer code or an unresolved dependency decision.

## `READY — NORMAL SITE MANAGEMENT IS NO-CODE EDITABLE`
Use only if:
- normal content edits require no source-code changes;
- static pages are editable through Elementor/WordPress;
- expected drag-and-drop page layout editing works;
- navigation/header/footer have appropriate admin edit paths;
- forms/Blog are admin-editable;
- remaining custom code is infrastructure only;
- visual/function/responsive/accessibility/SEO parity passes.

---

# 37. Final Response Format
Report:

## Verdict
## Executive Summary
## Environment
## Audit Scope
## Editability Scorecard
## Non-Editable Areas Found
## Root Causes
## Fixes Implemented
## Elementor / WordPress Editor Verification
## Header / Footer / Navigation
## Forms / Blog / Dynamic Content
## Intentionally Code-Owned Components
## Visual Parity
## Functional / Responsive Regression
## Accessibility / SEO / Performance Checks
## Plugin / Dependency Decisions
## Git Status / Commits
## Remaining Limitations
## Documentation Created
## Final Verdict

Do not claim full no-code editability unless actual editor workflows were tested.
