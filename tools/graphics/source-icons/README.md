# Icon artwork sources

Vector artwork the icon sprite is built from, for icons that are **not**
drawn in code. `build-icon-sprite.mjs` reads the files in this directory
(see its `SUPPLIED` map) and emits them into `inc/icon-sprite.php` and
`assets/svg/icons/`. Nothing here ships — only the generated output does.

| File | Sprite id | Design role |
|---|---|---|
| `graduation-cap-filled.svg` | `graduation-cap-filled` | Teachers feature "Qualified & Experienced" |
| `graduation-cap-outline.svg` | `graduate` | Testimonial tag "Expert Tutors" |
| `people-pair.svg` | `people-pair` | Pricing trust "Qualified Male & Female Tutors" |
| `rehal-quran.svg` | `rehal-quran` | Pricing trust "One-on-One Live Classes" |
| `stat-students.svg` | `presenter` | About stat "Students Taught" |
| `target-arrow.svg` | `target-arrow` | Testimonial tag "Personalised Focus" |

## What was changed from the supplied files

`supplied-original/` holds the artwork exactly as delivered: 1254x1254, two
tone, gradient fills. The files beside this README are the same geometry,
normalised so it can serve as a sprite symbol:

- **Gradients replaced with `currentColor`.** These six icons appear in the
  design as dark green on cream, gold on cream, and white on a coloured
  button. A symbol with baked-in colour cannot do that. Flattening was
  checked before committing to it — all six still read correctly in one
  colour, because the separation between cap and head, book and stand, is
  cut into the geometry rather than carried by the second colour.
- **viewBox tightened to the ink, squared, and given an 8% margin**, so they
  sit at the same optical weight as the 24-grid icons instead of at whatever
  scale the 1254px canvas left them.
- `<defs>`, `<title>` and `<desc>` dropped once the gradients they defined
  were gone.

The build additionally runs svgo at 2 decimal places on these, because the
artwork is traced and carries far more precision than a 24px icon can render
(it takes `rehal-quran` from 62KB to 28KB).
