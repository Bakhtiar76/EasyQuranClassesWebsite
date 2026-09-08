# Local staging content — client clearance required

All entries below are unverified staging content authorized by TASK-DESIGN-PARITY.md§2. Production release is blocked until the client confirms/replaces every entry. This file is being populated page-by-page; it is not yet a complete audit of the site.

| Page/section | Exact staged string or asset | Reference / origin | Single edit location | Client question | Layout impact |
|---|---|---|---|---|---|
| All / footer CTA | Your First Class Is Free. / Start This Week. | End.jpeg | footer.php CTA heading | Is the first class free and can a new student start this week? | Two-line heading |
| All / footer CTA | Tell us the student's age, level, and the times that suit you. We'll match a teacher and confirm your trial – usually within a day. | End.jpeg | footer.php CTA paragraph | Can teacher matching and trial confirmation usually happen within a day? | Three-line paragraph |
| All / footer CTA | +1.5K / Happy Students | End.jpeg | footer.php stat text | Is this count valid and what does it measure? | Load-bearing stat circle |
| All / footer CTA | Four portrait avatars | Newly generated fictional portraits, 2026-09-09 | footer.php avatar slug array | Approve illustrative portraits or supply verified student/family portraits with consent. These do not substantiate the adjacent count. | Load-bearing four-avatar capsule |
| All / footer | Online Quran classes for kids and adults with qualified teachers. Learn Quran, Tajweed, Hifz and Islamic Studies from the comfort of your home. | End.jpeg | tools/00-site-setup.php eqc_footer_about → Customizer | Confirm audiences, subjects and qualified-teacher claim. | Four-line paragraph |
| All / contact | Random Address, USA 733898 | End.jpeg | tools/00-site-setup.php eqc_address → Customizer | What is the public postal address, or should the address be removed? | Two-line row |
| All / contact | (406) 555-0120 | End.jpeg | tools/00-site-setup.php eqc_phone_display → Customizer | What phone number may be published? | One-line row |
| All / contact | info@easyquranclasses.com | End.jpeg | tools/00-site-setup.php eqc_contact_email → Customizer | Does this monitored mailbox exist and accept enrolment enquiries? | Long unbroken line; wrapping checked |
| All / WhatsApp | 10000000000 and +1 (000) 000-0000 | Existing dummy | tools/00-site-setup.php WhatsApp mods → Customizer | Provide the approved WhatsApp number and display format. | Action destination |
| All / footer links | Give Donation; Education Support; Our Campaign; Privacy Policy; Terms & Conditions | End.jpeg | tools/00-site-setup.php footer_items | Supply approved destination URLs/content. Local staging routes these labels to contact enquiries; no payment or legal policy has been invented. | Eight total links in two columns |
| All / footer social | Facebook, Twitter, Instagram, YouTube symbols; no account URLs | End.jpeg | Customizer eqc_social_* | Provide approved account URLs or approve removal. | Four decorative circles until URLs exist |
| Home / hero trust row | Trusted by 5,000+ Students & Parents | Home.jpeg | tools/pages/10-home.php hero trust row (one string) | Is the 5,000+ student/parent figure accurate, and what does it count? | Load-bearing: sits beside a fixed five-avatar stack |
| Home / hero trust row | Five overlapping portrait avatars | Reused teacher/testimonial staging portraits (generated fictional people) | tools/pages/10-home.php $hero_avatar_ids | Approve illustrative portraits or supply consented student/family photos. They do not substantiate the adjacent count. | Load-bearing five-avatar stack |
| Home / hero trust row | Five filled gold stars | Home.jpeg | tools/pages/10-home.php eqc_icon_str( 'star-filled' ) repeat count | Is there a real aggregate rating, and from which platform? Decorative until sourced (aria-hidden, no rating claim in the accessibility tree). | Fixed five-star row |
| Home / trust strip | Students from 20+ Countries | Home.jpeg | tools/pages/10-home.php eqc_trust_tile( 'globe', ... ) | How many countries do students actually join from? | Two-line tile title |
| Home / trust strip | Safe & Secure Learning / Support 7 Days A Week / Certificates Available | Home.jpeg | tools/pages/10-home.php eqc_trust_tile() calls | Confirm 7-day support and that certificates are actually issued. | Four-column strip; title length drives tile height |
| All / copyright | © 2026 Easy Quran Classes. All Rights Reserved. | End.jpeg | footer.php (dynamic year) | Confirm legal business/trading name and ownership. | Single copyright line |

Common navigation labels, brand name and CTA button wording are transcribed interface copy; any client change to their lengths requires responsive rechecking. Source seed values apply on a fresh bootstrap; the corresponding Customizer field is the single current-site edit.

## Replacement photography

The four `teacher-*.webp` and three `testimonial-*.webp` portraits are newly generated fictional people, not verified likenesses of the reference names. All names, credentials, reviews and customer associations remain local staging content pending clearance. The hero is a generated illustrative lesson scene; it does not depict an actual lesson or pupil. The child-reading photograph is licensed editorial subject imagery and does not establish an endorsement or enrolment relationship. Sources and generation provenance are in ASSET-SOURCES.md.
