---
description: Converts an approved page brief and DESIGN.md into a clean Elementor Free implementation plan and supports the custom CSS/PHP/JS needed around Elementor without brittle database manipulation. Use when building or revising an Elementor page or reusable section.
---

# Elementor Build Workflow

Read `DESIGN.md` first.

## Inspect

Before implementation, identify:

- page purpose and primary CTA
- existing Elementor containers/widgets/classes
- global colors/typography already configured
- child-theme CSS already present
- responsive issues in the current page
- factual content still awaiting client confirmation

## Build Strategy

Prefer in this order:

1. Elementor Free containers/widgets
2. Elementor global styles
3. reusable semantic CSS classes in the child theme
4. small JS enhancement only if interaction genuinely requires it
5. small PHP/theme change only if the template cannot be solved cleanly in Elementor Free

Do not install an addon pack just to solve a small visual issue.

Do not directly write `_elementor_data` or serialized Elementor DB values during normal work.

## Output

For each section provide:

- container structure
- desktop/tablet/mobile behavior
- widgets required
- global tokens/classes used
- content state: verified vs placeholder
- any custom-code requirement

After implementation, run visual QA or provide an exact manual Elementor verification checklist.
