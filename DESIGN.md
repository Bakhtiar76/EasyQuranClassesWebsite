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

  --eqc-gold-700: #6F551C; /* Small gold text on cream: AA contrast. */
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

### Type Scale

Type does **not** ride `--eqc-u` linearly. Every role is
`clamp(floor, calc(n * var(--eqc-u)), ceiling)`, where `n` is the size in px
at the 1600px reference width. The floor guarantees legibility at any width
*continuously*; the ceiling stops headings ballooning on a 2560px display.

There is no separate mobile table and no floor media query — the clamp is the
floor at every width. A breakpoint-bounded floor only holds on one side of the
breakpoint, which is what left a 6px discontinuity at exactly 1100px and
mainstream laptops (1101–1600) unfloored. See QA/LESSONS.md #36, #38.

| Token | Floor | @1600 | Ceiling | Usage |
|---|---:|---:|---:|---|
| Display XL | 34px | 52px | 60px | About section heading |
| H1 | 32px | 46px | 54px | page hero |
| H2 | 28px | 44px | 52px | major section title |
| H2 compact | 24px | 34px | 40px | heading in a narrow column (teachers) |
| H3 | 19px | 22px | 26px | course/blog card title |
| H4 | 17px | 18px | 20px | card headings |
| Body L | 17px | 17.5px | 19px | hero/about intro |
| Body | 15px | 15.5px | 17px | standard copy, card body |
| Small | 14px | 14.5px | 16px | metadata/labels |
| XSmall | 13px | 13px | 14px | smallest label role |
| Eyebrow | 12px | 13.5px | 15px | uppercase section label |
| Button | 15px | 15px | 17px | buttons |
| Price | 30px | 38px | 44px | pricing card figure |

**Heading-to-body ratio must stay between 2.5 and 3.2.** At 1600 it is 2.84.
It was 5.0 when type shared the layout unit, which is what made the page read
as simultaneously oversized and unreadable.

**Never set `font-size` as a bare `calc()` on `--eqc-u`.** 33 rules did, which
is how ten roles reached 7.6–9.4px on a 390px phone while every contrast check
passed. Use the tokens so a size cannot escape its floor.

## 7. Layout System

### Global Widths

```css
--eqc-u: clamp(0.70px, 0.0625vw, 1.32px);    /* 1 unit = 1px on the 1600px reference screen */
--eqc-content-w: 85.6%;
--eqc-content-max: calc(1119 * var(--eqc-u));   /* 1343px @1920 */
--eqc-content-narrow: calc(558 * var(--eqc-u));
--eqc-text-max: calc(463 * var(--eqc-u));
```

> **Updated from the client reference, 2026-09-09** (`claude-opus-5`, design
> parity pass — `QA/design-review/home.md` findings 1 and 4).
>
> `--eqc-content-max` was 1240px in this document and 1400px in `tokens.css`;
> neither matched the reference. Measured on `Home.jpeg` (1307px canvas), the
> hero's content column runs from the H1's left edge x105 to the arch's outer
> right edge x1224 — 1119px, i.e. 85.6% of the canvas, or **1644px at 1920**.
> `.eqc-container` is `border-box` with `--eqc-gutter` padding inside, so the
> token carries 1644 + 2x32 = **1708px**. The trust panel gives a slightly
> tighter 1620px; the difference is that panel's own inset.
>
> The H1 cap rose from 66px to **79px**, derived from glyph width rather than
> assumed font metrics: the hero's second line spans 823px at 1920 in the
> reference, and that string in DM Serif Display needs 897px at 86px, so the
> reference size is 86 x 823/897 ~ 79px. At 66px the hero H1 wrapped to three
> lines where the reference has two — and in this design the line break is
> load-bearing, because string length is what sets the column geometry.
>
> Only ratios measured *within a single* reference file are meaningful: the
> section JPEGs are crops at different zoom levels (`QA/LESSONS.md` #11).

> **Superseded by the proportional scale system, 2026-09-09** (`claude-opus-5`
> — `QA/design-review/home.md` "Proportional scale system").
>
> The px values in the tables above are no longer the source of truth. The
> client's design is **proportional**: every dimension in `Assests/*.jpeg` is a
> fixed fraction of the viewport, measured on a 1307px canvas. `tokens.css`
> defines `--eqc-u` as one pixel on that canvas, and every size token is
> `calc(<measured native px> * var(--eqc-u))`. The px figures shown here are
> what those resolve to at 1920.
>
> Measured type, in native units: H1 53.7, body-l 16.6, body 13.6, small 11.6,
> xsmall 10.3, button 11.2, eyebrow 8.85 — all derived by **glyph width**
> against the reference's own line widths, never from assumed font metrics.
>
> Two deliberate departures from pure proportion, both under
> TASK-DESIGN-PARITY.md §2A: the unit is capped at 1.607 (~2100px) so a 2560px
> display does not get 32px body copy, and below 1100px the type floors out at
> readable minimums (§6's 16px body rule) while layout keeps scaling. The
> floors are applied in a media query, not as `max()` on the token, because
> they sit above the reference's own small-role sizes and would otherwise
> change the rendering at the reference width itself.

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

Parity implementation (2026-09-09): shared chrome spans94% of the viewport, capped at1810px. Header bar scales64–150px; the approved vector mark accompanies a two-line live wordmark. Six links are Home, About Us, Courses, Teachers, Pricing, Contact Us; the separate gift CTA reads FREE TRIAL. The drawer is used below1024px, traps focus, makes the background inert and clears its state when resized to desktop. The header compacts on scroll. Fonts are bundled locally with their OFL licences.

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

Parity implementation follows `Assests/End.jpeg`: a dark CTA with a two-line white/gold heading and three-line paragraph, one white rounded action capsule containing four avatars, a count circle and gold button. It remains two columns from1120px. The cream footer panel has three ruled columns (brand/about, contact, two columns of four quick links), then a dark copyright bar with an overlapping logo medallion. The footer grid stacks below1024px. Small gold text uses gold700; decorative gold retains the reference palette. Missing social URLs render noninteractive labelled marks. Photography sources and fictional portrait status are documented in QA/ASSET-SOURCES.md.

TASK-DESIGN-PARITY.md explicitly permits exact unverified reference claims on the local staging site. Every such string belongs in QA/PLACEHOLDER-REGISTER.md and requires clearance before production; this local task exception supersedes the general prohibition below during parity work only.

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
