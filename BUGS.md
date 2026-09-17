# YOWM Studio — Bug & polish log

Running list of issues found during beta. Tackled in batches, not one at a time.

## Testing environments
- **Admin (Lani):** Chrome — primary.
- **Student view:** Zen browser. Note: Zen is Chromium-based, so its engine ≈ Chrome — good for workflow testing, but not a *different* rendering engine.
- **Windows:** covered by a couple of student testers.
- **Worth adding:** **Safari (WebKit)** — a genuinely different engine from Chrome/Zen and the most likely to surface CSS/layout quirks the Chromium browsers hide. Firefox (Gecko) is a nice-to-have third.

## Open bugs
_(none open right now.)_

## Parking lot (deliberately deferred — do NOT build now)
- **Auto-generate the cohort schedule.** _Target: the Dec/Jan 2026→2027 between-classes window, not before._ Today the schedule is a hand-built "at a glance" resource Lani assigns per cohort. The pattern is fixed each year: **first lecture posts the 2nd Sunday in March, then runs on the standard pattern.** Elegant approach: generate the schedule from data the plugin already holds — each lesson's per-cohort release date — so the schedule box builds itself. Nice-to-have, low priority. _[parked 2026-09-17]_
- **In-house discussion / Discord replacement.** Tempting, explicitly deferred. Building discussion is a whole second product (threading, notifications, moderation, spam, mobile) + an ongoing moderation burden, and it isn't YOWM's core value (lessons + gating + private podcasts is). Discord already does it well and students know it. Revisit ONLY if beta students report Discord genuinely failing them — and even then the answer is a purpose-built tool (Discourse / bbPress / Circle), never a from-scratch build inside YOWM Studio. _[parked 2026-08-06]_

## Fixed
- **#1 Cohort-year dropdown wouldn't close (Students → Invite a student)** — it now closes on click-away so it no longer covers the Payment field. _Shipped 0.27.0, 2026-09-17._
- **#2 Phone classroom nav cramped / left-jammed** — welcome line gets its own row, links wrap evenly. _Shipped 0.28.0, 2026-09-17._
