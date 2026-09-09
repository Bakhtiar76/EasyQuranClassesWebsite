# Design-parity verification run

`node tests/visual/sweep.mjs http://localhost / /about/ /courses/ /teachers/
/pricing/ /contact/ /faq/ /free-trial/ /blog/ --out QA/after/parity --scan`

Nine routes, eight viewports each, plus a 380-1900px scan in 40px steps.

Result: **0 failures, 0 warnings** on every route — no horizontal overflow at
any scanned width, no console errors, no page errors, no failed asset
requests, exactly one H1 per page and no image missing alt text. The
per-route `report.json` files are the record.

The full-page PNGs from this run are **not tracked**: they are 55MB, and the
repository already carries ~110MB of earlier QA captures. They are written
next to these reports by the command above and can be regenerated at any
time. `.gitignore` excludes only this directory's PNGs, not the earlier
committed evidence sets.
