# Shared Hosting Deployment & Handover

**Status:** Deployment instructions are prepared. The live cPanel account, domain, document root, PHP extensions, and database server have not been provided or verified. No live deployment has been performed.

## 1. Hosting requirements

- PHP 8.2 or newer with `PDO`, `pdo_mysql`, `session`, and `fileinfo` enabled.
- MySQL 8+ with InnoDB and `utf8mb4` support.
- Apache with `mod_rewrite` and `.htaccess` overrides allowed for the directives used by this project. `mod_headers` is recommended for security headers on static assets.
- HTTPS enabled for the domain before creating the first administrator.
- Writable application directories for logs, cache, and uploaded media.
- Node.js, npm, Composer, and a long-running process are not needed on the production host. The Tailwind stylesheet is already compiled and committed.

Select PHP 8.2+ for the domain in cPanel's MultiPHP Manager, if available, and verify the required extensions with the hosting provider. cPanel exposes per-domain PHP version management through MultiPHP Manager ([official guide](https://docs.cpanel.net/cpanel/software/multiphp-manager-for-cpanel/)); the available versions depend on the host.

## 2. Document root and file layout

The domain document root must point to the project's `public/` directory. Keep application code and secrets above/outside the web root:

```text
/home/CPANEL_USER/sakib-enterprise/
  .env                         # private production settings
  bootstrap.php
  app/
  config/
  database/
  resources/
  routes/
  storage/
  public/                       # set this as the domain document root
    index.php
    .htaccess
    assets/
    uploads/
```

In cPanel's **Domains → Manage**, set the domain document root to a path ending in `/sakib-enterprise/public` when the account allows it. cPanel documents document roots as paths relative to the account home directory and notes that changing a root does not move files; the primary domain may not allow a document-root change ([official guide](https://docs.cpanel.net/cpanel/domains/domains/manage-the-domain/130/)).

If the host cannot serve this `public/` directory as the document root, stop before uploading. Do not copy the entire project into `public_html` or expose the project root. This app's front controller and media paths expect the `public/` layout; an alternate host layout needs a host-specific front controller and upload mapping.

The Apache rules use `mod_rewrite`; the conditional `mod_headers` block adds headers to static assets when that module is available. Apache may ignore `.htaccess` rewrites unless `AllowOverride FileInfo` (or an equivalent allowed rule set) is enabled. `Options -Indexes` and access-control rules also need their corresponding override permissions ([Apache rewrite requirements](https://httpd.apache.org/docs/2.4/en/rewrite/htaccess.html), [Apache `.htaccess` guide](https://httpd.apache.org/docs/2.4/en/howto/htaccess.html)). If clean routes return 404 or the server returns 500, ask the host to check the Apache error log and `.htaccess` permissions.

## 3. Create and import the production database

1. In cPanel **MySQL Database Wizard**, create a database and a dedicated database user.
2. Grant that user the privileges needed by the app on this database. Use the exact database name, username, host, and port shown by the hosting provider; cPanel commonly prefixes database and username names.
3. Open phpMyAdmin, select the new empty database, and import `database/sakib_enterprise.sql`.
4. Do not create an administrator directly in SQL. The schema intentionally contains no default admin.

For an existing database, back it up first and apply only the migrations it still needs, in order:

- `database/migrations/2026_11_26_add_booking_submission_token.sql` if the database predates Phase 6.
- `database/migrations/2026_11_26_backfill_booking_status_history.sql` once if it predates Phase 7.
- `database/migrations/2026_11_27_add_customer_portal_login_attempts.sql` for the customer status portal.

The fresh-install SQL already contains the current schema. Do not import it over a production database as a substitute for reviewing and applying migrations.

The application connects through PDO's MySQL driver (`pdo_mysql`); the PHP manual describes this driver and its installation requirements ([PHP PDO_MYSQL manual](https://www.php.net/manual/en/ref.pdo-mysql.php)).

## 4. Configure production settings

Create `.env` in the private project root, outside the document root. Copy the variable names from `.env.example` and set values for the real host:

```dotenv
APP_NAME="SAKIB ENTERPRISE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Dhaka
ADMIN_SETUP_TOKEN=64_character_random_hex_value

DB_HOST=host_value_from_cpanel
DB_PORT=3306
DB_DATABASE=cpanelprefix_sakib_enterprise
DB_USERNAME=cpanelprefix_appuser
DB_PASSWORD=unique_long_database_password
DB_CHARSET=utf8mb4
```

Generate the setup token on a trusted computer with PHP:

```powershell
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Keep `.env` out of the public directory and version control. Use restrictive file permissions supported by the host (for example, `600` for `.env` and `755` for directories). The PHP account must be able to read `.env` and write only to the required storage/upload directories.

## 5. Upload files and set permissions

Upload the runtime folders and files to the private project root: `app/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `bootstrap.php`, and the complete contents of `public/` into its `public/` folder. The `work/` folder, `.git/`, local `.env`, and local database files are not production uploads.

Set ownership/permissions so the PHP process can write to `storage/logs/`, `storage/cache/`, and `public/uploads/media/`. Keep PHP execution disabled in upload directories; the committed upload `.htaccess` files deny script extensions. Do not make the full project tree world-writable.

The environment checker can confirm the local PHP runtime and required extensions:

```sh
php scripts/check-environment.php
```

If the host provides Terminal/SSH, run it from the private project root. Then run `php scripts/check-database.php` to verify the configured PDO connection. If the host has no terminal, use the host's PHP Selector/extension screen and verify the site through HTTPS after setup; do not publish diagnostic scripts under the document root.

## 6. Create the first administrator

1. Confirm HTTPS works and `APP_ENV=production`, `APP_DEBUG=false`, and the one-time setup token are configured.
2. Open `https://your-domain.example/admin/setup` and create the administrator with a unique password of at least 12 characters.
3. Sign in at `/admin/login`, confirm the dashboard opens, then remove `ADMIN_SETUP_TOKEN` from the production `.env` immediately.
4. Confirm `/admin/setup` is closed after an administrator exists. Do not share the password or token or store them in this repository.

## 7. Go-live checklist

- The domain resolves to HTTPS and its document root ends in `/public`.
- PHP is 8.2+ and `PDO`, `pdo_mysql`, `session`, and `fileinfo` are enabled.
- MySQL 8+ is reachable with the production `.env` settings.
- `/`, `/services`, `/booking/air-ticket`, and `/admin/login` load; CSS and images load; an unknown route shows the app's 404 page.
- `/health/database` is not exposed in production; this route is deliberately disabled when `APP_ENV=production`.
- `/.env`, SQL files, and private project directories cannot be downloaded over HTTP.
- A customer can use a real inquiry reference plus the submitted phone to see its latest status after an admin status update.
- First-admin setup token has been removed and the admin can still log in.
- Real phone, WhatsApp, email, packages, destinations, prices, images, and policies have been approved by the business.

## 8. Backup, restore, and updates

### Back up

- Export the production database from phpMyAdmin, including structure and data, and store the backup outside the document root in an encrypted location.
- Back up the code release and `public/uploads/media/` files.
- Back up `.env` separately in an encrypted password manager/secure storage. Do not include it in a public or shared archive.
- Keep at least one restore point before every schema or code update.

### Restore

1. Recreate the database and app user, then import the matching database backup.
2. Restore the matching code release and uploaded media files.
3. Restore the `.env` securely and verify the `APP_URL`, database values, `APP_ENV=production`, and `APP_DEBUG=false`.
4. Restore the limited write permissions for logs, cache, and media uploads.
5. Verify the homepage, admin login, one booking lookup, and the customer status portal over HTTPS.

### Update

1. Export a database backup and back up uploads and `.env`.
2. Upload the reviewed release without overwriting `.env`, `storage/logs/`, or existing uploads.
3. Apply each new SQL migration once, in its documented order. Rebuild Tailwind locally before uploading if templates changed; production does not need Node.js.
4. Recheck the go-live checklist and keep the previous release/backup until the site works.

## 9. Host-specific verification still required

No cPanel provider, domain, account document root, PHP version/extensions, MySQL version, or Apache configuration has been supplied. Therefore the items below remain unverified and no deployment is claimed:

| Host detail | Required value/check |
| --- | --- |
| Provider and cPanel plan | Confirm PHP/MySQL/Apache features and upload limits |
| Domain and document root | Must serve the project's `public/` directory |
| PHP version | 8.2+ for the domain |
| PHP extensions | `PDO`, `pdo_mysql`, `session`, `fileinfo` |
| MySQL version and host | MySQL 8+; provider-supplied host/port/database prefix |
| Apache | `mod_rewrite` and required `.htaccess` overrides; `mod_headers` is recommended for static assets |
| Terminal access | Needed to run the included environment/database scripts on the host |
| HTTPS and backups | Certificate active; provider backup/export/restore path confirmed |
