# Deploying HRMS to InfinityFree

Deployment still needs an InfinityFree hosting account, a subdomain, and a MySQL database. This guide does not create accounts or publish data.

1. Create a free account at https://www.infinityfree.com/, verify your email, and create a hosting account with a free subdomain. Enable HTTPS using the hosting dashboard's SSL tools before signing in to HRMS.
2. Open the hosting control panel's **MySQL Databases** page. Create a database, then note its exact hostname, database name and username. Use the database password shown or specified by the hosting dashboard, not the local XAMPP root password.
3. In the hosting account's phpMyAdmin, select the new, empty database and import your chosen SQL export. The project's `hris.sql` contains existing account and HR records; for a public demonstration use a separate database containing only sample data. Do not put SQL exports in the public `htdocs` directory. Importing SQL does not transfer uploaded files.
4. Upload the application into the hosting account's `htdocs` directory using its file manager or FTP. Include `vendor`, `config`, `includes`, role folders, images and the root PHP entry points. Preserve `.htaccess` files and the `uploads` directory structure. Exclude `.git`, `.vscode`, tests, scripts, SQL exports, `admin_create.php`, `account_test.php`, and local credential files. For a demo, upload only sample attachments and photos.
5. On the hosting account, copy `config/hosting.example.php` to `config/hosting.local.php` and replace the database placeholders. Leave `HRMS_BASE_URL` empty if the app is directly in `htdocs`. Keep this credentials file private and out of Git. Local XAMPP settings stay unchanged; environment variables still take priority.
6. Confirm the host provides PHP 8.2 or newer with mysqli, mbstring, curl, openssl, fileinfo and zip. The app creates some database columns and tables automatically, so its database user needs the relevant CREATE/ALTER privileges.
7. Open the HTTPS subdomain and verify login/logout for admin, HR and employee; dashboard links; attendance and reports; task assignment/submission/review; profile photos and chat attachments. Verify that unauthenticated direct requests to `uploads/private_chat` and `uploads/profile_photos` return an access-denied response. Check that SQL exports and account-creation helpers were not uploaded.

## Email and file limits

The app already supports PHPMailer with external SMTP. Keep email disabled until you configure `config/mail.local.php` on the host and test delivery. Do not copy your personal local mail password into an upload bundle. External SMTP on port 587 with TLS is supported by the host; PHP `mail()` is not a substitute.

InfinityFree applies limits to stored files: PHP/HTML files are limited to 1 MB, `.htaccess` to 10 kB, and other files to 10 MB. A PHP upload setting cannot override the host's storage limits. Verify actual document uploads and PDF parsing on the host before using this installation.

References:
- https://www.infinityfree.com/
- https://forum.infinityfree.com/t/file-size-and-upload-limits/49308
- https://forum.infinityfree.com/t/support-for-php-mailer/115366/3
