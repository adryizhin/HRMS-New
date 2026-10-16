# Running this installation

## Staff records, attendance, leave and reports

All role pages use shared responsive layouts. At widths of 1024 pixels or less, the sidebar becomes a hamburger menu with an X close button. Clicking the backdrop, pressing Escape or choosing a navigation link closes it. The menu traps keyboard focus while open and restores focus when closed. Tables scroll within their sections; forms, cards and charts stack on narrow screens. Browser checks cover 320, 390, 768, 1024 and 1440 pixel widths.

HR's 201 Files and KPI list include employees, admins and HR. Admins and HR can upload their own documents and submit assigned evaluations through My Documents and My Evaluations. Admins also have My KPI. HR can assign evaluations to active employees and admins. Only the System Administrator assigns evaluation work to Human Resource staff.

Attendance supports searches by name, employee ID or role. HR and admins can search all staff; employees see only their own attendance. Summary cards remain totals for the authorized scope, rather than the filtered search.

Reports are available to all three roles through the sidebar. Select monthly, quarterly or yearly, a year, and a month or quarter. Admins and HR can select an individual or all staff; employees receive personal reports. Accomplishments count submissions made during the period, including tasks assigned earlier. KPI attendance is filtered by attendance date and KPI tasks by assignment date, using their latest evaluation results. Historical reports are not frozen score snapshots. Charts, CSV summaries and browser printing / Save PDF are available.

Leave uses the existing 15-day allowance as an annual working-day allowance shared by Sick Leave and Vacation Leave. Successful submissions reserve days immediately; approval keeps that deduction, and rejection restores it. Weekends and configured holidays are excluded. Cross-year requests charge each year's allowance. Overlapping requests, insufficient balances, invalid dates and duplicate submissions do not deduct additional days. Existing leave requests are initialized once from their working dates when the leave pages are opened. Database additions are automatic and preserve existing rows.

Philippine nationwide non-working holidays are automatically imported once per configured year. The included data covers 2026 (including declared Eid dates) and 2027 (Proclamation 1427; Eid dates still await separate proclamations). Sources are linked in `config/philippine_holidays.php`. Edits and deletions are preserved. Add local holidays or newly announced Eid dates in the Holiday Calendar. Add future official yearly lists to the configuration before their first import. Holidays are shared with attendance absence marking, payroll workdays and leave calculations. Previously generated absences on imported holiday dates are removed.

Run `php tests/features.php` for isolated feature checks. It clones only the database schema, creates synthetic accounts, runs checks against a loopback test server, and drops its disposable database. It requires database CREATE/DROP permission and port 8091. Set `HRMS_FEATURE_BROWSER=1` with Playwright available on `NODE_PATH` and Microsoft Edge installed to verify report charts, themes and mobile layout. Browser screenshots are saved under `tests/features-report-*.png`.

Start Apache and MySQL in XAMPP, then open http://localhost/HRMS/ . Existing account passwords are unchanged.

This checkout uses the PHP runtime provided by XAMPP; no machine-specific CGI runtime is required.

PHP needs mysqli, mbstring, curl, openssl, fileinfo, and zip. Database defaults are localhost, root, no password, database `hris`. Override them using `HRMS_DB_HOST`, `HRMS_DB_USER`, `HRMS_DB_PASS`, and `HRMS_DB_NAME`. Set `HRMS_BASE_URL` when the public URL differs from the folder under the web root.

## Database migration using hris.sql

The root `hris.sql` is a full snapshot of the current database, including accounts (with existing password hashes), payroll, attendance, evaluations, tickets, chat, and settings stored in the database. It includes 20 tables. It uses InnoDB and `utf8mb4_general_ci`, with 191-character indexed email fields. It contains no DROP TABLE, CREATE DATABASE, USE, DEFINER, table-lock, or MariaDB client sandbox commands. This avoids several common cross-version import errors; it does not guarantee every historical XAMPP version can run the application.

Use a XAMPP installation with **PHP 8.2 or newer** and the extensions listed above. The current export/import is verified on **MariaDB 10.4.32 with PHP 8.2.12**. The SQL uses syntax intended for MySQL 5.7+ / MariaDB 10.2+, but those other server versions have not been tested here. Older PHP packages cannot be made compatible merely by importing an SQL file. Reference: [MariaDB dump client compatibility](https://mariadb.org/mariadb-dump-file-compatibility-change/).

1. Copy the entire HRMS project to the new XAMPP `htdocs` folder, including `vendor/` and **`uploads/`**. Photos, message attachments, and documents are files; an SQL import cannot restore them. Do not copy old browser/PHP session files.
2. Start Apache and MySQL. In phpMyAdmin, create an **empty** database named `hris` with `utf8mb4_general_ci`, select it, and import `hris.sql`. Do not import over an existing installation; back up an existing database first and restore into a separate empty database.
3. Alternatively, from a Windows Command Prompt with the destination database already created, run:

   `C:\xampp\mysql\bin\mysql.exe -u root -p --default-character-set=utf8mb4 hris < C:\xampp\htdocs\HRMS\hris.sql`

   This redirection example is for **Command Prompt**, not PowerShell. Enter the destination database password when prompted; press Enter if it is blank. For phpMyAdmin size limits, use this command-line import.
4. Set `HRMS_DB_HOST`, `HRMS_DB_USER`, `HRMS_DB_PASS`, and `HRMS_DB_NAME` for the destination if its database credentials differ from the defaults. Configure `HRMS_BASE_URL` if the public folder name changes. Existing account passwords stay the same; users log in again on the destination.
5. Configure email and AI service settings separately on the destination. The SQL contains no Gmail app password or server database password. Do not distribute personal local credential files with the export. Leave ticket email inactive until the destination sender is configured, to avoid sending queued status updates unintentionally.
6. Open `http://localhost/HRMS/`, sign in, and check a profile photo, chat attachment, document, evaluation and payslip.

Refresh the export before each migration with MySQL running (pause application writes/schema changes during backup and verification):

`C:\xampp\php\php.exe scripts/export-database.php`

The exporter reads the configured source database using a consistent transaction and replaces `hris.sql` only after a successful dump. It does not alter source records. If XAMPP is installed elsewhere, use its PHP path; set `HRMS_DUMP_BIN` if its dump executable cannot be found automatically. Temporary database credentials are deleted after export. Keep the SQL private; the root Apache rule denies web access to the dump.

Verify a fresh export using a disposable database (requires CREATE/DROP privileges):

`C:\xampp\php\php.exe tests/database_export.php`

This imports into a randomly named test database, compares every row plus all table schemas/indexes/foreign keys/automatic ID counters, then deletes the test database. It never imports into or replaces the source database.

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

## HR and administrator workflow updates

Admin and Human Resource dashboards share the animated robot launcher, message window, sender initials, composer and unread counter. The red badge counts unseen messages from every sender, including while the window is closed. Opening an unread conversation marks only that conversation as seen; other conversations remain unread. Background polling does not mark messages as read or extend the inactivity timeout.

The existing System Administrator account (user ID 1) has Assign HR Evaluations and Review HR Evaluations sidebar pages. Other admins cannot access those pages, and Human Resource accounts cannot assign work to Human Resource recipients, even through a direct request. HR retains assignments to employees and admins. Set `HRMS_SYSTEM_ADMIN_ID` only when migrating this installation to a different privileged account. Assignment forms require a session token and active, authorized recipients. Human Resource recipients see their work in My Evaluations, with the full Human Resource role label.

HR leave review includes status filtering, responsive request cards, escaped request text and approval/rejection feedback. Existing ownership checks, allowance restoration and notification behavior are retained.

Unknown-email password-reset requests are retained for admin review with the automatic note “Email not found in the system.” Admins can save notes or reject these tickets; they cannot reset a password without a linked account. The ticket inbox displays a count of unmatched open requests. Public success responses remain neutral. The requested email and system note columns and nullable account reference migrate automatically when the ticket feature is accessed; existing tickets are preserved.

Run `php tests/todo_updates.php` for 26 isolated authorization and workflow checks. Set `HRMS_TODO_BROWSER=1` and make Playwright available on `NODE_PATH` to check actual admin/HR badges, messaging, both themes, responsive layouts and leave filtering. These checks use a disposable database and loopback port 8091, never production records.
## Account settings and chat attachments

Account Settings is available in every role's sidebar. Users can edit their name, phone number, email, profile photo and password. Email/password changes require the current password and sign out other sessions. Role, employee ID and work shift remain read-only. Photos accept validated JPG, PNG and WebP files up to 2 MB and 5,000 pixels per side; uploaded photos appear in chat contacts, headers and message avatars, with initials as the fallback.

HR/admin chat composers have an attachment button and a removable filename preview. Files can be sent with or without message text. Supported types are PDF, DOCX, XLSX, PPTX, TXT, CSV, JPG, PNG and WebP. Office containers and MIME types are validated. The effective upload limit is the smaller of 10 MB and PHP's configured upload/post limits. PHP's default limit on this installation is reflected by the chat picker. Only conversation participants can download attachments. Private chat files and profile photos have direct Apache access denied; they are served by authenticated PHP endpoints. Preserve the upload directories' `.htaccess` protection when moving this installation.

The profile fields and chat attachment columns migrate automatically without replacing existing records. Login has an accessible password visibility button. HR leave requests use Font Awesome again for navigation, summary cards and review actions.

Run `php tests/account_chat.php` for 25 isolated upload/account checks on loopback port 8093. Set `HRMS_ACCOUNT_BROWSER=1` with Playwright on `NODE_PATH` to verify attachment selection/removal/sending/downloads, settings in both themes at desktop/mobile widths and the login eye toggle. Test uploads and the disposable database are removed afterward.

Profile photo editing now includes a live circular crop preview, zoom, drag positioning, horizontal/vertical alignment and reset/cancel controls. Choose a new photo or use Adjust current photo, then Save changes to upload the selected crop as a 512 × 512 PNG. The smaller avatar preview updates immediately. Existing photo size/type validation remains in place, and the browser tests verify that the saved image matches the selected crop.

The Human Resource dashboard shows Messages on the left and Send Evaluation on the right. On smaller screens these panels stack. Sent evaluation history is collapsed by default, and submission results return to the dashboard.

All roles share a notification bell beside the role label in the upper-right page header and in the mobile header. Counts refresh every 15 seconds for open password-reset tickets (Admin), pending personal evaluations, submitted evaluations awaiting the assigned reviewer, pending leave requests assigned to HR, and unread personal updates. Completed reviews create personal evaluation updates; leave decisions use existing personal notifications. Marking displayed updates read does not dismiss unfinished work. The notification endpoint scopes records to the logged-in user, requires CSRF for read actions, and background polling does not renew idle sessions.

Admin and HR unread-message notifications appear only on the chatbot icon and are excluded from the header bell to avoid duplicate alerts.

The System Administrator uses a single **HR Evaluations** sidebar entry with **Assign Evaluations** and **Review Evaluations** tabs on one page. Existing assignment/review URLs redirect to this page, and review detail pages link back to its Review tab. Other administrators cannot access this workflow.

On **Create Employees**, admins can use each row’s **Edit** button to change its department or select **Unassigned**. The dialog starts with the saved department selected. Department updates require an authenticated admin and CSRF token, validate employee/department IDs, and change only the department field.

Password-reset requests have a separate **Active email for updates** field. Account email identifies the account; active email receives status-only messages. New tickets queue Open/Pending emails, with further emails on In progress, Resolved, or Rejected transitions. Saving notes without a status change and duplicate requests do not resend emails or replace the original contact address. Passwords and internal admin notes are never included.

Gmail sending has been enabled in the local sender configuration and an activation test was accepted by Gmail SMTP. Sender configuration is in ignored, HTTP-protected `config/mail.local.php`; a safe template is in `config/mail.example.php`. For another installation, enter the sender’s Gmail app password locally and set `enabled` to `true`. Gmail app passwords require 2-Step Verification and may be unavailable for some managed accounts: [Google app password guidance](https://support.google.com/accounts/answer/185833). The SMTP defaults are smtp.gmail.com, port 587, STARTTLS: [Google SMTP guidance](https://support.google.com/mail/answer/7104828).

Ticket updates commit independently of SMTP delivery. Administrators see pending/failed mail counts and can retry up to three messages per click after sender setup. SMTP acceptance is recorded as Sent; inbox delivery and spam filtering are controlled by the receiving provider. The dedicated `tests/ticket_email.php` uses a loopback SMTP receiver and a disposable database; it does not send real emails.

All Admin and Human Resource pages using the shared navigation include one robot messaging launcher, including account settings and evaluation views. The chat popup includes a searchable contact picker and a back button to switch conversations, so messaging is usable without visiting the dashboard. Profile avatars, unread counts, file attachments, and responsive layout use the same shared widget. Employee pages do not receive this launcher.
