# YOWM Studio — Bug & polish log

Running list of issues found during beta. Tackled in batches, not one at a time.

## Testing environments
- **Admin (Lani):** Chrome — primary.
- **Student view:** Zen browser. Note: Zen is Chromium-based, so its engine ≈ Chrome — good for workflow testing, but not a *different* rendering engine.
- **Windows:** covered by a couple of student testers.
- **Worth adding:** **Safari (WebKit)** — a genuinely different engine from Chrome/Zen and the most likely to surface CSS/layout quirks the Chromium browsers hide. Firefox (Gecko) is a nice-to-have third.

## Open bugs
4. **Podcast episode titles should include the module + lecture number (enhancement).** Episodes are currently titled by the lesson's plain WP title (e.g. "Preparing for Drafting"). Lani wants the module + number prefix — e.g. "Drafting #6: Preparing for Drafting" (format `{Module} #{Number}: {Title}`; session episodes keep the "… — Live Session" suffix). This reverses the 0.6.2 change that stripped the prefix. → Change `podcast_episode_title()` in `yowm-studio.php` to build from `lesson_module_name` + '#' + `META_NUMBER` + ': ' + clean title. _[requested 2026-09-20]_

5. **Structured lesson sections — Lecture Summary, Homework, Supplemental Information/Links (enhancement).** Add defined sections to the lesson editor + lesson page; each hides entirely when left empty (no empty headers). **Target design: `docs/lesson-sections-target.webp`** — Lani currently hand-authors this in Gutenberg; the feature turns it into reusable structured fields. Headings are **H2** (confirmed 2026-09-28), matching the existing "Listen to the audio lecture" section.
   - **Lecture Summary** → rendered under a "Lecture Summary" H2 (prose in the target).
   - **Homework** → renders as a **bulleted list** in the target (with inline links + italics).
   - **Supplemental Information/Links:** → repeatable rows, each a text label + a URL; rendered as a bulleted list of links. Reuse the lecture-versions repeater JS pattern in `admin.js` for add/remove rows.
   - **Rich text needed:** the target shows italics and inline links inside Lecture Summary + Homework, so a plain `<textarea>` won't do — use a small rich editor (`wp_editor()`/TinyMCE) per field, saved via `wp_kses_post`. (Reconsider at build vs. simply structuring the Gutenberg content.)
   - **Outline integration:** render the sections INSIDE the outline-scanned `.entry-content` as real H2s so they auto-appear in the "On this page" sidebar (the audio section already does this).
   - Suggested meta: `_yowm_lecture_summary`, `_yowm_homework`, `_yowm_supplemental_links` (array of `{text,url}`). Likely lesson-level (shared across cohorts) — confirm at build. Editor fields in the Lesson meta box; persist in `save_meta`; render each in `single-lesson.php` only when non-empty.
   - **Open Q for build:** do these sit *alongside* the free Gutenberg lesson content, or become the lesson's structure (replacing free-form)? The target shows only structured sections. _[requested 2026-09-28]_

3. **Classroom nav "Welcome, {name}" can show an email instead of a first name (student-facing).** In `cohort-nav.php` the greeting uses `first_name`, then falls back to `display_name` — which for an account with no first name is the email (seen on Lani's admin account, "Welcome, lani.d.rich@gmail.com"). Students created via the roster have a first name so they're usually fine, but no one should ever see a raw email here. → Fix: prefer first name; fall back to `display_name` only when it doesn't look like an email address; otherwise drop the name entirely (e.g. just "Welcome" / "Welcome back"). Warmer + safe. _[reported 2026-09-17]_

## Parking lot (deliberately deferred — do NOT build now)
- **Auto-generate the cohort schedule.** _Target: the Dec/Jan 2026→2027 between-classes window, not before._ Today the schedule is a hand-built "at a glance" resource Lani assigns per cohort. The pattern is fixed each year: **first lecture posts the 2nd Sunday in March, then runs on the standard pattern.** Elegant approach: generate the schedule from data the plugin already holds — each lesson's per-cohort release date — so the schedule box builds itself. Nice-to-have, low priority. _[parked 2026-09-17]_
- **In-house discussion / Discord replacement.** Tempting, explicitly deferred. Building discussion is a whole second product (threading, notifications, moderation, spam, mobile) + an ongoing moderation burden, and it isn't YOWM's core value (lessons + gating + private podcasts is). Discord already does it well and students know it. Revisit ONLY if beta students report Discord genuinely failing them — and even then the answer is a purpose-built tool (Discourse / bbPress / Circle), never a from-scratch build inside YOWM Studio. _[parked 2026-08-06]_

## Fixed
- **#1 Cohort-year dropdown wouldn't close (Students → Invite a student)** — it now closes on click-away so it no longer covers the Payment field. _Shipped 0.27.0, 2026-09-17._
- **#2 Phone classroom nav cramped / left-jammed** — welcome line gets its own row, links wrap evenly. _Shipped 0.28.0, 2026-09-17._
