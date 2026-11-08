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

The root `hris.sql` is a full snapshot of the current database, including accounts (with existing password hashes), payroll, attendance, evaluations, tickets, chat, and settings stored in the database. The November 8, 2026 snapshot includes 23 tables and 2,189 rows, including promotion suggestions, payroll profiles and appraisal storage. It uses InnoDB and `utf8mb4_general_ci`, with 191-character indexed email fields. It contains no DROP TABLE, CREATE DATABASE, USE, DEFINER, table-lock, or MariaDB client sandbox commands. This avoids several common cross-version import errors; it does not guarantee every historical XAMPP version can run the application.

Use a XAMPP installation with **PHP 8.2 or newer** and the extensions listed above. The current export/import is verified on **MariaDB 10.4.32 with PHP 8.2.12**. The SQL uses syntax intended for MySQL 5.7+ / MariaDB 10.2+, but those other server versions have not been tested here. Older PHP packages cannot be made compatible merely by importing an SQL file. Reference: [MariaDB dump client compatibility](https://mariadb.org/mariadb-dump-file-compatibility-change/).

The SQL contains database structure and records. Application functions, search controls, promotion permissions and page designs are implemented in the PHP, JavaScript and CSS files; migrate the updated project together with this snapshot. The included 10 Regular Employee suggestions are pending HR decisions.

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

## Leave request details and review

Employee Request Leave now shows the annual working-day balance and personal request history instead of unrelated dashboard totals. The form retains entered values after validation errors. Check working days previews the requested working days and available balance for each affected year without submitting or reserving leave. Submission validates dates, overlapping requests, active HR reviewer, allowance, request token, and a reason of at most 2,000 characters.

Human Resource review includes staff ID, department, email, requested working days, submission time, optional employee-visible notes, and the recorded decision reviewer/time. Reviews retain the chosen status filter. A completed decision cannot be overwritten or generate duplicate updates. Submission and review use the same employee-lock order to serialize balance changes. Pending and approved requests reserve days; rejection restores them. Personal history is restricted to the signed-in employee.

The review audit columns are added automatically by the shared leave schema helper. Existing decisions remain intact and are labeled legacy records when no review timestamp was recorded. Run `php tests/features.php` for 84 isolated feature checks; `HRMS_LEAVE_BROWSER=1` additionally exercises submission, review, and responsive layouts with Playwright.

## Performance scoring

Key Performance Indicators now use four measures: reviewed work quality 40%, task completion 25%, attendance reliability 20%, and on-time delivery 15%. The former separate overdue-task-health factor has been removed. Overdue unfinished work reduces completion and deadline delivery directly.

Attendance reliability awards full credit to present days, 75% credit to late days and zero to absent days. Quality averages only final review scores on submitted work marked Reviewed, clamped individually to 0–100. Completion is submitted work divided by tasks already due or submitted; future and undated pending tasks have no measurable outcome and are excluded. On-time delivery is on-time submitted work divided by deadline-tracked tasks already due or submitted. Missing timestamps earn no on-time credit.

All overall scores use a fixed 100-point scale: each measure contributes its score multiplied by its assigned weight; missing measures contribute zero points. Weights are never redistributed. Perfect attendance alone earns 20% overall. Perfect quality plus completion alone earns 65%. No records earn 0%. Every person receives an overall score and rating: Excellent at 85+, On Track at 70+, Needs Support at 50+, otherwise At Risk. Coverage remains visible to distinguish missing evidence from measured results; individual measures without evidence display 0% (no data). Reports, CSV exports, averages and rankings use the same fixed scores, including zeros. Historical periods exclude submissions after the period end and evaluate deadlines as of that end date (or now for ongoing periods); stored review scores remain the latest recorded values because review score history is not versioned.

Run `php tests/features.php` for 98 isolated feature checks, including weighted formulas, overdue tasks, incomplete assessments, missing timestamps, future records and historical cutoffs.

## Demo evaluation records

All 36 currently active accounts have six sample evaluation assignments each (216 total), including reviewed work with sample scores/feedback, submitted work awaiting review, overdue pending work, and future assignments. Assignment instructions and submitted reports are real TXT files under uploads/tasks and uploads/submissions. These originated as fictional demonstration records. Their visible titles, descriptions, feedback and attachments now use normal evaluation wording; the internal demo-evaluation-v1 assignment keys retain their provenance and prevent duplicate seeding. AI score fields remain empty; no AI service or outbound email is called.

The 30 named demo accounts follow their existing Excellent / Neutral / Low groups and currently produce 98%, 74%, and 38% overall respectively. Original staff accounts also have sample evaluations; their overall scores continue to include their existing records. All HR assignments belong to the System Administrator; Employee and IT Admin assignments belong to active Human Resource reviewers. Existing tasks and scores are preserved.

Use `php scripts/seed-demo-evaluations.php --all-active` to preview recipients, then add `--apply` to seed missing assignments. Omit `--all-active` to target only the 30 named demo accounts. Unique assignment keys prevent duplicates on repeated runs. Set `HRMS_DEMO_EVALUATION_REPORT` to a writable CSV path to generate a per-account score summary. The source demo account identifiers are shared with the attendance seeder in config/demo_accounts.php.

## Personnel document fixtures

All 36 active accounts now have five PDF records in My Documents and HR 201 Files: Staff Profile, Role Responsibilities, Performance Summary, Training and Development Plan, and Onboarding Checklist. The 180 records include 108 verified, 36 awaiting review, and 36 returned for corrections. Existing documents and reviews are preserved. Performance summaries are snapshots of the recorded measures at generation time; they do not update automatically when attendance or evaluations change.

The files are stored in uploads/201_files and must be copied with the database when migrating. No government IDs, medical records, signed contracts or formal credential claims are fabricated. The records use existing account information, role responsibilities, current performance measures and proposed development activities.

To prepare a fresh fixture set, run `php scripts/seed-demo-documents.php <manifest-path>`, then use the bundled Python runtime to run `scripts/generate-personnel-pdfs.py <manifest-path>`, inspect the PDFs, and register them with `php scripts/seed-demo-documents.php <manifest-path> --apply`. The generator preserves existing PDFs, and repeated registration skips existing records. Keep the manifest private because it includes staff contact information.

## Employee Performance Appraisal form

Review Evaluations now opens the Employee Performance Appraisal supplied by the user. It includes employee details and period; Job Knowledge 20%, Performance 20%, Customer Relation 15%, Loyalty 15%, Work Attitude 15%, Attendance 15%; individual comments; strengths, weaknesses, recommendations, potential; development options; and evaluator/reviewer/approver details.

The rating scale is Outstanding 4, Above Average 3, Average 2, Below Average 1. Section points are rating / 4 multiplied by its weight; all weights total 100. All six ratings are mandatory. The server calculates and validates the result independently of the live browser preview. Fractional totals such as 88.75% are retained by the decimal task score column. Individual ratings, comments and metadata are stored in task_appraisals; the total becomes the evaluation review score used by the existing performance calculations. Existing scores remain until the full form is saved and are not converted into invented rubric responses.

Only the assigned reviewer can save. Human Resource appraises assigned Employee/Admin work; the System Administrator appraises assigned Human Resource work. Saving is CSRF protected and transactional; only the first review sends a notification. Sign-off names are recorded form entries, not a separate approval workflow or digital signature. Hire and regularization dates are appraisal fields and are not inferred from account creation dates.

Staff can use View Appraisal in My Evaluations to open their own completed form read-only. Print / Save PDF includes the appraisal only. Schema additions initialize automatically and are safe for existing task data. The live installation has been initialized without changing existing scores/statuses.

Run `php tests/features.php` for 126 isolated feature checks, and set `HRMS_APPRAISAL_BROWSER=1` to check live totals, save/reload, personal viewing, both themes, desktop/mobile layouts and print isolation. `php tests/todo_updates.php` also verifies the existing 81 workflow checks against the new form.

## Payroll allowances, shared leave and promotion decisions

HR Payroll uses a searchable staff directory instead of a Payee dropdown. Open payroll displays the selected staff member in a native preview modal over the directory. The modal contains cutoff and generation controls, the payslip preview, Print / Save PDF, and collapsible allowance settings. Close, Escape and backdrop clicking return to the staff list with its search/filter preserved. Successful saves and validation errors reopen inside the modal. Printing hides the modal frame, settings and directory, leaving only the payslip. The allowance filter shows all staff, eligible existing staff, or staff with an allowance amount, and the sort can prioritize allowance eligibility/amount. HR marks eligibility and enters an amount per 15-day cutoff and optional effective date in Allowance settings. No tenure threshold or allowance amount is inferred. Enabled allowances apply automatically to newly generated cutoffs ending on or after the effective date; amounts are not prorated. Existing payslips remain snapshots until HR explicitly regenerates them.

Selecting a cutoff start sets the end 14 days later; selecting the end sets the start 14 days earlier. Both produce exactly 15 inclusive calendar days, including across months/leap years. The server derives the range again and ignores posted allowance values. Payroll generation and allowance changes require HR and CSRF protection. Generated payroll produces a personal notification. Employee/Admin payroll pages list their own generated payslips with View payslip links; another user's payslip ID cannot be opened. Payroll printing still includes only the payslip.

Review Evaluations supports staff name, employee ID, evaluation title and instruction search within the assigned reviewer's submissions; the System Administrator review tab also preserves its search. My Evaluations/dashboard supports text search and All / Not submitted / Submitted (including reviewed) / Reviewed filters within the signed-in user's assignments.

Mandatory Leave is included alongside Sick Leave and Vacation Leave in the same 15-working-day annual balance, as requested. Employees no longer choose an HR reviewer. New requests enter a shared queue with hr_id initially empty; every active HR can view all requests, including earlier requests. The first HR decision records the reviewer and final status, and a second decision cannot change the result or deduct/refund days again. Rejected leave restores the reserved days. No automatic statutory leave allocation or extra balance is created.

Admins use Promotion Suggestions to search active employees and IT administrators by name, employee ID, position or role, then recommend a proposed position with reasons. HR uses Promotions to approve or reject with notes. A pending suggestion for the same employee cannot be duplicated. Approval updates users.job_title; rejection preserves it. Account roles/access permissions, salaries, and allowance eligibility are not changed by promotion decisions. The decision is recorded with HR identity/time and notifies the employee and suggesting admin. Pending suggestions appear in the HR header bell. Employees see their approved position in Account Settings.

New storage is payroll_profiles, promotion_suggestions and users.job_title. Leave hr_id supports empty/shared routing. Initialize through the application pages; existing users, pay, evaluations and request statuses are preserved. Run `php tests/features.php` for 183 isolated checks. Set `HRMS_WORKFLOW_BROWSER=1` for browser workflows, both themes, 320/390/1440 layouts and payroll print isolation. The existing `php tests/todo_updates.php` suite retains 81 passing checks.

Set `HRMS_PAYROLL_MODAL_BROWSER=1` when running `php tests/features.php` to check modal opening, keyboard/backdrop closing, reopening, staff selection, form saving and validation, allowance controls, desktop/mobile themes and payroll-only print layout.
