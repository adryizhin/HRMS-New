# Running this installation

## Staff records, attendance, leave and reports

All role pages use shared responsive layouts. At widths of 1024 pixels or less, the sidebar becomes a hamburger menu with an X close button. Clicking the backdrop, pressing Escape or choosing a navigation link closes it. The menu traps keyboard focus while open and restores focus when closed. Tables scroll within their sections; forms, cards and charts stack on narrow screens. Browser checks cover 320, 390, 768, 1024 and 1440 pixel widths.

HR's 201 Files and KPI list include employees, admins and HR. Admins and HR can upload their own documents and submit assigned evaluations through My Documents and My Evaluations. Admins also have My KPI. HR can assign evaluations to all active staff.

Attendance supports searches by name, employee ID or role. HR and admins can search all staff; employees see only their own attendance. Summary cards remain totals for the authorized scope, rather than the filtered search.

Reports are available to all three roles through the sidebar. Select monthly, quarterly or yearly, a year, and a month or quarter. Admins and HR can select an individual or all staff; employees receive personal reports. Accomplishments count submissions made during the period, including tasks assigned earlier. KPI attendance is filtered by attendance date and KPI tasks by assignment date, using their latest evaluation results. Historical reports are not frozen score snapshots. Charts, CSV summaries and browser printing / Save PDF are available.

Leave uses the existing 15-day allowance as an annual working-day allowance shared by Sick Leave and Vacation Leave. Successful submissions reserve days immediately; approval keeps that deduction, and rejection restores it. Weekends and configured holidays are excluded. Cross-year requests charge each year's allowance. Overlapping requests, insufficient balances, invalid dates and duplicate submissions do not deduct additional days. Existing leave requests are initialized once from their working dates when the leave pages are opened. Database additions are automatic and preserve existing rows.

Philippine nationwide non-working holidays are automatically imported once per configured year. The included data covers 2026 (including declared Eid dates) and 2027 (Proclamation 1427; Eid dates still await separate proclamations). Sources are linked in `config/philippine_holidays.php`. Edits and deletions are preserved. Add local holidays or newly announced Eid dates in the Holiday Calendar. Add future official yearly lists to the configuration before their first import. Holidays are shared with attendance absence marking, payroll workdays and leave calculations. Previously generated absences on imported holiday dates are removed.

Run `php tests/features.php` for isolated feature checks. It clones only the database schema, creates synthetic accounts, runs checks against a loopback test server, and drops its disposable database. It requires database CREATE/DROP permission and port 8091. Set `HRMS_FEATURE_BROWSER=1` with Playwright available on `NODE_PATH` and Microsoft Edge installed to verify report charts, themes and mobile layout. Browser screenshots are saved under `tests/features-report-*.png`.

Start Apache and MySQL in XAMPP, then open http://localhost/HRMS/ . Existing account passwords are unchanged.

This installation uses its own PHP 8.4.26 CGI runtime in `.runtime/php`. The root `.htaccess` selects it for HRMS only; XAMPP's PHP installation is unchanged. The configuration contains this machine's absolute project path. If moving the project, update `.htaccess` and `.runtime/php/php.ini`, or use a web server already running PHP 8.4 and remove the custom `PHPRC`, `Action`, and PHP handler directives. The bundled CGI endpoint is restricted to local requests.

PHP needs mysqli, mbstring, curl, openssl, fileinfo, and zip. Database defaults are localhost, root, no password, database `hris`. Override them using `HRMS_DB_HOST`, `HRMS_DB_USER`, `HRMS_DB_PASS`, and `HRMS_DB_NAME`. Set `HRMS_BASE_URL` when the public URL differs from the folder under the web root.

## Database

The existing database has already been repaired. Do not import the full SQL file over it.

For a fresh installation, import `hris-no-comments.sql`; it creates/selects `hris` and includes the exported data. Email addresses use a 191-character column so the unique email index also imports on the installed MySQL 5.6 server. The fresh import was verified to contain 15 tables and 14 automatically incrementing ID columns.

For an interrupted import with existing data, back up the database first, then run:

```powershell
.\.runtime\php\php.exe -c .\.runtime\php\php.ini .\scripts\repair-database.php
```

The repair restores missing indexes, automatic IDs, and foreign keys without replacing rows. It refuses to shorten email values longer than 191 characters. A pre-repair backup for this installation is stored in `.runtime/hris-before-checks.sql`.

## Verification

All application PHP files passed syntax checks. Thirty HTTP checks passed against an isolated database copy, covering login/logout for all roles, main pages, evaluation creation, review, and score saving. Browser checks passed for all three dashboards and theme switching. The isolated test databases and server were removed after testing.

The scripts in `tests` use Playwright. Runtime tests require a disposable database, the protected `.runtime/prepare-test.php` fixture, and the restricted `.runtime/test-router.php` server on loopback port 8088; they must not target the real database. Results are recorded in `tests/runtime-results.json` and `tests/browser-results.json`.

External AI scoring was not called during testing. PDF extraction now uses the installed PDF parser instead of requiring an external `pdftotext` command. AI errors no longer save artificial scores or mark evaluations reviewed. `GEMINI_API_KEY` and `GEMINI_MODEL` override the local protected AI settings; the model default is `gemini-3.8-flash`. Existing cached scores were not changed.

Runtime source: https://www.php.net/downloads.php?os=windows&version=8.4 . Model availability reference: https://ai.google.dev/gemini-api/docs/deprecations/ .

## Forgot password tickets

The login page includes **Forgot password?**, which opens a request form for the account email, optional employee ID and optional message. Matching active accounts receive a pending ticket; repeated requests keep the existing open ticket. Responses do not disclose whether an email exists. Requests require a session token and are limited to five attempts per source IP in 15 minutes, across browser sessions.

Admins open **Password Reset Tickets** in the sidebar to filter open, pending, in-progress, resolved and rejected tickets. They can save notes, mark a request in progress or reject it with a reason. To resolve a ticket, verify the requester's identity independently, check the verification box and enter matching new passwords (12–72 bytes). Provide the password directly to the verified requester through your approved communication channel. Passwords are stored as hashes; resetting signs out the account's existing sessions. Closed tickets cannot reset passwords again. Ticket history records the handling admin, notes and timestamps. The page shows the latest 100 tickets for the selected filter.

The two password ticket tables are created automatically on first submission or when admins open the ticket page. No SQL import is needed. The database account needs CREATE TABLE permission. Run `php tests/password_tickets.php` for 35 isolated workflow and security checks; it creates a disposable database with synthetic users and removes it afterwards. It requires the configured database service, CREATE/DROP permission and loopback port 8091.

## Online users and session activity

Online Users now requires a valid, unexpired session from an active account and a heartbeat within the past 90 seconds. Users with multiple browser sessions appear once. The dashboard refreshes its list every 30 seconds; a closed or disconnected browser normally disappears within two minutes. Explicit logout removes that session immediately and the list reflects it on its next refresh.

Presence signals run every 30 seconds while the browser can run timers. They do not renew the existing 15-minute inactivity timeout. Actual interaction or authenticated page navigation renews activity. Expired sessions are marked timed out on the server and cannot be revived by a heartbeat. Login Logs and CSV exports distinguish offline open sessions from active online users.

The nullable `login_logs.last_seen` column is added automatically when needed; existing logs remain offline until their actual session contacts the server. The database user needs ALTER permission for this one-time migration. No SQL import is required. Run `php tests/online_users.php` for 24 isolated checks against a disposable schema and synthetic accounts; the test uses loopback port 8091 and requires database CREATE/DROP permission.
