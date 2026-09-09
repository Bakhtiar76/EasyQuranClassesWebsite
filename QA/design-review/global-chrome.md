# Global chrome close review

Status: implemented and independently verified; checkpoint ready.

## Reference calibration and applicable lessons

Read `QA/LESSONS.md` lessons 1–4. Studied `Website Layout.png`, `Home.jpeg` (1307×967) and `End.jpeg` (1489×978) at native resolution. The master composite is 992×1586 and includes browser chrome; it is not a 1920-pixel image or a complete bottom-of-page capture. Use separate section images for legible geometry. Coordinates below are native image pixels; scale horizontal and vertical geometry together by 1920/reference width for a 1920 comparison, retaining fluid bounds and readable type below desktop.

Baseline measured by Chrome DevTools at 1920×1080: document content width 1905 (15px scrollbar). Current header x252.5 y17.6, width1400 height77.2. Footer CTA width1336 height422.6 with padding52; footer columns width1336 height374.9; copyright bare text height55.8. Computed fonts are Manrope and DM Serif Display. Source tokens currently set content max1400 although DESIGN.md says1240; neither matches the reference's nearly full-width chrome.

| # | Section | Reference shows | Build currently does | Severity | Fix (file + change) | Reuses |
|---|---|---|---|---|---|---|
| 1 | Header frame | Home: x41–1273, y28–131, width1232 (~94.3% canvas), height103; generous inset around logo | 1400×77 at1920; only73% viewport width | P1 | shell.css + tokens.css: separate ~94% chrome width capped at1810 and fluid ~100–150px header bar | eqc-header-bar, radius-pill, shadow-card |
| 2 | Logo | Home mark x98–168, text x179–334; EASY QURAN above letterspaced CLASSES with short gold rules | Mark52 high + single title-case Easy Quran Classes, no tagline | P1 | header.php/footer.php/template-tags.php: shared live-text lockup; scale mark and typography via tokens | eqc_logo_mark_svg, existing approved traced mark; new lockup helper justified to avoid duplicating shared markup |
| 3 | Navigation | Six entries Home, About Us, Courses, Teachers, Pricing, Contact Us; active bronze underline | Eight entries, including Blog and duplicate Free Trial; About/Contact shortened | P0 | local scaffolding/menu reconciliation: reference labels/order; retain WordPress menu ownership | primary menu, wp_update_nav_menu_item |
| 4 | Header CTA | Gift icon + FREE TRIAL, bronze pill x1062–1226 y54–105 | Calendar + mixed-case Free Trial, 139.6×56 | P1 | header.php and sprite generator: gift symbol, uppercase label, scaled pill | eqc-btn--bronze; Lucide Gift absent from current sprite, extend generator |
| 5 | CTA copy | Two lines: Your First Class Is Free. / Start This Week.; second gold. Body includes usually within a day | Heading single-color and body omits final promise | P0 | footer.php: exact copy and explicit display line; register timing claim | existing footer CTA |
| 6 | CTA anatomy | End: panel x44–1447 y31–451; white action capsule x638–1420 y234–387 containing four avatars, +1.5K circle, gold button | Avatar pill, bronze button and WhatsApp separate, wrapped below copy; 5,000+ incorrect | P1 | footer.php/shell.css: one capsule; cream stat circle; gold button and circled double chevron | avatar-stack, existing teacher media; add ChevronsRight to sprite |
| 7 | Footer panel | End x44–1447 y469–956; cream rounded panel, three columns divided at x498 and897 | No rounded enclosing panel or vertical column rules | P1 | footer.php/shell.css: enclosing panel, measured grid/padding and rules | footer-grid, existing tokens |
| 8 | Footer details | GET IN TOUCH! / QUICK LINKS with short gold rules; circular green icon badges, hairlines between contact rows; reference address/phone | Mixed case headings, plain icons; address absent and phone dummy differs | P1 | footer.php/shell.css + local contact seed values; keep theme mods editable | footer-heading/contact, Customizer values |
| 9 | Footer links | Two columns × four links: About/Give Donation/Education Support/Our Campaign and Contact/Privacy Policy/Terms & Conditions/FAQs | Seven links in one column | P0 | local menu seed + shell.css columns. Reference destinations without approved pages link to matching contact enquiry anchor, documented below | WordPress footer menu; no new page framework |
| 10 | Footer social | Four outlined circular social marks, End x101–358 y770–820 | Hidden because URLs unset | P1 | footer.php: decorative noninteractive placeholders when URL missing; do not invent external account URLs | eqc-social-icon, sprite; register missing URLs |
| 11 | Copyright | Dark green rounded bar x54–1437 y878–955; logo medallion overlaps upper center; © 2026 exact footer line | Bare muted text, no bar/medallion | P1 | footer.php/shell.css: bar, 64px-ish medallion | approved mark, green/gold tokens |
| 12 | Mobile drawer | Reference silent; keyboard usable UI required | No focus containment, no close button within overlay, background remains focusable | P3 | header.php/eqc.js: close control, focus trap, inert background, breakpoint cleanup | existing drawer JS and close icon |

## Ordered fix plan

1. Complete unchanged-site screenshot and Lighthouse baselines; checkpoint DB.
2. Extend shared shell tokens, shared logo helper and existing icon generator. Keep approved logo silhouette.
3. Implement header/footer markup and CSS; reconcile only sanctioned local menu/contact staging defaults through WordPress APIs.
4. Fix drawer keyboard behavior. Reuse all existing child-theme component classes; no second framework.
5. Render at1920; crop header, CTA and footer beside native references. Iterate geometry and line breaks based on measurements.
6. Capture1920/1440/1024/768/390, test intermediate widths360–1920, keyboard/focus, SVG semantics, contrast and reduced motion; lint PHP and JS, rerun Lighthouse/trace.
7. Record remaining differences, update DESIGN/LESSONS/register/assets, review diff, stage only owned files and commit.

## Improvised

- Keep approved Logo2 mark even though the reference lockup uses a narrower illustrative book: task explicitly approves the existing traced logo. Recreate the surrounding two-line wordmark.
- Missing social account URLs show decorative marks with accessible context, not fake links. No production/external account is contacted in QA.
- Donation/support/campaign/legal destinations lack approved page content. Preserve exact footer labels and use local contact enquiry anchors pending client decisions, recording every destination in the register. Do not invent legal policies or donations/payment functionality.
- Mobile/tablet adapts by available width; no fixed heights on text panels. Drawer close/focus management and44px minimum touch controls protect keyboard/touch access.
- Gold small text on cream will use a darker existing token where4.5:1 requires it; gold on green and large display type checked separately.

## Iteration log

- Review: identified11 structural/anatomical discrepancies plus drawer keyboard deficiency; no presentation edits yet. Baseline Lighthouse desktop: accessibility100, best practices100, SEO54 (local indexing gate), agentic browsing100. Unthrottled local trace: LCP339ms, CLS0.05; compare on the same setup rather than treating these as production scores.

- Implementation: Claude Code `claude-opus-5` supplied the bounded shared-shell patch; Codex reviewed it and corrected the early1800px CTA stack, capsule height/radius, footer type/grid spacing, address wrapping, Twitter bird, background ornament and accessible logo name/contrast.
- Imagery: user explicitly rejected all existing reference photo crops. Replaced13 cropped photographs and added one CTA portrait. Preserved attachment IDs with native WordPress media APIs; a second sync reported all16 WebP assets unchanged (including the two approved logo assets). Removed13 obsolete cropped JPGs from staging. Source originals and licences/provenance retained.
- Measured1920: header1805×149 (reference normalized1810×151); CTA1805×536 (1809×542); action capsule1019×196 (~1008×197); footer panel1805×626 (~1809×628); copyright1782×99 (~1783×99); count circle118, medallion82 (~81). Small geometric differences reflect integer/font metrics and approved logo anatomy. Reference and build are shown side by side in `../after/global-chrome/footer-comparison.png` (reference left).
- Independent QA caught logo accessible-name mismatch and insufficient gold subline contrast, both fixed; desktop/mobile Lighthouse accessibility100, best practices100, SEO54, agentic browsing100. SEO's existing noindex/missing description/generic blog-link findings remain for page-specific review; noindex must remain locally. Post-change local unthrottled trace LCP286ms, CLS0.00; no performance regression observed.
- Final browser sweep:1920,1440,1024,768,390 plus40 widths360–1920 at40px increments. The scan caught1000px footer overflow; moved grid collapse to1024px and re-ran successfully. No page/console/network/asset failures. Drawer14-tab containment, Escape, inner close button, restored focus and desktop-resize cleanup passed. All decorative chrome SVGs hidden from accessibility tree; checked text contrast5.07:1 or higher. Reduced-motion captures settle without carousel/reveal animation. All10 changed PHP files linted; shared JavaScript syntax valid.
- Evidence: `../after/global-chrome/home/report.json`, desktop/mobile Lighthouse JSON, `drawer-check.txt`, `contrast-check.json`, five full-page captures and footer comparison. Full Home content remains pending its own review.

Final deliberate departures: approved client logo, replacement licensed/generated photography, one consistent Lucide outline UI family (including the reference's filled gift/contact glyphs), darker small gold text for AA, no invented social profiles, and responsive stacking/accessibility behavior where the source is silent. Missing footer destinations use local contact enquiry query strings, not anchors. These remain documented staging destinations.
