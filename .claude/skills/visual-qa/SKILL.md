---
description: Performs browser-based responsive and visual QA for Easy Quran Classes using the chrome-devtools MCP, checking layout, console issues, interactions, forms and DESIGN.md consistency. Use after frontend changes and before milestone approval.
---

# Visual QA

Use the chrome-devtools MCP (already configured for this project). Typical flow:

1. `new_page` to the target URL.
2. `resize_page` to each breakpoint below, `take_screenshot` at each.
3. `take_snapshot` / interact via `click`, `fill`, `hover` for forms and nav.
4. `list_console_messages` and `list_network_requests` for errors/failed assets.

Target staging unless the user explicitly asks to inspect production.

Read `DESIGN.md` before judging visual consistency.

Check at minimum:

- 1440x900
- 1024x768
- 768x1024
- 430x932
- 390x844
- 360x800

Validate:

- header/navigation
- H1/H2 hierarchy and wrapping
- spacing rhythm
- card alignment
- image crop/aspect ratio
- CTA visibility
- hover/focus behavior where practical
- mobile tap targets
- horizontal overflow
- accordions/carousels
- form labels/errors/submission path when safe test data is available
- console errors
- obvious failed network resources
- placeholder or fabricated-looking content accidentally exposed

Capture screenshots for failures and key milestone states when practical.

Report issues by severity: blocker, high, medium, polish.

Do not make design changes during an audit unless the task explicitly includes fixing them.
