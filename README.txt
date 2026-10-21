UNSENT · folder layout (Update 5: files split into folders, no feature change)

index.php             front controller. Every page is index.php?p=<name> (links did not change)
login.php             sign in                      ?p=signin
register.php          create account               ?p=register  (or ?p=signin&new=1)
forgot-password.php   recover password            ?p=recover
process_login.php     POST: login, register, verify code, new password
logout.php            POST: log out
404.php               unknown ?p= name

config/               config.php (DEMO, timezone, headers, session, CSRF), database.php (data.json read/write),
                      data.json (your data), migrate.php (data.json -> MySQL, run: php migrate.php from this folder)
includes/             functions.php, icons.php, header.php (<head>), sidebar.php, layout-page.php (page top bar, letter rows),
                      footer.php (scripts), bootstrap.php (data + current user), process.php (POST dispatcher)
user/                 home.php, write.php (also the editor), unsent.php, profile.php, process_user.php, process_letter.php
admin/                index.php (shell + shared numbers), overview.php, users.php, activity.php, audit.php, settings.php,
                      functions.php, process_admin.php
css/                  style.css, fx3d.css
js/                   core.js first, then one file per feature (order is set in includes/footer.php), fx3d.js (3D, module)
img/                  your images (logo, mood, postcards ...). Not in this zip, copy yours in.
uploads/              new profile photos + postcard fronts. Old files in img/uploads/ still work.

Notes
- config/, includes/, admin/, user/ have a .htaccess that blocks direct web access (data.json holds password hashes).
- uploads/ has a .htaccess that blocks scripts.
- Every included file starts with defined('ROOT') || exit; so opening it by URL shows nothing.
- Move: old root files app.js, style.css, fx3d.css, fx3d.js, migrate.php, data.json are replaced by the folders above. Delete the old ones.

----- Update 4 notes (unchanged) -----
Update 4: bug fixes + polish. Replace index.php, app.js, fx3d.css, fx3d.js. style.css, data.json, migrate.php unchanged.

SECURITY
- CSRF token on every POST form and every fetch (was admin-only).
- Password recovery: the demo code 72550 let anyone reset ANY account, admins included. Now: member accounts only, and only while DEMO is true.
- Stored XSS in letter bodies closed (cleanbody kept onclick=, style= etc. on <p>/<b>/<div>). Old letters are cleaned when opened.
- Session id regenerated on sign-in/sign-up, HttpOnly + SameSite cookie, login throttle (6 tries, 1 min), nosniff / frame headers.
- Prefilled demo email + password removed from the sign-in form. Top of index.php: const DEMO = true; set it to false before going live.

DATA BUGS
- Delete my account left the letters (and uploaded images) in data.json; a new account with the same email inherited them. Now removed.
- Emails are lowercased and compared case-insensitively (sign-in, owner of letters).
- data.json is written atomically with a lock; an unreadable file stops the app instead of being overwritten by the demo seed.
- Profile: username/name/email/phone validated, clear error toast instead of silently reverting.
- Mood: the per-mood hint/placeholder used $tm before it was set. Fixed. Undefined-index warnings removed.
- Dates use const TZ (Asia/Jakarta) so "today" and mood streaks match your clock.

EDITOR
- Colour swatches also ran the toolbar handler (stole focus, bogus execCommand). Fixed.
- Autosave: one save at a time (a fast second save made a duplicate letter), saves when the tab is hidden, warns before leaving mid-save.
- Paste is plain text; paragraphs are always <p>.
- New letters start blank with placeholders (no sample letter to delete). Stray </div> removed.

MOTION (matches the "calm" rule)
- app.js still carried the old tilt / ripple / drifting sheets / parallax / duplicate count-up layer. Removed. Only the back-to-top button stays.
- Card lift while the postcard turns was cancelled by a hover !important rule. Fixed.
- Icon draw leaves no gap at the path seam.

UI
- Greeting follows the time of day. Password strength meter (replaces the pop-up). Skip link, visible focus rings, 44px touch targets,
  "no letters match" note, OTP paste + numeric, 3D chart labelled for screen readers, balanced headings, quiet scrollbars.

Undo: put the old files back.
