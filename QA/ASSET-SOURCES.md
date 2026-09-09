# Asset provenance and licences

Local design staging only. Unknown provenance is recorded honestly and is not a release approval.

| File / family | Source | Author / owner | Licence | Attribution requirement | Use |
|---|---|---|---|---|---|
| assets/fonts/dm-serif-display-latin-400.woff2 | https://fonts.gstatic.com/s/dmserifdisplay/v17/-nFnOHM81r4j6k0gjAW3mujVU2B2G_Bx0g.woff2 | Adobe / Google; DM Serif designers | SIL OFL1.1, verified https://raw.githubusercontent.com/google/fonts/main/ofl/dmserifdisplay/OFL.txt | Copyright/licence bundled as DM-Serif-Display-OFL.txt | All display typography, unmodified Latin subset |
| assets/fonts/manrope-latin-variable.woff2 | https://fonts.gstatic.com/s/manrope/v20/xn7gYHE41ni1AdIRggexSg.woff2 | Manrope Project Authors, Mikhail Sharanda and contributors | SIL OFL1.1, verified https://raw.githubusercontent.com/google/fonts/main/ofl/manrope/OFL.txt | Copyright/licence bundled as Manrope-OFL.txt | Body/UI400–700, unmodified Latin variable subset |
| inc/icon-sprite.php, outline symbols | Installed lucide-static through tools/graphics/build-icon-sprite.mjs; https://lucide.dev/license | Lucide contributors | ISC (existing project dependency) | Retain licence; generated library source attribution here | Shared outline icons |
| inc/icon-sprite.php, brand symbols | Installed simple-icons through tools/graphics/build-icon-sprite.mjs; https://github.com/simple-icons/simple-icons | Simple Icons contributors | CC0 (existing project dependency; brand rights separate) | No copyright attribution required | Social/WhatsApp marks supplied by existing pipeline |
| assets/svg/logo/* | Assests/Logo/Logo2.jpeg; existing trace/build-logo pipeline | Client supplied approved artwork | Client-provided local design reference; ownership/release rights require client confirmation | Client brand | Shared logo and medallion |
| about-quran-open-page.webp | [Quran Book on Windowsill](https://www.pexels.com/photo/quran-book-on-windowsill-15694717/) | Firdevs | Pexels licence | Not required | About collage, 1200×1800 |
| about-child-reading-quran.webp | [Boy Reading Koran](https://www.pexels.com/photo/boy-reading-koran-on-a-carpet-9127846/) | Timur Weber | Pexels licence | Not required | General education illustration, 1200×1800; no endorsement claim |
| blog-islamic-education-1.webp | [Sunlit Arches](https://www.pexels.com/photo/serene-mosque-interior-with-sunlit-arches-35160090/) | Muhammed Fatih Beki | Pexels licence | Not required | Blog illustration, 1200×1800 |
| blog-islamic-education-2.webp | [Columns and Red Carpet](https://www.pexels.com/photo/columns-and-red-carpet-in-mosque-20719987/) | Esra Korkmaz | Pexels licence | Not required | Blog illustration, 1800×1200 |
| blog-islamic-education-3.webp | [Sunlit Mosque](https://www.pexels.com/photo/sunlit-mosque-interior-with-arched-windows-29202525/) | HATİCENUR TAŞDEMİR | Pexels licence | Not required | Blog illustration, 1013×1800 |
| hero-online-quran-class.webp | Built-in image generation, 2026-09-09 | Generated for this project | Generated output; no third-party stock source | Generation provenance recorded | Fictional lesson scene, 1122×1402 |
| teacher-*.webp (four) | Built-in image generation, 2026-09-09 | Generated for this project | Generated output | Generation provenance recorded | Fictional staff placeholders, 800×800; not actual named teachers |
| testimonial-*.webp (three); student-avatar-boy.webp | Built-in image generation, 2026-09-09; exact prompts in GENERATED-IMAGE-PROMPTS.md | Generated for this project | Generated output | Generation provenance recorded | Fictional testimonial/CTA placeholders, 800×800 |

All photographs were inspected before use. [Pexels licence](https://www.pexels.com/license/) verified 2026-09-09: free commercial use and modification, attribution optional; identifiable people must not be presented as endorsing the business. Pexels photos are licensed copyrighted works, not claimed to be public domain.

Full-resolution source files are retained as `local/media-staging/*-source.jpg` or `*-source.png`; served files are adjacent WebP exports. The original generated PNGs also remain in the Codex generated-images directory, preserving their provenance metadata. Native WordPress image conversion retains the full composition, without reference frames or pixel upscaling. Responsive presentation uses CSS masks and WordPress image sizes.

Reproduce optimized assets: `wp --user=1 eval-file /tools/06-media.php prepare` in the local Docker runner, inspect `/backups/design-media`, then copy the exports to `local/media-staging`. `wp --user=1 eval-file /tools/06-media.php` imports missing files or updates changed files in place and regenerates attachment sizes. Bootstrap invokes that same synchronization step. Source images are never downloaded at runtime.

Twitter bird source: https://raw.githubusercontent.com/simple-icons/simple-icons/9.21.0/icons/twitter.svg (Simple Icons9.21.0, CC0), vendored as tools/graphics/twitter-reference.svg to reproduce the supplied reference rather than substituting the X glyph.
