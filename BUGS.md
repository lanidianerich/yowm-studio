# YOWM Studio — Bug & polish log

Running list of issues found during beta. Tackled in batches, not one at a time.

## Testing environments
- **Admin (Lani):** Chrome — primary.
- **Student view:** Zen browser. Note: Zen is Chromium-based, so its engine ≈ Chrome — good for workflow testing, but not a *different* rendering engine.
- **Windows:** covered by a couple of student testers.
- **Worth adding:** **Safari (WebKit)** — a genuinely different engine from Chrome/Zen and the most likely to surface CSS/layout quirks the Chromium browsers hide. Firefox (Gecko) is a nice-to-have third.

## Open bugs
1. **Cohort-year dropdown won't close — Students → Invite a student.** After picking a cohort year, the checkbox popup stays open and covers the Full Year / Monthly payment dropdown below it. You can click "behind" it to reach the dropdown, but it's janky. → Fix: close the `<details>` year dropdown on outside-click (and/or after a selection) in `admin.js`. _[reported 2026-08-06]_
2. **Phone cohort nav is cramped / left-jammed (student-facing).** On narrow phone widths the classroom nav (`.yowm-cohort-nav-inner`) wraps but items pile to the left unevenly and look crunched. iPad and desktop are fine. → Fix: give the mobile nav a proper responsive layout in `front.css` (clean stack or scrollable row, even spacing, separate the "Welcome, {name}" line; reconsider the `.yowm-change-cohort { margin-left:auto }` behavior on wrap). Confirmed on iPhone Safari. _[reported 2026-08-06]_

## Parking lot (deliberately deferred — do NOT build now)
- **In-house discussion / Discord replacement.** Tempting, explicitly deferred. Building discussion is a whole second product (threading, notifications, moderation, spam, mobile) + an ongoing moderation burden, and it isn't YOWM's core value (lessons + gating + private podcasts is). Discord already does it well and students know it. Revisit ONLY if beta students report Discord genuinely failing them — and even then the answer is a purpose-built tool (Discourse / bbPress / Circle), never a from-scratch build inside YOWM Studio. _[parked 2026-08-06]_

## Fixed
_(none yet — cleared here as they ship.)_
