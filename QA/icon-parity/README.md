# Icon parity collage

Every reference crop beside every shipped icon, grouped into semantic families
so each shipped glyph sits next to the drawing(s) it is meant to reproduce.

- **Reference:** `tools/graphics/reference/icons/*.png` — 98 crops of the
  client's design screenshots (`icons.json` gives each one's role/where).
- **Shipped:** `wp-content/themes/easy-quran-classes-child/assets/svg/icons/*.svg`
  — 57 standalone icons emitted by `tools/graphics/build-icon-sprite.mjs`.

## Files

- `collage.html` — the source page (open in a browser for the live version).
- `collage-full.png` — the whole collage, one tall image.
- `collage-01.png` … `collage-14.png` — one PNG per family, for legibility.

Regenerate after SVG changes: `node tools/graphics/render-icon-parity.mjs`

## How to read a tile

Each reference tile carries a verdict tag for its relationship to the shipped set:

| tag | meaning |
|---|---|
| match | shipped glyph reproduces the crop directly |
| weight variant | same glyph, different fill/weight (outline vs solid) |
| mirrored in CSS | shipped glyph is this one rotated/mirrored (`chevron-left` = `chevron-right` 180°) |
| glyph only – frame is CSS | crop is glyph + disc/ring/frame; only the glyph is an icon |
| ships as ornament SVG | motif ships from `assets/svg/` as a divider/rosette, not in the icon set |
| no icon equivalent | nothing in the icon set corresponds (logo marks, CSS pager dots) |

Shipped tiles show how many crops map to them, or `no crop` when the design
never draws that icon (`menu`, `close`, `plus`, `check`, `chevron-down`,
`monitor-play`, plain `shield` / `sparkle` / `star` outlines).

## Families

1. Arrows · 2. Chevrons · 3. People · 4. Graduation · 5. Shields ·
6. Books & Qur'an · 7. Calendar & clock · 8. Quotes ·
9. Stars, sparkles & rosettes · 10. Phone, mail, map & support · 11. Other UI ·
12. Brand & logo · 13. Theme icons with no reference crop ·
14. Ornaments (ship as `assets/svg/` SVGs, not part of the icon set).

## Audit result (2026-09-09)

- All 98 reference crops were reviewed against the 57 generated icon symbols
  and the standalone ornament family. The icon sprite was regenerated from
  `build-icon-sprite.mjs`; the existing glyph verdicts remain valid.
- The AI reference's excessive flower variation is intentionally normalized
  into one professional family: `rosette-reviews.svg` is the signature
  layered rosette and `rosette-simple.svg` is its reduced outline. Major and
  compact placements share those two levels consistently.
- The pricing medallion's outer shell is generated from the same cusped
  contour as `cartouche-alphabet-mask.svg`, scaled to the medallion's measured
  aspect ratio. Its dark inner scallop remains a separate mask.
- Pricing list bullets, benefit discs, eyebrow rosette/cartouche and the
  divider above each price are now present and matched to `pricing.jpeg`.
- Section placement was checked too: teacher corner motifs were removed and
  the missing blog corner motifs were added.
- `divider-*.svg` files render as full horizontal rules, so their centre motif
  is necessarily small in the collage. `collage-14.png` is the authoritative
  side-by-side sheet; final in-page evidence is under
  `QA/graphics-parity-final/`.
- `facebook.svg` (f-in-circle) has **no crop** — the design uses the bare `f`,
  which is `facebook-f.svg`. Correct, but the unused file is still shipped.
- `rehal-quran` and `book-open` (outline) read thinner/lighter than the
  client's gold, filled drawings — flagged `weight variant`.
- Every framed pricing-trust glyph (`trust-*-framed`) and every disc/ring CTA
  is `glyph only` — the frame/disc is CSS or an ornament, not the icon.

See `QA/GRAPHICS-PARITY.md` for the complete reference-to-consumer matrix and
the rebuild rules that prevent stale Elementor-embedded SVG markup.
