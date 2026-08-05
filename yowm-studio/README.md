# YOWM Studio 0.13.0

This update adds the front-facing classroom to the existing YOWM Studio plugin.

## Install the update

1. Open **Plugins → Add New Plugin → Upload Plugin**.
2. Upload `yowm-studio-0.2.0.zip`.
3. WordPress should offer to replace version 0.1.0.
4. Confirm the replacement.
5. Open **Settings → Permalinks** and click **Save Changes** once.

Your existing cohort and Quick Sheet are preserved.

## Set the classroom password

Open:

**YOWM Studio → Settings**

Enter:

- one shared classroom password;
- how many days a browser should remember it;
- the classroom homepage title and introduction;
- the public signup-page URL.

The password is stored as a WordPress password hash, not as readable text.

## Public structure

```text
/
    Classroom home with cohort cards

/2027/
    Protected 2027 cohort home

/2027/lesson-slug/
    Protected lesson

/2027/resources/resource-slug/
    Protected resource
```

## Cohort home

A published cohort page automatically displays:

- its welcome text;
- its current announcement;
- Quick Sheet and Discord links;
- assigned or global resources;
- the six most recently published lessons;
- all lessons grouped by module.

## Resources

Edit a Resource and select the cohort or cohorts that should see it.

When no cohorts are selected, the resource appears in every cohort.

Existing resources from version 0.1 therefore remain visible automatically.

## 404 page

The plugin 404 page offers:

- Classroom home
- Learn about the class

The public class URL is editable under **YOWM Studio → Settings**.

## Lesson page

A lesson page automatically shows:

- formatted module/number/title;
- Sunday written post;
- Sunday post audio;
- Saturday unlisted YouTube video;
- Saturday audio;
- previous lesson;
- classroom home;
- next lesson.

## Important

After installing this update, open **Settings → Permalinks** and click **Save Changes**. This activates the `/2027/` URL structure.


## Version 0.2.1 fixes

- Published cohorts appear on the classroom home even when an older cohort record is missing its saved year metadata.
- The plugin can derive a year such as 2027 from the cohort title or slug.
- Lesson modules are now presented as clear radio-button choices.
- Each cohort has its own permanent shared password.
- Access remains until the browser cookie is cleared or the cohort password changes.
- There is no password-expiration setting.
- Changing a cohort password immediately invalidates access granted with the old password.

Set the password while editing the cohort under **Cohort details → Cohort access**.


## Version 0.2.2 fixes

- Removed the introductory hero from the classroom homepage.
- The homepage now starts directly with cohort cards.
- Fixed valid `/2027/` routes being mistaken for 404 pages.
- Rewrite rules flush automatically when this plugin version is installed.
- Kept the YOWM Studio admin menu expanded on Cohort, Module, Lesson, and Resource screens.
- Changed Modules to WordPress's native hierarchical checkbox panel.
- Existing modules now appear as selectable checkboxes in the Lesson editor.
- Added a migration that restores a missing cohort Year from its title.

### Assigning a module

Edit a Lesson and look in the right editor sidebar for **Modules**. Select one existing module and click **Update**.

If the sidebar is hidden, click the Settings icon in the upper-right corner of the editor.


## Version 0.2.3

- Cohort URLs are detected directly from the browser request, so `/2027/` does not depend on Hostinger rewrite behavior.
- No physical `2027` folder or separate WordPress page is required. The published Cohort generates the classroom page.
- Cohort cards contain only the featured image and year.
- The lesson editor includes a prominent Module selector inside Lesson details.

### Add the 2027 bee

Edit the 2027 Cohort, choose **Featured image** in the editor sidebar, upload the 2027 bee, and update the Cohort.


## Version 0.2.4 fix

WordPress was still classifying `/2027/` as the site front page. The plugin therefore selected the cohort chooser template again even though it had correctly detected the 2027 route.

This release gives cohort, lesson, and resource routes priority over WordPress's front-page flag.


## Version 0.3.0: Resources replace the Quick Sheet

Resources now have three display types:

### Link

Use for:

- Discord
- Zoom
- Google Calendar
- YouTube playlist
- How Story Works download
- Dropbox or other external destinations

The cohort card goes directly to the URL.

### Information card

Use for short information that should be visible without opening another page:

- instructor and coordinator contact details
- usual meeting time
- short reminders
- recording guidance

An optional URL may be added. For email links, enter a `mailto:` URL.

### Page

Use the normal WordPress editor for longer material:

- full schedule
- How the Class Works
- workshop mission
- confidentiality and intellectual property
- detailed instructions

The resource card opens a calm internal reading page.

### Global resources

Leave every cohort checkbox empty to make the resource available to every cohort.

Select one or more cohorts only when the URL or information changes by year.

## Other 0.3.0 changes

- Lessons now appear before Resources.
- Resources appear at the bottom and are grouped as Class links, At a glance, and Information.
- Removed the Lock classroom link.
- Removed “Protected” from public titles.
- Reduced classroom and resource page title sizes.
- Migrates a WordPress-built-in cohort password into the permanent YOWM cohort password automatically.
- Existing Resources become Page resources during the update.


## Version 0.3.1 fixes

- Studio Cohorts, Lessons, and Resources now use a straightforward classic editing screen so every YOWM field is visible.
- Fixed the password form redirect loop.
- Added an unmistakable Cohort password box with current-password status.
- Passwords entered through WordPress Quick Edit are also accepted by YOWM Studio.
- Lesson Cohort assignment is visible in Lesson details.
- Resource type and cohort assignment are visible in Resource details.
- Resources now offer an explicit All Cohorts or Specific Cohorts choice.

### Lesson assignment

Edit the Lesson and use:

- Cohort
- Number
- Module

inside **Lesson details**.

### Resource assignment

Edit the Resource and use:

- Link, Information card, or Page
- All Cohorts or Specific Cohorts

inside **Resource details**.


## Version 0.3.2

- Removed the duplicate Module selector from the WordPress sidebar.
- The Module selector remains in Lesson details at the bottom of the editor.
- Link resources now appear as buttons rather than cards.
- Removed Link, Information, and Read labels from resource displays.
- Added Display order to every Resource. Lower numbers appear first.
- Added file-download behavior for Link resources.
- Relative file paths such as `/files/package.zip` are supported.
- A Link resource without a saved URL is no longer rendered as an empty link that refreshes the page.

## Later public-site task

Add a dedicated How Story Works download page to `lanidianerich.com` using the existing writer and student download cards from the public 404 page.


## Version 0.3.3

- Restored the Gutenberg block editor for Cohorts, Lessons, and Resources.
- YOWM options still appear below the editor.
- Information Cards now render the formatting from the main Gutenberg editor.
- Existing plain-text Information Card content remains visible as a fallback.
- Rebuilt the Lesson title area so it uses the calm classroom heading scale.
- Lesson titles now sit above the outline/content columns instead of being squeezed into the sidebar column.

### Formatting an Information Card

1. Edit the Resource.
2. Select **Information card** under Resource details.
3. Format the content in the main Gutenberg editor.
4. Use the optional URL only when the card should also link somewhere.


## Version 0.3.4

- Fixed long Resource pages collapsing into the narrow outline column.
- Resource content now remains in the full reading column.
- When a Resource does not contain enough H2/H3 headings for an outline, the empty outline column is removed automatically.


## Version 0.3.5: Resource library tools

### Duplicate a Resource

You can duplicate a Resource in two places:

- **YOWM Studio → Resources → Duplicate**
- the normal WordPress Resource list, using the **Duplicate** row action

The duplicate opens immediately as a Draft and copies:

- Gutenberg content
- excerpt
- Resource type
- URL
- information-card settings
- download/new-tab settings
- cohort assignments
- display order
- featured image

The copy is titled `Original Title — Copy`.

### Organized Resource screen

**YOWM Studio → Resources** is now an organized library rather than one increasingly long flat list.

Resources are placed inside collapsible cohort sections:

- All Cohorts
- 2027
- 2028
- future cohorts

A Resource assigned to multiple cohorts appears in each relevant section. This is only an organizational view; it does not create duplicate content.

The screen shows:

- display order
- title
- Resource type
- publication status
- Edit and Duplicate actions

A **Standard WordPress list** button remains available when bulk actions or Quick Edit are useful.


## Version 0.3.6

- Explicitly forces Gutenberg for Cohorts, Lessons, and Resources.
- Prevents WordPress, the theme, or another editor preference from sending Studio content back to the Classic Editor.
- Keeps the cohort-grouped Resource library and Resource duplication tools from 0.3.5.

After installing, open **YOWM Studio → Resources** and click **Edit** on a Resource. The Gutenberg block editor should open, with YOWM Resource details beneath it.


## Version 0.4.0: Library and reusable lessons

### Library

The public and admin-facing name is now **Library**. The underlying WordPress content type remains unchanged so existing content and URLs are preserved.

### Reusable lessons

A Lesson can now apply to:

- All cohorts
- Specific cohorts

The written Gutenberg lesson and its post audio are shared. You no longer need to duplicate an unchanged lesson for every year.

### Cohort-specific live-session recordings

Each Lesson has a **Live-session recordings by cohort** area.

For each cohort, you can enter:

- YouTube video URL
- audio-only URL

The 2027 classroom shows the 2027 recording. The 2028 classroom shows the 2028 recording, while both use the same written lesson.

Existing lessons and recording links are migrated automatically.

### Lesson display

The automatic **Sunday post** heading has been removed.

### Library page outline

Every full Library page now keeps the left-side outline area visible. It always includes a **Library home** link, and Gutenberg H2/H3 headings are added beneath it automatically.


## Version 0.5.0: Classroom calendar

### Per-cohort lesson releases

Each Lesson now has a Release date and time inside each cohort's recording section.

- Leave it blank for immediate access.
- Set a future date to hide the Lesson from that cohort until the release time.
- The same Lesson can already be available to 2026 while remaining scheduled for 2027.

Administrators can still preview scheduled Lessons while logged into WordPress.

### Cohort quick view

The cohort home now shows:

- **Now** — the most recently released Lesson
- **Coming up** — title and release date only
- **Quick links**
- **Recent lessons**
- a link to the complete **Past lessons** archive
- a preview of the **Library**

### Archives

Each cohort now has:

```text
/2027/lessons/
/2027/library/
```

Only released Lessons appear in the Lesson archive.

### YouTube

YouTube URLs are normalized automatically. Supported forms include:

- `youtube.com/watch?v=...`
- `youtu.be/...`
- `youtube.com/live/...`
- `youtube.com/shorts/...`
- existing embed URLs

The plugin uses YouTube's privacy-enhanced embed domain and falls back to a normal link if a URL cannot be recognized.


## Version 0.5.1

### YouTube and Gutenberg embeds

The Lesson template no longer runs Gutenberg content through `wp_kses_post()` after WordPress renders it. That sanitizer was removing iframe-based YouTube blocks, which is why videos embedded directly inside the written Lesson disappeared completely.

This restores:

- Gutenberg YouTube blocks in Lesson content
- other trusted WordPress embed blocks
- cohort-specific Saturday-session video embeds

A direct **Open this video on YouTube** link appears below cohort-specific videos as a fallback.

### On deck

The cohort quick view now uses **On deck**.

The next scheduled Lesson appears directly beneath the current Lesson with:

- title
- excerpt
- `Coming [release date]`

The upcoming card remains informational and does not link to the unreleased Lesson.


## Version 0.6.0: Private cohort podcast feeds

### Enable a feed

Edit a Cohort and open **Private podcast feed** beneath Cohort details.

1. Check **Enable this cohort podcast**.
2. Give the podcast a title and description.
3. Update the Cohort.
4. Copy the private RSS feed URL.

Students paste that secret URL into a podcast app that supports adding an RSS feed by URL.

The feed is private by obscurity: it is not listed publicly and the address contains a long secret token. It is one shared token per cohort, not a separate identity-based subscription for each student. Regenerating the token immediately revokes the old feed URL.

### Add audio from a Lesson

Each cohort section in **Lesson recordings** now includes:

- Lesson release date and time
- Lecture audio URL
- Upload or choose audio
- Lecture podcast release
- YouTube video URL
- Live-session audio URL
- Upload or choose audio
- Live-session podcast release

The upload button opens the normal WordPress Media Library and fills the Lesson field automatically.

### Release behavior

**Lecture audio**

- Set a podcast release date for an independent schedule.
- Leave it blank to use the Lesson's cohort release date.
- If neither date exists, it is available immediately.

**Live-session audio**

- Set a future podcast release when needed.
- Leave it blank for an immediate drop when the audio is first saved.

The RSS feed is generated dynamically, so no WordPress cron event is required. A podcast app sees an episode after its release time when it refreshes the feed.

### Website synchronization

The same cohort-specific Lecture audio and Live-session audio URLs populate the audio players on the Lesson page. There is no second upload workflow.

### Audio files

WordPress-hosted MP3 or M4A files provide the most reliable enclosure metadata. External audio URLs are accepted, but some podcast apps may be stricter when the remote host does not expose file size or MIME information.


## Version 0.6.1: Feed routing fix

The original `/podcast/2026/secret-token/` route could be intercepted as a 404 by the server before WordPress reliably rendered the RSS.

Private feed URLs now use a WordPress-safe query URL:

```text
https://example.com/?yowm_podcast=1&yowm_podcast_year=2026&yowm_podcast_token=SECRET
```

Podcast apps accept RSS URLs with query parameters. This route reaches WordPress through the normal site homepage and is rendered before theme or classroom routing begins.

The Cohort editor now also shows:

- whether the podcast is enabled;
- a **Test feed in a new tab** button;
- the current copyable feed URL.

The old pretty URL remains recognized as a fallback, but the new query URL should be used in podcast apps.


## Version 0.6.2: Podcast presentation

### Clean episode titles

Podcast episode titles now use the Lesson's actual WordPress title rather than the classroom display title.

For example:

```text
Why Mindset Matters
Why Mindset Matters — Live Session
```

The Module and Lesson number still appear normally inside the classroom.

### Podcast artwork

Each Cohort now has its own Podcast artwork selector.

1. Edit the Cohort.
2. Open Private podcast feed.
3. Click Choose podcast artwork.
4. Select or upload the square artwork.
5. Update the Cohort.

The chosen image is added to both the channel and episode metadata. Podcast apps control when they refresh cached artwork, so an existing subscription may not change instantly.

### Podcast author

Each Cohort also has an editable Podcast author field, defaulting to `Lani Diane Rich`.


## Version 0.7.0: Reusable lectures

A Lesson now has one reusable Lecture audio file and one transcript.

Select the current cohorts whose private podcast should receive that Lecture, then assign an independent release date to each selected cohort. A blank date means immediate publication after saving.

**Select all current cohorts** stores the IDs of the cohorts that exist now. A cohort created later is not automatically added, so a new cohort cannot accidentally receive the full back catalog.

Live-session video and audio remain cohort-specific. Live-session audio still drops immediately when first saved unless a future date is entered.

The transcript appears in a collapsible **Lecture transcript** section on the protected Lesson page.

No fixed 365-day schedule shifting is used. Future schedule automation should use actual calendar dates, which will account for leap years correctly.


## Version 0.7.1

Every protected cohort page now has a persistent navigation bar with:

- Classroom home
- Lessons
- Library
- Change cohort

The `/YEAR/library/` and `/YEAR/lessons/` routes are also recognized directly by the plugin. This fixes the Library link opening a blank Lessons page.


## Version 0.8.0

### Lecture versions

A Lesson may now contain multiple named Lecture versions. Each version has its own audio, transcript, and Archive checkbox. Each Cohort selects one version, so returning 2026 students can keep their original recording while 2027 and later cohorts use a revised recording.

Archived versions remain active for Cohorts assigned to them. New Cohorts begin with **No lecture assigned**.

### Release time

Lecture and written-Lesson date fields publish at **6:00 AM America/Denver**. Blank dates remain immediate.

### Navigation

The cohort navigation is inserted after the Cohort year is known. The theme's broken mobile menu is hidden on cohort pages.

### Copy podcast feed

Library items have a **Copy podcast feed** type that copies the current Cohort's private RSS URL and briefly says **Copied!**

### Pocket Casts metadata

The feed now includes Last-Modified, ETag, no-cache headers, TTL, private-feed metadata, and a build date based on the newest episode.


## Version 0.9.0: Podcast media reliability

### Automatic podcast MP3 conversion

When an administrator chooses an audio file for a Lecture version or Live Session:

- MP3 files are used immediately.
- M4A, AAC, WAV, OGG, and other audio files are sent to the server for MP3 conversion.
- The original upload remains untouched.
- The generated MP3 is added to the WordPress Media Library and placed into the Lesson field automatically.
- Selecting the same original file again reuses its existing converted MP3.

The server must have FFmpeg installed and PHP must be allowed to execute it. When those capabilities are unavailable, YOWM Studio shows an explicit warning and leaves the original URL in the field so work is not lost.

Conversion uses high-quality variable-bitrate MP3 encoding and preserves available source metadata.

### Permanent podcast episode GUIDs

Every Lecture version receives a permanent UUID. Every cohort-specific Live Session also receives a permanent UUID.

The feed GUID no longer depends on:

- the audio URL;
- the Lesson title;
- the episode description;
- artwork;
- release-date changes.

Replacing an audio file or editing episode metadata therefore does not make a podcast app mistake it for a new episode.

Lecture GUIDs remain tied to the Lecture version. Live-session GUIDs remain tied to that cohort's session recording.


## Version 0.9.1: MP3-only podcast workflow

YOWM Studio no longer attempts server-side audio conversion.

The intended workflow is now:

```text
recording file
→ convert to MP3 on the computer
→ store the MP3 in Dropbox
→ upload or choose the MP3 in WordPress
```

When an administrator selects audio:

- MP3 files show **Podcast-ready MP3 selected**.
- M4A, AAC, WAV, OGG, and other formats show a clear warning.
- The selected URL remains in the field so no work is lost.
- No FFmpeg or Hostinger server capability is required.

Permanent episode GUIDs from 0.9.0 remain in place.


## Version 0.10.0: cohort-safe reusable Lessons

### Blank assignment means no Lesson

When a Lesson has reusable Lecture versions, it now appears in a cohort only when that cohort has an explicit valid Lecture-version assignment.

A blank 2027 assignment no longer inherits or displays the 2026 Lesson. Older Lessons with no Lecture-version records continue to use the normal All Cohorts / Specific Cohorts scope.

### One Lecture release

The Cohort Lecture Assignment section now has one **Lecture release date**. It releases both:

- the written Lesson;
- the Lecture episode in the private podcast feed.

The release is always 6:00 AM in `America/Denver`. Existing separate podcast dates remain readable for backward compatibility and are mirrored when the Lesson is next saved.

### Immediate version assignment

New Lecture versions are inserted into every Cohort Assignment dropdown immediately. Their dropdown labels update while the version name is typed, and Current/Archived state updates without saving and reloading.

### Transcript access

Student Lesson pages now label the transcript control **Read or search the lecture transcript**. Transcripts remain attached to the correct Lecture version.


## Version 0.11.0: reliable cohort authentication

### Independent cohort access

YOWM Studio now treats its own cohort password as the only classroom password system. Every cohort has a separate signed browser cookie, so unlocking 2027 does not lock 2026.

Native WordPress post passwords are migrated when necessary and then removed. Do not use the Password field in WordPress Quick Edit for Cohorts; use the Cohort password panel instead.

### Cache-safe login

Protected classroom routes and password actions send:

- `Cache-Control: no-store, private`;
- `Pragma: no-cache`;
- `Vary: Cookie`;
- a `DONOTCACHEPAGE` signal for compatible cache plugins.

The public password form no longer relies on a WordPress nonce that can become stale inside a cached page. Failed attempts are rate-limited by cohort and IP address.

After successful login, Studio adds a unique redirect value so an old cached gate page cannot immediately reappear.

### Authentication diagnostics

Open **YOWM Studio → Authentication** to see:

- whether every cohort has a YOWM password;
- whether a native WordPress password remains;
- whether the current browser is unlocked for each cohort;
- the classroom URL;
- recommended Hostinger/LiteSpeed cache exclusions.

No password text or password hash is displayed.

### Class Info

Student-facing uses of **Library** are now labeled **Class Info**. Existing `/library/` URLs remain unchanged so bookmarks and links keep working.

### YouTube compatibility

The Lesson template still uses cohort-specific session-video metadata. Version 0.11.0 also recovers older generic YouTube metadata when the Lesson is explicitly scoped to one cohort, without exposing one cohort's recording to another.


## Version 0.11.1

- Fixed the missing **Authentication** submenu. It now appears directly under **YOWM Studio** for administrators.
- Renamed the WordPress backend **Library** section to **Class Info**.
- Renamed backend post labels to **Class Info Item / Class Info**.
- Preserved the existing internal post type and `/library/` URLs so no content, links, or bookmarks break.


## Version 0.12.0: invite-only individual accounts

Open a Cohort and use the **Students** panel. Paste one student per line:

```text
Student Name, student@example.com
Student Name <student@example.com>
student@example.com
```

Studio creates missing WordPress accounts, reuses existing accounts for returning students, assigns cohort membership, generates a personal podcast URL, and emails a one-time password setup link.

There is no public student registration. Students sign in with their email address and the password they choose.

The roster supports:

- Resend invite
- Revoke access
- Restore access
- Generate a new personal podcast URL

Revoking a student blocks that cohort immediately and invalidates only that student's podcast feed.


## Version 0.12.1 hotfix

- Removed the early personal-podcast interception that could cause a front-end critical error.
- Moved roster management out of the Cohort editor into **YOWM Studio → Student Access**.
- Replaced the bulk textarea with ten clear Name and Email fields.
- Fixed the account-creation form so it submits as a normal standalone WordPress admin form.
- Added a cohort selector and visible success counts for account creation and invitation delivery.

Personal feed URLs are still created and shown in the roster. The older working cohort podcast feed remains the live feed while the personal-feed delivery route is stabilized.


## Version 0.12.2 hotfix

- Removed the classroom page's runtime dependency on the Student Access helper class.
- Simplified the login gate's redirect calculation.
- Prevented YOWM Student accounts from entering the WordPress backend.
- Hid the WordPress admin bar for students.
- Redirected student logins and post-password-setup sessions to their assigned classroom.


## Version 0.12.3 diagnostic

This build adds **YOWM Studio → System Diagnostics**.

After installing:

1. Open `/2026/` and reproduce the critical error.
2. Return to **YOWM Studio → System Diagnostics**.
3. Copy the Message, File, and Line fields.

The diagnostic stores only the most recent fatal PHP error. It does not display passwords, cookies, student data, or page content.


## Version 0.12.4 hotfix

- Fixed the `/2026/` critical error caused by a call to the removed `current_cohort()` method.
- Cohort access now resolves the cohort from the current route year.
- Retained the System Diagnostics screen for future troubleshooting.


## Version 0.12.5

- Added **Welcome, {first name}** to the signed-in classroom navigation.
- Falls back to the WordPress display name when no first name is saved.
- Fixed the Student Access cohort selector using the wrong WordPress admin URL.
- Fixed roster redirects and the Cohort-editor roster button.
- Confirmed the student-facing navigation label is **Class Info**.


## Version 0.13.0: unified students and student-chosen usernames

- **YOWM Studio → Students** is one roster across all cohort years.
- New entries include first name, last name, email, and one or more cohort years.
- New students receive a private setup link and choose their own username and password before WordPress creates the account.
- Returning students are recognized by email and receive additional cohort memberships on the same account.
- The roster shows username, names, email, cohort years, status, and each personal podcast feed.
- Administrators can edit names and cohort memberships, suspend or restore access, rotate individual podcast URLs, delete pending invitations, and permanently delete YOWM Student accounts.
- Administrator/editor accounts cannot be deleted through the student roster.
