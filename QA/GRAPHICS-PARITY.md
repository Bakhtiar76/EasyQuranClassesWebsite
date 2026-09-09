# Graphics parity record

Last verified: **2026-09-09** against the client-owned screenshots in
`Assests/` and the 98 native-resolution crops in
`tools/graphics/reference/icons/`.

This is the durable handoff for both Claude and Codex. It records the actual
reference-to-asset-to-page mapping so a later pass does not replace distinct
ornaments with one generic flower or confuse a CSS frame with an icon glyph.

## Audit scope and result

- Compared all 98 reference crops with the 57 generated standalone icon SVGs,
  the shared sprite in `inc/icon-sprite.php`, and the standalone ornament
  assets in `assets/svg/`.
- Regenerated the icon set from `tools/graphics/build-icon-sprite.mjs`.
  Existing icon-family verdicts remain correct; no new glyph mismatch was
  found in the 57-symbol sprite.
- Corrected the remaining ornament mismatch family, then normalized the AI
  reference's excessive flower variation into one signature rosette and one
  simplified outline derived from it. Curved sparkles, course number outlines,
  pricing bullets/benefit frames, pricing medallion shell and price dividers
  remain purpose-specific non-rosette shapes.
- Corrected page placement: removed corner ornaments from Teachers (not in the
  reference) and added them to Blog (present in the reference).
- Rebuilt every Elementor page with editor context (`--user=1`) after changing
  inline SVG assets, then flushed Elementor CSS.
- Passed local visual sweeps at 1440, 768 and 390 pixels. Direct 1600-pixel
  section captures were also inspected against the 1599–1600-pixel client
  section JPEGs.

## Authoritative mapping

| Reference feature | Generated/shipped asset | Live consumer |
|---|---|---|
| About upper simple flower | `rosette-simple.svg` | `10-home.php` `.eqc-about-rail` |
| About lower curved diamond | `divider-about.svg` | `10-home.php` `.eqc-about-rule` |
| About faint lattice field | `girih-lattice-fine.svg` | `components.css` `.eqc-section--about::before` at 0.10 opacity |
| About faint arch field | `keel-arch-outline.svg` | `components.css` `.eqc-section--about::after` |
| Courses heading flower + end diamonds | `divider-section.svg` | default `eqc_section_heading_el()` divider |
| Course number badge | `seal-outline.svg` | `.eqc-card--course .eqc-card-index::before` mask; no fill |
| Course-card mini flower rule | `divider-card.svg` | `eqc_course_card()` |
| Courses closing flower | `rosette-reviews.svg` (signature) | `10-home.php` `.eqc-courses-closing` |
| Courses/blog corner girih | `lattice-corner.svg` | `eqc_section_ornaments()` + `.eqc-section--ornamented` |
| Teacher heading gear + rule | `divider-teacher.svg` | `10-home.php` `.eqc-teachers-rule` |
| Pricing eyebrow cartouche | `pricing-eyebrow-frame.svg` | `.eqc-eyebrow--rosette::after` mask |
| Pricing eyebrow flower | `rosette-simple.svg` | `.eqc-eyebrow--rosette::before` mask |
| Pricing heading sparkle | `divider-diamond.svg` | pricing `eqc_section_heading_el(..., 'diamond')` |
| Pricing medallion outer shell | `pricing-medallion-shell-mask.svg` + `pricing-medallion-frame.svg` | `.eqc-pricing-icon::before` + inline ring |
| Pricing medallion dark scallop | `pricing-medallion-mask.svg` | `.eqc-pricing-icon::after` |
| Pricing medallion calendar | icon sprite `calendar` | `eqc_pricing_card()` |
| Pricing list rosette | `pricing-bullet.svg` | `eqc_pricing_card()` list items |
| Pricing banner sparkle rule | `divider-accent.svg` | `.eqc-pricing-divider` |
| Divider above price | `divider-price.svg` | `.eqc-pricing-price-divider` |
| Pricing benefit scallops | `seal-filled.svg` as two nested masks | `.eqc-benefit__disc::{before,after}` |
| Pricing section corners | client-supplied `corner-ornament.svg` | `.eqc-section--pricing::{before,after}` at 0.38 opacity |
| Testimonial heading flower | `divider-reviews.svg` embedding the signature geometry | testimonials heading divider |
| Testimonial card dot rule | `divider-dot.svg` | `eqc_testimonial_card()` |
| Testimonial card corner | `corner-motif.svg` | `.eqc-card--testimonial::before` |
| Footer bottom seal | `logo/eqc-logo-mark.svg` inside a CSS circle | `footer.php` `.eqc-footer-medallion` |
| Footer CTA texture | `girih-lattice-fine.svg` | footer CTA CSS background mask |

The pricing medallion shell and eyebrow frame intentionally reuse
`closedCartouche()` in `gen-ornaments.mjs`, the same source geometry that emits
`cartouche-alphabet-mask.svg`. This implements the client's direction that the
alphabet cartouche outer line should become the recurring pricing outline.

`rosette-reviews.svg` is the canonical layered signature and
`rosette-simple.svg` is its only compact outline variant. `divider-section`,
`divider-reviews` and `divider-card` embed those same two geometries at their
required scales. `corner-frame.svg`, `rosette-12.svg`, `star-8-filled.svg` and
the traced generic `rosette.svg` remain library assets, not additional live
flower styles.

## Comparison evidence

- `QA/icon-parity/collage.html` — live source sheet.
- `QA/icon-parity/collage-full.png` — all icon families.
- `QA/icon-parity/collage-14.png` — ornament reference/output pairs.
- `QA/graphics-parity-final/home/desktop-1440x900.png`
- `QA/graphics-parity-final/home/tablet-768x1024.png`
- `QA/graphics-parity-final/home/mobile-390x844.png`
- `tools/graphics/scratch/shot-*-1600.png` — local ignored working captures
  for direct section comparison; regenerate rather than commit them.

## Required regeneration sequence

From the repository root:

```powershell
node tools/graphics/gen-ornaments.mjs
node tools/graphics/build-icon-sprite.mjs
node tools/graphics/render-icon-parity.mjs
pwsh -NoProfile -File local/iterate.ps1 -All -Route / -Out QA/graphics-parity-final -Widths 1440,768,390
```

Docker/WP-CLI must run outside the Codex sandbox. `local/iterate.ps1 -All`
verifies `http://localhost|local`, runs every sanctioned page builder with
`--user=1`, flushes Elementor CSS, and then captures the supported viewports.

Do not skip the all-page rebuild after modifying any SVG embedded by
`eqc_get_svg_asset()` or `eqc_divider_svg()`: Elementor stores the SVG markup
as a literal snapshot in `_elementor_data`.

## Screenshot reliability rule

A raw element screenshot can be blank when reveal animation state or lazy
media has not been activated. Use `tools/graphics/scratch/section-shot.mjs` for
section comparisons. It enables reduced motion, scrolls the whole page to
activate reveal/lazy content, waits for images, hides only the sticky header,
then captures the element. Never accept a blank capture as site evidence.

## Future-change guardrails

1. Compare at the native reference scale before changing geometry.
2. Keep glyphs in `build-icon-sprite.mjs`; keep frames, seals, textures and
   dividers in `gen-ornaments.mjs`/CSS.
3. Prefer the mapped semantic asset over a generic rosette.
4. Use masks when an external SVG must take on a CSS color; `currentColor`
   does not inherit through a CSS `background-image` URL.
5. Regenerate, rebuild all pages, flush Elementor CSS, and capture all three
   responsive widths before calling an ornament change complete.
