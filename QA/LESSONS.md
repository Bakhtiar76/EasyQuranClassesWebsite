# Design parity lessons

1. Verify current state rather than assuming dated task notes still apply: Docker was already running.
2. Existing user changes rename the reference JPEGs and add `Website Layout.png`; preserve these and do not stage deletions incidentally.
3. CLI and web PHP can see different `WORDPRESS_CONFIG_EXTRA` values with the official Docker image. Confirm environment as well as URL before local writes.
4. Rebuild with `--user=1` and verify distinctive saved content. Regenerated inline dividers require rebuilding every page; CSS masks do not.
5. The actual composite is992×1586, and individual JPEGs use different native widths. Record native coordinates and normalized canvas fractions; do not assume any file itself is1920px wide.
6. A localhost-only screenshot guard must inspect redirects before following them. Checking the final URL is too late. The runner now fetches redirects with maxRedirects0 and checks every next URL before fetching it.
7. Baseline captures must include the original fonts. The legacy site fetched Google Fonts; baseline used a narrow `--allow-fonts` exception. Subsequent captures use self-hosted fonts with no exception. Reduced motion makes screenshot states deterministic; normal motion is tested separately.
8. Existing hero media already contains a gold frame; teachers are~220px and blog sources only163px tall. Do not mistake baked photo decoration for the live SVG silhouette, and do not claim these small crops are licensed original photography.
9. A passing viewport list can miss a narrow failure between breakpoints: the intermediate scan found footer overflow at1000px. Fix the content breakpoint and repeat the scan.
10. A manually supplied aria-label can disagree with concatenated visible text. Put real whitespace between wordmark lines and let its accessible name derive from the visible text. Test with Lighthouse as well as visual review.
