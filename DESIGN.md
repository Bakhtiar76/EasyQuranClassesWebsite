# Easy Quran Classes — Design System & UI Specification

## 1. Purpose

This file is the visual source of truth for the Easy Quran Classes website.

The client screenshots are the primary design reference at Assests Folder.
For inspiration: Riwaq Al Quran (https://riwaqalquran.com/) may inform UX, information architecture, trust-building and conversion flow, but must not be copied visually or structurally one-to-one.

The finished website should feel:

- calm, trustworthy and premium
- recognizably Islamic without becoming ornamental or visually heavy
- welcoming to parents, adults and beginners
- modern enough for an online education service
- conversion-focused without looking aggressive or sales-heavy
- intentionally designed on mobile, not merely stacked


## Local Implementation Environment

All design and Elementor implementation is developed and verified on the approved **local WordPress installation**. The production cPanel site is not used as the design workspace because hosting has no shell access and production changes are release-gated.

For design work:
- Elementor changes happen locally;
- Playwright/browser QA targets localhost;
- screenshots should be captured from the local site during implementation;
- production-specific URL/content facts must remain configurable and must not be hardcoded into reusable theme code;
- final visual QA must be repeated after cPanel deployment because hosting fonts/cache/image delivery can differ from local.

## 2. Design Priority

When visual references conflict, use this order:

1. Client-provided Easy Quran Classes screenshots
2. Easy Quran Classes logo and brand identity
3. This DESIGN.md system
4. Riwaq Al Quran as UX/content inspiration only
5. General best practice

Never recreate Riwaq Al Quran or another site pixel-for-pixel.

## 3. Brand Personality

Use the following adjectives as design filters:

- Reverent
- Clear
- Warm
- Personal
- Established
- Calm
- Accessible
- Premium
- Family-friendly

Avoid visual language that feels:

- overly corporate
- childish
- flashy
- futuristic
- gaming-like
- dark/luxury for its own sake
- excessively decorative
- template-like

## 4. Content Integrity

Design must not make unverified claims look factual.

Do not publish fabricated:

- student counts
- review counts
- testimonials
- teacher names
- teacher qualifications
- certifications
- years of experience
- ratings
- prices
- guarantees
- accreditation
- country counts
- business statistics

When the screenshots contain such content but the client has not confirmed it, treat it as layout-only reference and use a clearly marked placeholder in staging.

## 5. Core Color System

**Confirmed brand mark (2026-09-07):** the client's logo is the headphones-and-open-book mark at `Assests/Logo/Logo2.jpeg` — **forest green**. (Three other colorways — purple, teal, navy — exist in `Assests/Logo/` as unused drafts; forest green is the approved one.) It matches the tokens below, sampled from the client's own reference screenshots.

These are the working tokens, validated against both the confirmed logo and the client screenshots.

```css
:root {
  --eqc-green-900: #143126;
  --eqc-green-800: #1B3A2D;
  --eqc-green-700: #24503B;
  --eqc-brand-green: #006B3D;

  --eqc-gold-600: #A98235;
  --eqc-gold-500: #B9974C;
  --eqc-gold-300: #D9C48D;

  --eqc-bronze-700: #7A432A;
  --eqc-bronze-600: #925234;

  --eqc-cream-100: #F7F3EC;
  --eqc-cream-50: #FBF8F2;
  --eqc-surface: #FFFDF9;
  --eqc-white: #FFFFFF;

  --eqc-heading: #14231B;
  --eqc-text: #40483F;
  --eqc-muted: #737A72;
  --eqc-border: #E5DCCB;
  --eqc-border-strong: #D4C5A7;

  --eqc-success: #2F6B4A;
  --eqc-error: #9F3B32;
}
```

### Color Roles

- Deep forest green: primary UI, footer, dark CTA sections, icons, buttons
- Brand green: logo-led accents only when visually necessary
- Gold: ornaments, small dividers, active states, badges and subtle borders
- Bronze: primary conversion CTA such as Free Trial when stronger contrast is needed
- Cream: default page background
- Surface: cards and elevated content
- White: use sparingly inside dark sections or form controls

### Color Rules

- Do not use green, gold and bronze as three equal CTA colors.
- Default interactive button = deep green.
- Highest-priority conversion CTA = bronze or gold-on-green, depending on context.
- Gold should usually accent, not carry long body text.
- Avoid low-contrast gold text on cream backgrounds.

## 6. Typography

### Primary Fonts

- Display / headings: `DM Serif Display`
- Body / UI: `Manrope`
- Arabic where required: `Noto Naskh Arabic`

Use system fallbacks after these fonts.

### Font Loading

Prefer local hosting if practical. Load only the weights actually used.

Recommended weights:

- DM Serif Display: 400
- Manrope: 400, 500, 600, 700
- Noto Naskh Arabic: 400, 600 only if Arabic content requires it

### Type Scale — Desktop

| Token | Size | Weight | Line Height | Usage |
|---|---:|---:|---:|---|
| Display XL | clamp(52px, 5vw, 76px) | 400 | 1.02 | rare hero/display use |
| H1 | clamp(46px, 4.2vw, 66px) | 400 | 1.06 | page hero |
| H2 | clamp(38px, 3.4vw, 54px) | 400 | 1.10 | major section title |
| H3 | 28–34px | 400/600 | 1.18 | cards/subsections |
| H4 | 21–24px | 600 | 1.25 | card headings |
| Body L | 18px | 400 | 1.75 | hero/about intro |
| Body | 16px | 400 | 1.7 | standard copy |
| Small | 14px | 500 | 1.55 | metadata/labels |
| Eyebrow | 14–15px | 700 | 1.3 | uppercase section label |
| Button | 15–16px | 600 | 1 | buttons |

### Mobile Type Scale

- H1: 38–46px
- H2: 32–38px
- H3: 25–29px
- Body: 16px
- Small: 13–14px

Do not reduce body copy below 16px on mobile.

## 7. Layout System

### Global Widths

```css
--eqc-content-max: 1240px;
--eqc-content-narrow: 820px;
--eqc-text-max: 680px;
```

### Section Spacing

- Desktop: 88–104px vertical
- Laptop: 72–88px
- Tablet: 64–72px
- Mobile: 44–56px

### Horizontal Gutters

- Desktop: 32px minimum
- Tablet: 28px
- Mobile: 20px
- Small mobile: 16px only where required

### Common Gaps

- Grid gap desktop: 24–30px
- Grid gap tablet: 20–24px
- Grid gap mobile: 16–20px
- Text stack gap: 12–20px

### Corner Radius

- Small control: 10–12px
- Card: 16–20px
- Large panel: 24–32px
- Hero imagery / feature panel: 28–40px where appropriate
- Pills: 999px

## 8. Elevation & Borders

The design should feel soft, not glossy.

### Standard Card

```css
border: 1px solid var(--eqc-border);
box-shadow: 0 10px 28px rgba(32, 46, 37, 0.06);
```

### Hover Elevation

Increase shadow only slightly and move at most 2–3px vertically.

Avoid:

- strong drop shadows
- neumorphism
- glassmorphism
- glowing outlines

## 9. Islamic Ornament System

Use geometric Islamic patterns as supporting texture, never as the main visual event.

Approved uses:

- low-opacity corner motifs
- section dividers
- thin gold line ornaments
- arch frames for selected imagery
- rosette/8-point motifs as small accents
- subtle background watermark pattern

Rules:

- opacity normally 3–10%
- avoid placing patterns under long text
- do not use multiple competing patterns in one section
- patterns should be SVG where possible
- decorative SVGs should be hidden from assistive technologies

## 10. Photography Direction

Use authentic-looking Quran learning imagery:

- 1-to-1 online tutoring
- parent/child learning context
- Quran reading
- tasteful home learning environments
- male and female teachers where factually applicable

Avoid:

- obviously AI-looking hands/faces
- exaggerated mosque stock imagery used merely as decoration
- images with visible unrelated branding
- generic corporate meeting imagery

### Image Treatment

Preferred treatments:

- Islamic arch mask for primary hero/feature imagery
- soft 16–28px radius for cards
- warm neutral color grading
- no heavy filters
- maintain natural skin tones

## 11. Iconography

Use one consistent outline or restrained filled icon family.

Preferred visual characteristics:

- rounded geometry
- 1.5–2px line weight
- dark green foreground
- gold as accent only

Do not mix several icon libraries on one page.

## 12. Buttons

### Primary

- deep green or bronze background
- white text
- 52–56px desktop height
- 48–52px mobile height
- 18–24px horizontal padding
- pill or 14–18px radius depending on context

### Secondary

- transparent/cream surface
- 1px dark green or muted gold border
- dark green text

### Icon Button

Circular arrow treatment may be used to reflect client references.

### Button Behavior

- hover: subtle tone shift + at most 1–2px movement
- focus-visible: clear 2–3px outline
- disabled state must be visually distinct
- minimum mobile tap area: 44x44px

## 13. Forms

Forms should feel simple and private.

### Field Style

- 48–54px minimum height
- 12–14px radius
- warm-white background
- 1px neutral border
- visible labels above fields
- placeholders must not replace labels

### Trial Form Suggested Fields

Only use fields approved by the client. Likely candidates:

- Student name
- Student age range
- Current level
- Preferred course
- Preferred days/time
- Country/time zone
- Parent/guardian name when relevant
- Email
- WhatsApp/phone
- Notes

Do not collect more personal information than the business genuinely needs.

## 14. Header

Desktop header should follow the client reference direction:

- white/surface rounded container
- logo left
- primary navigation centered/right
- high-priority Free Trial CTA right
- restrained active-link indicator
- comfortable vertical padding
- optional sticky behavior only if it does not create mobile clutter

Recommended desktop nav:

- Home
- About
- Courses
- Teachers
- Pricing
- Contact
- Free Trial

FAQ and Blog may live in a secondary route, footer, or expanded navigation depending on final width.

### Mobile Header

- logo left
- menu button right
- compact Free Trial CTA may appear inside drawer
- no horizontally squeezed desktop navigation
- drawer must be keyboard accessible

## 15. Footer

Footer should combine the client reference's strong dark-green identity with a clean information hierarchy.

Suggested structure:

1. Free Trial CTA panel
2. Main footer content
   - logo/about
   - contact details
   - quick links
   - course links
   - legal links
3. copyright strip

Do not display invented address, phone or email information.

## 16. Core Components

### Course Card

- numbered decorative badge
- course name
- level label
- concise 2–3 line description
- gold divider ornament
- circular arrow CTA
- optional subtle corner motif

Desktop: 3 columns
Tablet: 2 columns
Mobile: 1 column

### Teacher Card

Only publish verified teacher data.

- portrait
- name
- role
- 2–3 verified qualifications/specialties
- View Profile CTA if profile exists

Desktop carousel/grid should show 3–4 depending on width.
Mobile should show one strong card at a time or a vertical list.

### Pricing Card

- plan frequency
- class duration
- classes/month
- verified features
- price
- CTA

Recommended plan may be visually emphasized only if the client explicitly wants it.

### Testimonial Card

- verified reviewer name or approved anonymized identity
- location only if supplied/approved
- testimonial copy
- no fabricated portrait
- no rating unless supplied

### Blog Card

- featured image
- category
- publish date
- title
- short excerpt
- Read More

## 17. Homepage Section Blueprint

### 17.1 Hero

Goal: communicate the offer immediately and convert to a free trial.

Recommended content order:

- trust eyebrow
- H1: online Quran learning + personal guidance
- 1–2 sentence value proposition
- 3–4 factual benefit chips
- primary Free Trial CTA
- secondary WhatsApp CTA if approved
- trust proof only when verified
- arch-framed hero image

### 17.2 Trust Strip

Potential items:

- 1-to-1 live classes
- male & female teachers, only if true
- flexible schedule
- progress support/tracking, only if true

### 17.3 About / Accessibility

Use the screenshot direction with Quran imagery and layered Islamic frames.

Core message:

Learning the Quran should not depend on geography, schedule, age or previous confidence.

Avoid unverifiable statistics.

### 17.4 Courses

Initial course set from client reference:

- Noorani Qaida
- Quran Reading with Tajweed
- Tajweed Course
- Quran Tafseer
- Quran Memorization
- Islamic Studies

Treat exact levels/descriptions as content requiring client approval.

### 17.5 How It Works

Recommended four steps:

1. Request a free trial
2. Share level and availability
3. Get matched with a suitable teacher
4. Begin classes and review progress

### 17.6 Teachers

Use verified teacher profiles only. If not available, keep internal placeholders on staging and do not publish fabricated profiles.

### 17.7 Pricing

Use only confirmed plans and pricing.

### 17.8 Testimonials

Publish only genuine testimonials supplied or approved by the client.

### 17.9 FAQ

Answer enrollment objections such as:

- Who are the classes for?
- Can beginners start from zero?
- How does the free trial work?
- Are timings flexible?
- How are teachers selected?
- What device is needed?

Claims must match actual business operations.

### 17.10 Blog

Show latest three WordPress posts using the native Posts system.

### 17.11 Final CTA

Dark forest panel with clear free-trial invitation and minimal copy.

## 18. Other Page Direction

### About

- mission
- who the academy serves
- teaching approach
- learning experience
- trust/safety statements only if factual
- CTA

### Courses Archive

- intro
- filter/category only if genuinely useful
- course cards
- comparison/help-choosing section
- CTA

### Course Detail

If approved as separate SEO pages:

- course promise
- who it is for
- learning outcomes
- curriculum/coverage
- teaching approach
- class format
- pricing link
- FAQ
- free trial CTA

### Teachers

- intro
- verified teacher grid
- matching process
- CTA

### Pricing

- plan cards
- what is included
- FAQ
- free trial CTA

### Contact

- simple form
- verified email/phone/WhatsApp
- response expectation only if factual
- no map unless the business has a public location

### FAQ

Use accordion UI with server-rendered accessible markup.

### Blog

- archive grid
- categories if useful
- search optional
- single-post typography optimized for reading

### Free Trial

- concise form
- privacy reassurance
- steps after submission
- fallback contact option

### Online Quran Classes for Kids

Recommended additional SEO/conversion page.

- parent-focused value proposition
- beginner pathways
- teacher matching
- age-appropriate learning approach
- safety/support claims only when confirmed
- suitable courses
- FAQ
- trial CTA

## 19. Elementor Implementation Rules

Use Elementor Free containers/flexbox.

Prefer:

- Site Settings for global colors/typography where available
- reusable container patterns
- predictable spacing tokens
- native widgets before custom code
- custom CSS from the child theme when a reusable design behavior is not practical in Elementor Free

Avoid:

- per-widget random margins
- dozens of unique font sizes
- negative margins as primary layout strategy
- spacer widgets for structural spacing
- multiple Elementor addon packs
- direct editing of Elementor database JSON

### Elementor CSS Classes

Use semantic reusable classes where custom CSS is necessary, for example:

```text
.eqc-section
.eqc-section--cream
.eqc-section--dark
.eqc-card
.eqc-card--course
.eqc-card--teacher
.eqc-btn
.eqc-btn--primary
.eqc-btn--secondary
.eqc-ornament
.eqc-arch-media
```

Do not use classes tied to one temporary page position such as `.section-3-left-card`.

## 19A. Responsive & Dynamic WordPress Architecture

The design system must be implemented as a **fluid WordPress website**, not as a fixed desktop mockup with separate mobile patches.

### Approved presentation stack

Prefer one cohesive stack:

```text
WordPress
+ Hello Elementor
+ Easy Quran Classes child theme where needed
+ Elementor Free Containers/Flexbox
+ native WordPress Posts/menus/media/templates
+ project-owned responsive CSS / minimal vanilla JS / SVG
```

Do not mix multiple parent themes, page builders, large Elementor addon suites or frontend frameworks merely to obtain responsive widgets.

The theme provides a lightweight site shell. Elementor provides page composition. The child theme provides shared presentation code that belongs to the site rather than to one page. WordPress owns dynamic content such as menus, Posts, archives and media derivatives.

### Fluid layout principles

Breakpoints are escape hatches, not the primary layout system.

Use:
- flexible containers;
- CSS Grid/Flexbox;
- wrapping;
- `minmax()` and `auto-fit` where useful;
- `clamp()` for fluid type/spacing when it improves continuity;
- percentage/bounded widths;
- `max-width` for readable text and page containment;
- `aspect-ratio` for stable media;
- responsive WordPress images via `srcset`/`sizes`;
- content-driven breakpoints.

Avoid:
- fixed page widths;
- fixed section heights;
- fixed card heights that can clip real content;
- absolute-positioned primary layout;
- device-specific duplicate content;
- a large collection of one-off media queries;
- designing only at 1440px and 390px with broken states in between.

### Responsive range

Design intentionally for the whole range from approximately **320px to 2560px+**.

Key behavior:
- small phones: prioritize one clean reading/action path;
- normal phones: maintain comfortable gutters and tap targets;
- tablets: use 1–2 columns based on actual available width;
- laptops: avoid cramped 4-column layouts when copy does not fit;
- desktops: use balanced whitespace and bounded text widths;
- ultrawide: contain content rather than stretching text/cards unnaturally across the viewport.

### Touch / pointer behavior

Every interaction must work without hover.

Hover may enhance a card/button, but:
- the action must remain obvious on touch;
- focus-visible must communicate keyboard state;
- tap targets should be at least ~44px where practical;
- hover-only tooltips/content are not acceptable for important information.

### Dynamic content principles

"Dynamic" means maintainable WordPress behavior, not unnecessary frontend complexity.

Use WordPress dynamically for:
- navigation menus;
- Blog Posts/categories/archives;
- featured images;
- shared templates;
- reusable global components;
- site URL/logo/title settings;
- future administrator-managed content when appropriate.

Do not introduce a custom post type, custom database layer, headless frontend or JS framework unless the editorial requirements genuinely justify it.

### Responsive component rules

**Hero**
- no fixed hero height;
- copy and media must reflow naturally;
- hero image uses stable aspect ratio/object-fit;
- CTA group wraps cleanly;
- ornament simplifies before content becomes cramped.

**Course/teacher/blog cards**
- grid adapts by available component width;
- cards grow with content;
- buttons remain reachable when titles/descriptions wrap;
- image ratio remains consistent without clipping faces/content badly.

**Pricing**
- 4 columns only when readable;
- collapse before cards become narrow;
- never reduce core text to tiny sizes just to preserve four columns.

**Forms**
- full-width controls on narrow screens;
- labels never overlap;
- two-column fields collapse based on width/content;
- validation messages do not cause horizontal overflow.

**Header**
- use a real mobile/tablet navigation state;
- do not squeeze desktop nav into a narrow row;
- sticky state must preserve viewport space on mobile.

**Footer**
- columns collapse intentionally;
- long contact/link text wraps safely;
- CTA blocks remain visually balanced at narrow widths.

### Responsive QA principle

Do not approve a page by checking only Elementor's Desktop/Tablet/Mobile toggles.

Validate real browser widths, intermediate widths, orientation changes and wide-screen containment. A page is complete only when there are no obvious broken states between the named QA samples.

## 20. Motion & Interaction

The website should feel **dynamic, engaging and premium**, while still calm and respectful for a Quran-learning brand.

Motion is part of the design system. It must improve hierarchy, storytelling, feedback or perceived quality — never exist only to show off an effect.

### Motion character

Use motion that feels:

- soft;
- confident;
- elegant;
- intentional;
- slightly editorial;
- responsive to user action;
- consistent across pages.

Avoid motion that feels:

- flashy;
- gaming-like;
- crypto/agency-demo-like;
- chaotic;
- overly bouncy;
- constantly moving;
- slow enough to block content.

### Timing system

```css
--eqc-motion-fast: 160ms;
--eqc-motion-base: 260ms;
--eqc-motion-reveal: 520ms;
--eqc-motion-slow: 800ms;
--eqc-motion-stagger: 70ms;
--eqc-ease-standard: cubic-bezier(.22, 1, .36, 1);
--eqc-ease-soft: cubic-bezier(.16, 1, .3, 1);
```

Use a small reusable timing vocabulary instead of random durations.

### Preferred techniques

Prefer, in order:

1. CSS transitions/transforms/keyframes;
2. supported Elementor Free entrance animations when appropriate;
3. reusable vanilla JS / IntersectionObserver for viewport reveals;
4. lightweight SVG motion graphics;
5. a dedicated animation library only when a specific interaction clearly justifies it.

Animate `transform` and `opacity` whenever possible.

### Recommended signature motion

Evaluate and selectively use:

- staged hero copy/CTA entrance;
- arch-image mask/clip reveal;
- subtle geometric SVG/ornament reveal;
- section-heading gold line draw;
- staggered course-card entrance;
- small card lift/border/arrow hover interactions;
- progressive "How It Works" line/path drawing;
- teacher-card image micro-zoom;
- pricing-card focus/hover emphasis;
- smooth FAQ accordion transition;
- blog image/arrow microinteraction;
- subtle sticky-header state change;
- restrained final-CTA background ornament motion.

The homepage may carry several coordinated motions. Inner pages should reuse that language rather than introduce unrelated effects.

### Motion graphics

Prefer project-owned SVG/CSS Islamic geometry — arches, rosettes, line ornaments and subtle patterns — over heavy animation assets.

External animated assets must have a verified license, a clear design purpose, an optimized file size and a static/reduced-motion state.

### Motion density

- approximately one signature motion idea per major section;
- avoid animating every child widget independently;
- no more than 1–2 subtle continuous decorative animations in one viewport;
- simplify/disable complex motion on mobile;
- never hide essential information behind hover-only behavior.

### Do not use

- scroll hijacking;
- forced smooth-scroll libraries;
- blocking preloaders;
- cursor replacement;
- constant text marquees unless there is a strong content reason;
- autoplay background video by default;
- aggressive parallax;
- large particle/canvas effects;
- infinite CTA pulsing;
- excessive 3D tilt.

### Accessibility

All custom motion must respect `prefers-reduced-motion`.

When reduced motion is requested:

- reveal content immediately;
- remove nonessential transforms/parallax;
- keep state changes understandable;
- preserve all functionality.

### Performance

Motion must not:

- delay H1/LCP content;
- introduce visible CLS;
- create obvious mobile scroll jank;
- require a heavy library for trivial fades;
- attach duplicated scroll listeners per section.

Use IntersectionObserver or similarly efficient strategies for viewport-triggered effects when custom JS is required.

## 21. Responsive Behavior

Responsive design is fluid from approximately 320px through ultrawide desktop sizes. The named widths below are validation samples, not fixed design buckets.

### Small Phone — ~320–359px

- preserve readable 16px body text;
- simplify ornament and secondary visual effects first;
- keep CTA buttons easy to tap;
- ensure no content depends on two columns;
- avoid hero compositions that consume several screens before the first action.

### Phone — ~360–430px

- one clear content flow;
- H1 must not dominate the entire first screen;
- primary CTA appears early;
- complex multi-column sections become one column;
- forms use full available width;
- no clipped ornament/SVG overflow;
- cards can grow with content rather than match artificial fixed heights.

### Tablet — ~768–1024px

- use one or two columns based on actual content fit;
- split hero only while both sides remain comfortably readable;
- reduce decorative elements before shrinking typography excessively;
- avoid forcing desktop navigation when it becomes cramped;
- handle both portrait and landscape intentionally.

### Laptop — ~1024–1440px

- maintain useful gutters;
- allow 3-column course grids when card copy fits;
- use 4-column pricing only when readability remains strong;
- avoid oversized empty regions caused by fixed-height desktop sections.

### Desktop — ~1440–1920px

- balanced whitespace;
- bounded readable line lengths;
- use the max-content system rather than stretching every component;
- maintain strong visual hierarchy and purposeful negative space.

### Wide / Ultrawide — 1920–2560px+

- keep main content centered/bounded;
- do not stretch paragraphs into very long lines;
- allow selected hero/background treatments to breathe without stretching cards unnaturally;
- verify background ornaments and gradients do not expose abrupt edges.

### Target QA samples

- 320x568
- 360x800
- 375x812
- 390x844
- 430x932
- 768x1024
- 820x1180 where practical
- 1024x768
- 1280x800
- 1366x768
- 1440x900
- 1920x1080
- 2560x1440 where practical

Also inspect several intermediate widths by resizing the browser. Fix the layout at the component breakpoint where content becomes cramped rather than relying only on device labels.

## 22. Accessibility

Minimum target: WCAG 2.2 AA-oriented implementation.

Required:

- semantic heading hierarchy
- one appropriate H1 per page
- visible focus states
- keyboard-usable menus/forms/accordions
- meaningful alt text for informative images
- empty alt text for decorative images
- sufficient text contrast
- labels for form fields
- accessible error messages
- 44x44px touch targets where practical
- no critical information conveyed by color alone

## 23. Performance Rules

- optimize images before upload
- prefer AVIF/WebP when supported
- serve properly sized responsive images
- do not lazy-load the likely LCP hero image
- lazy-load below-the-fold images
- keep font families and weights limited
- avoid third-party animation libraries unless justified
- keep Elementor addon count minimal
- avoid background videos by default
- use SVG for ornaments/icons where appropriate

Do not install a cache plugin until the host/cache stack is audited.

## 24. Design QA Checklist

Before a page is accepted:

- follows client-reference visual language
- matches global tokens rather than arbitrary local styling
- has one clear primary CTA
- typography hierarchy is obvious
- body text line lengths are comfortable
- cards align cleanly
- ornament does not interfere with readability
- no fabricated claims are visible
- image quality is appropriate
- no layout overflow at target widths
- hover/focus states work
- mobile looks intentionally composed
- desktop and mobile CTA hierarchy match
- no placeholder content is accidentally public

## 25. Definition of Done — Visual

A page is visually complete only when:

1. Desktop, tablet and mobile layouts have been manually checked.
2. Important states have been tested in a real browser.
3. Content is either client-approved or clearly marked as staging placeholder.
4. No section relies on a broken Elementor workaround.
5. Custom CSS follows the shared token/class system.
6. The page is recognizably Easy Quran Classes, not a clone of the inspiration site.
