# Image replacement brief and acceptance

User instruction: replace all reference-cropped photography with high-quality licensed photographs or realistic generated imagery. All 13 former reference photo crops have been replaced; one new child portrait completes the four-person CTA lineup. Approved client logo artwork is separate from photography.

| Slots | Accepted direction | Source / served dimensions | Treatment |
|---|---|---|---|
| Hero | Documentary home study: boy in cream clothing attending a laptop Quran lesson; believable anatomy, warm daylight | Generated 1122×1402 / same | Complete rectangle, no baked arch or gold frame; crop and ornament implemented in CSS |
| Four teacher portraits | Two adult men (black cap/dark kurta; wavy hair/beige kurta), two adult women (plum and black niqab); consistent cream background and natural studio light | Generated 1254×1254 / 800×800 | Circular crop with headroom; fictional staging likenesses |
| Three testimonial portraits | Two fathers, one mother in taupe hijab; individual facial details, natural skin and fabric | Generated 1254×1254 / 800×800 | Circle; exact prompts in GENERATED-IMAGE-PROMPTS.md |
| CTA fourth portrait | Boy in cream cap and ivory kurta | Generated 1254×1254 / 800×800 | Circle, alongside three adult portraits |
| About collage | Actual Quran on wooden stand by window; child reading Quran | Licensed sources 2000×3000 and1600×2400 /1200×1800 | Live masks; preserve focal points |
| Three blog illustrations | Actual mosque interiors with architectural arches and natural light | Licensed sources1800px wide / long edge1800px | Landscape card crops chosen in CSS; originals retained |

All sources visually inspected at native or tool-scaled resolution: faces, hands, clothing, books and architectural details checked. Generated work is documented as generated; it is not described as real customer/staff photography. Stock source/author/licence links are in ASSET-SOURCES.md. No new site asset is extracted from a design reference.

Remaining acceptance work: verify each photo at its final page size and crop during that page's parity review. Replace a source if it cannot cover the final display without unacceptable softness or losing its subject. The hero currently supports roughly561 CSS pixels at2×; do not claim it supplies2× detail for a larger box. Generated staff/review imagery and associated identity claims remain subject to the placeholder register.

## Home / About collage — Arabic alphabet chart (added 2026-09-09, `claude-opus-5`)

| Field | Value |
|---|---|
| Page / section | Home, About collage (third element, upper-left) |
| Reference | `Assests/Home2.jpeg` — a quatrefoil-masked panel overlapping the large Quran-page arch |
| Mask | Quatrefoil (`quatrefoil-mask.svg`), gold hairline ring, cream gap |
| Aspect after masking | 1:1 |
| Minimum pixels | 900 × 900 (renders ~300u wide, ×2 for DPR, plus mask bleed) |
| Subject | A traditional Arabic alphabet / Noorani Qaida letter chart — a grid of letters, several coloured (red and green accents on a white ground), as used in beginner Quran teaching |
| Prompt | "A clean traditional Arabic alphabet teaching chart, grid of Arabic letters on white, a few letters accented in red and green, flat straight-on view, soft even lighting, no perspective distortion, no watermark, no people" |
| Why not cropped from the reference | `Home2.jpeg` is a ~1600px composite; the chart occupies ~180px of it, so a crop would be a low-resolution artefact — worse than the two real photographs beside it |
| Status | **Not shipped.** The About collage currently renders two of the reference's three elements. |
