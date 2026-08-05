# Changelog

## 0.20.0

- Added a "Podcast episode notes" field to the Lesson editor. It doubles as the lesson excerpt and the podcast episode description, and it's the field early WordPress posts used before the YOWM Studio migration. The feed now automatically appends a link back to the cohort classroom in each episode's notes.
- Removed Gutenberg's separate Excerpt panel on lessons so there is a single, clear place to write episode notes.

## 0.19.0

- Fixed lesson and Class Info links pointing to the wrong year. Opening a lesson from inside a cohort now keeps you in that cohort's year (e.g. a lesson clicked in /2026/ stays on /2026/) instead of jumping to the lesson's first-assigned year, which could 404 if that year's copy hasn't been released yet.

## 0.18.0

- Fixed a fatal error (critical-error page) when opening a lesson that can't be resolved for its cohort — for example a lesson that isn't assigned to that year yet. The router now serves a normal 404, and the lesson and Class Info templates degrade to a calm "not available" message instead of white-screening.
- Added a "Copy error details" button to YOWM Studio → System Diagnostics, so the last fatal error can be copied as plain text in one click.

## 0.17.0

- Rebuilt the Students admin area to be cleaner and less clunky.
- Invite form fields now stack top to bottom (so new fields are easy to add, and the Email field no longer looks oversized).
- Cohort years are now a compact checkbox dropdown in both the invite form and the roster, so accumulating years stay tidy.
- Roster feed column no longer shows the raw URL — just a "Copy" button and a "New URL" link.
- Roster columns are sortable: click a header to sort by that value.
- Added a payment type (Full year / Monthly) per student. "Suspend" now only appears for month-to-month students, since full-year students don't need revoking.
- Moved pending invitations into their own section beneath the roster.

## 0.16.0

- Student-facing classroom pages now open every external link (anything leaving the site, like Discord or Zoom) in a new tab, so students never lose their place. Internal classroom navigation still opens in the same tab.

## 0.15.0

- Added a moderator toggle to the Students roster: a "Can edit" checkbox turns a student into a workshop assistant who can edit all YOWM content (Cohorts, Lessons, Class Info, Media) while keeping their student account and personal podcast feed, and while staying locked out of Students, Settings, and Plugins. One login, no second account.
- Fixed the Invite-a-student form so the Email field is the same width as First and Last name.

## 0.14.0

- Added one-click updates: YOWM Studio now checks its GitHub repository and appears in the normal WordPress Plugins → Update list. After installing this version once, future updates install with a single click — no more zip uploads.

## 0.13.3

- Simplified the wrong-cohort screen: removed the "Sign in with a different account" button (there is only ever one login per student) and made "Email Lani" the primary button.

## 0.13.2

- Added a simplified admin menu for administrators: shows only YOWM Studio, Media, Plugins, and Settings by default.
- Added a "Full WordPress menu →" escape hatch and a "⬅ Simplify menu" toggle; the choice is per-user and never affects students.
- In simple mode, logging in lands on YOWM Studio instead of the generic dashboard.

## 0.13.1

- Made "contact Lani" on the no-access screen a clickable mailto link to lani@lanidianerich.com.

## 0.13.0

- Added a unified all-years student roster.
- Added pending invitations so students choose usernames before account creation.
- Added custom username/password setup page.
- Added multi-cohort invitation and returning-student support.
- Added editable first/last names and cohort memberships.
- Activated personal podcast feed delivery.
- Added suspend, restore, rotate feed, delete invitation, and permanent student deletion.


## 0.12.5

- Added a signed-in student welcome note to cohort navigation.
- Fixed “cannot load student roster” when changing cohorts.
- Corrected Student Access URLs from edit.php to admin.php.
- Fixed roster action redirects and Cohort-editor roster links.
- Updated the remaining classroom navigation label from Library to Class Info.


## 0.12.4

- Fixed fatal error: undefined method `YOWM_Studio::current_cohort()`.
- Resolved cohort access from `yowm_cohort_year` or the parsed classroom route.
- Kept diagnostic error capture available.


## 0.12.3 diagnostic

- Added a shutdown handler that records the most recent fatal PHP error.
- Added YOWM Studio → System Diagnostics.
- Displays the exact PHP message, file, line, request path, and environment versions.
- Does not record passwords or student data.


## 0.12.2

- Removed a possible front-end fatal dependency from cohort access checks.
- Simplified cohort gate rendering.
- Redirected students away from wp-admin.
- Hid the admin toolbar for YOWM Student accounts.
- Sent student logins to an assigned classroom.


## 0.12.1

- Fixed a possible critical error on cohort classroom routes.
- Removed the fragile early personal-feed rendering bridge.
- Fixed student creation not submitting from the Cohort editor.
- Moved student management to a standalone Student Access screen.
- Added field-based Name and Email roster entry.
- Added cohort selection and explicit invitation result counts.


## 0.12.0

- Replaced shared cohort passwords with invitation-only individual student accounts.
- Added bulk roster entry and password-setup invitation emails.
- Added one-account access across multiple cohorts.
- Added active and revoked cohort memberships.
- Added personal, revocable podcast feeds per student and cohort.
- Added resend invite, feed rotation, revoke, and restore controls.
- Added familiar email/password login and WordPress password reset.
- Blocked public account registration.
- Renamed Authentication to Student Access.


## 0.11.1

- Fixed Authentication being registered under a hidden Cohort menu.
- Added Authentication directly to the visible YOWM Studio submenu.
- Renamed backend Library labels and screens to Class Info.
- Preserved existing internal identifiers and public URLs.


## 0.11.0

- Replaced native WordPress post-password participation with YOWM-only cohort authentication.
- Preserved simultaneous access to multiple cohorts through independent signed cookies.
- Added strict no-store/private cache headers and cache-plugin bypass constants.
- Removed cache-fragile login nonces from the public cohort password form.
- Added rate limiting for unsuccessful password attempts.
- Added a unique post-login redirect value to defeat stale gate-page caches.
- Added an Authentication diagnostics screen.
- Migrated and removed native WordPress Cohort passwords.
- Renamed student-facing Library labels to Class Info without changing URLs.
- Added safe recovery for older generic YouTube session-video metadata.


## 0.10.0

- Fixed reusable Lessons appearing in a cohort with no Lecture-version assignment.
- Made blank cohort assignment mean not assigned rather than inherited.
- Unified written-Lesson and podcast-Lecture release dates.
- Standardized both releases at 6:00 AM America/Denver.
- Preserved backward compatibility with older separate podcast release metadata.
- Made new Lecture versions immediately selectable without saving and reloading.
- Updated assignment labels live while version names are typed.
- Added clearer Current and Archived status labels.
- Improved student-facing transcript discovery.


## 0.9.1

- Removed automatic server-side audio conversion.
- Removed the FFmpeg dependency and conversion AJAX endpoint.
- Added reliable file-extension checks based on the selected filename or URL.
- Added clear MP3-ready and non-MP3 warning messages.
- Added visible MP3-only workflow guidance in Lecture and Live Session editors.
- Preserved permanent episode GUIDs from 0.9.0.


## 0.9.0

- Added automatic server-side audio conversion to MP3 after Media Library selection.
- Preserved original uploads and added converted MP3 files as separate Media Library attachments.
- Reused existing conversions when the same source file is selected again.
- Added clear conversion progress, success, and failure messages.
- Added permanent UUID-based GUIDs for Lecture versions.
- Added permanent UUID-based GUIDs for cohort-specific Live Sessions.
- Stopped deriving podcast GUIDs from mutable audio URLs.
- Migrated existing Lecture versions and Live Sessions to stable GUIDs automatically.


## 0.8.0

- Added multiple archived Lecture versions per Lesson.
- Added per-Cohort Lecture-version assignments.
- Preserved old audio and transcripts for returning Cohorts.
- Defaulted new Cohorts to no Lecture assignment.
- Standardized Lecture and written-Lesson releases to 6:00 AM America/Denver.
- Fixed cohort navigation placement and mobile theme-menu conflicts.
- Added Copy podcast feed Library items.
- Added stronger RSS refresh metadata for podcast clients.
- Migrated the current reusable Lecture into an Original lecture version.


## 0.7.1

- Added persistent cohort navigation.
- Added direct links to Classroom home, Lessons, Library, and Change cohort.
- Fixed `/YEAR/library/` being interpreted as a Lesson slug.
- Fixed undefined archive variables in template selection.
- Made archive routing resilient when hosting or cache layers bypass rewrite variables.


## 0.7.0

- Replaced per-cohort Lecture uploads with one reusable Lecture audio file.
- Added explicit cohort selection and per-cohort release dates for the shared Lecture.
- Kept new cohorts excluded until explicitly selected.
- Kept Live-session audio and video cohort-specific.
- Added a Lecture transcript field and protected front-end transcript section.
- Avoided fixed 365-day date shifting so future scheduling can handle leap years correctly.


## 0.6.2

- Removed Module and Lesson-number prefixes from podcast episode titles.
- Kept the Live Session suffix for session recordings.
- Added per-cohort podcast artwork selection through the WordPress Media Library.
- Added editable podcast author metadata.
- Added channel and episode artwork metadata to the RSS feed.


## 0.6.1

- Replaced fragile pretty podcast URLs with WordPress-safe query URLs.
- Moved feed rendering to early init before front-page and theme routing.
- Kept legacy pretty feed URLs as a fallback.
- Added clear enabled/disabled podcast status.
- Added a Test feed in a new tab button.
- Added a plain diagnostic message when a feed request is rejected.


## 0.6.0

- Added one secret private RSS podcast feed per cohort.
- Added feed enable, title, description, copy, and token-regeneration controls.
- Added WordPress Media Library audio selectors inside Lessons.
- Added cohort-specific Lecture audio and Live-session audio.
- Added immediate and scheduled podcast release behavior.
- Reused the same audio fields on the protected Lesson page.
- Added standard RSS 2.0 enclosures and Apple Podcasts-compatible metadata.
- Fixed archive-route variables in the virtual route handler.


## 0.5.1

- Restored Gutenberg YouTube and iframe-based embeds inside Lesson content.
- Added a direct YouTube fallback below cohort-specific session videos.
- Renamed Now to On deck.
- Placed the next scheduled Lesson directly below the current Lesson.
- Added the upcoming Lesson excerpt and Coming date.


## 0.5.0

- Fixed YouTube playback by normalizing public and unlisted YouTube URLs.
- Added per-cohort Lesson release dates and times.
- Hid scheduled Lessons from students until release.
- Added Now, Coming up, Quick links, Recent lessons, and Library previews.
- Added per-cohort Past lessons and Library archive pages.
- Added the next scheduled Lesson to the YOWM Studio dashboard.


## 0.4.0

- Renamed Resources to Library in the YOWM interface.
- Added reusable Lessons that can apply to all or selected cohorts.
- Separated live-session video and audio links by cohort.
- Migrated legacy lesson cohort assignments and recording links.
- Removed the automatic Sunday post heading.
- Kept the Library outline visible on every full Library page.
- Added a permanent Library home link to the outline.


## 0.3.6

- Explicitly forced Gutenberg for Cohorts, Lessons, and Resources.
- Added both post-type and individual-post block-editor filters for compatibility.
- Preserved the grouped Resource library and duplicate actions.


## 0.3.5

- Added one-click Resource duplication.
- Copied Resource content, metadata, cohort assignments, order, and featured image.
- Added Duplicate actions to both YOWM's Resource library and WordPress's standard list.
- Replaced the YOWM Resources submenu with a collapsible cohort-grouped library.
- Added All Cohorts and one folder per cohort.
- Kept access to the standard WordPress Resource list.


## 0.3.4

- Fixed Resource content being placed in the outline column.
- Removed the empty outline column when no automatic outline is available.
- Preserved the full reading width on desktop, tablet, and phone.


## 0.3.3

- Restored Gutenberg editing for Studio content.
- Rendered Information Card body content with Gutenberg formatting.
- Preserved legacy plain-text card content as a fallback.
- Rebuilt lesson headers with the calm classroom reading layout.
- Fixed lesson titles being oversized and squeezed beside the outline.


## 0.3.2

- Removed the duplicate native Module selector.
- Rendered Link resources as buttons.
- Removed resource-format labels from public cards.
- Added explicit Resource display ordering.
- Added download-link behavior and relative-URL support.
- Prevented empty resource URLs from refreshing the current page.


## 0.3.1

- Switched YOWM Studio content types to clear classic editing screens.
- Fixed password submission returning to the gate.
- Added visible cohort password status and controls.
- Added compatibility with passwords entered through Quick Edit.
- Made lesson cohort assignment consistently visible.
- Made resource type and cohort assignment consistently visible.
- Added explicit All Cohorts and Specific Cohorts scope controls.


## 0.3.0

- Added Link, Information Card, and Page resource types.
- Added optional resource descriptions and new-tab behavior.
- Kept empty cohort assignments as the global-resource rule.
- Reorganized cohort pages with lessons first and resources last.
- Grouped resources into Class links, At a glance, and Information.
- Removed obsolete Quick Sheet and Discord fields from Cohorts.
- Removed the public Lock classroom link.
- Added calm classroom and resource-page typography.
- Removed WordPress's “Protected:” prefix from plugin output.
- Migrated built-in WordPress cohort passwords into YOWM's permanent cohort-password system.
- Migrated existing Resources to Page type.


## 0.2.4

- Fixed `/2027/` showing the cohort chooser again.
- Prioritized virtual cohort, lesson, and resource routes over WordPress's front-page state.


## 0.2.3

- Added direct request-path routing for cohort, lesson, and resource URLs.
- Removed dependence on rewrite behavior for `/2027/`.
- Simplified cohort cards to image plus year.
- Added featured-image output to cohort cards.
- Added a prominent saved Module selector to Lesson details.


## 0.2.2

- Removed the classroom homepage hero.
- Fixed virtual cohort routes returning the plugin 404 template.
- Added automatic rewrite flushing on plugin upgrades.
- Kept the YOWM Studio admin menu open on Studio content screens.
- Converted Modules to a hierarchical native WordPress checkbox taxonomy.
- Removed the conflicting custom module selector.
- Added cohort-year metadata repair during upgrades.
- Made the published-cohort query more explicit and reliable.


## 0.2.1

- Fixed published cohorts missing from the classroom homepage when legacy year metadata was absent.
- Added year fallback from cohort title and slug.
- Replaced the lesson module dropdown with radio-button choices.
- Explicitly reattached the Module taxonomy to Lessons.
- Added a permanent password per cohort.
- Removed the global classroom password and password-expiration setting.
- Invalidated old cohort access automatically when a cohort password changes.


## 0.2.0

- Added automatic classroom homepage with cohort cards.
- Added shared classroom password and secure access cookie.
- Added automatic cohort pages at `/2027/`.
- Added lesson pages at `/2027/lesson-slug/`.
- Added protected resource pages.
- Added cohort welcome, announcements, quick links, recent lessons, and module archives.
- Added Sunday post audio, Saturday YouTube, and Saturday audio output.
- Added previous/classroom/next lesson navigation.
- Added resource-to-cohort assignments.
- Added classroom 404 with classroom-home and public-signup links.
- Added editable classroom-home and signup settings.

## 0.1.0

- Initial content-model release.
