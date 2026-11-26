# SAKIB ENTERPRISE

Raw PHP travel-agency website for ordinary PHP/MySQL shared hosting.

## Requirements

- PHP 8.2 or newer with PDO and `pdo_mysql` enabled.
- PHP `fileinfo` enabled for image upload type verification.
- MySQL 8+ / InnoDB / `utf8mb4`.
- Apache with `.htaccess` and `mod_rewrite` enabled for clean routes. This release does not provide a query-string router fallback; ask the host to enable the required rewrite rules.
- Tailwind CSS is compiled locally to `public/assets/css/site.css`; hosting needs no Node.js or build process.
- Public layout styling is written with Tailwind utility classes directly in the PHP templates' HTML `class` attributes. See `TAILWIND.md` to rebuild the static stylesheet.

## Local setup

The local MySQL 8.4.9 instance for this computer binds only to `127.0.0.1`. Its data directory is under the Windows user profile, and its app credentials are in the ignored `.env` file. It does not run as a Windows service, so start it after a Windows restart:

```powershell
Set-Location -LiteralPath "D:\Omor Faruk\Project\Shakib EnterPrice"
powershell -ExecutionPolicy Bypass -File scripts\start-local-mysql.ps1
```

The project schema has already been imported locally. A fresh database install should create an empty database named `sakib_enterprise` first, then import `database/sakib_enterprise.sql`; this SQL file creates tables only and does not insert a default admin. When upgrading an older database, apply `database/migrations/2026_11_26_add_booking_submission_token.sql` if it predates Phase 6, then apply `database/migrations/2026_11_26_backfill_booking_status_history.sql` once to record a baseline for existing inquiries, and apply `database/migrations/2026_11_27_add_customer_portal_login_attempts.sql` for customer status access. New inquiries write their initial `pending` history entry automatically.

To stop the local MySQL process cleanly:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\stop-local-mysql.ps1
```

From PowerShell, run the PHP development server from the project root:

```powershell
Set-Location -LiteralPath "D:\Omor Faruk\Project\Shakib EnterPrice"
php -S 127.0.0.1:8000 -t public public/index.php
```

Open `http://127.0.0.1:8000/`. Check `/health` for application liveness. In local mode, `/health/database` checks the MySQL connection.

If the homepage returns 404, confirm PowerShell is in the project folder before starting the server, and include `-t public public/index.php` exactly as shown above. A made-up route such as `/missing-path` correctly returns 404.

To check the PHP runtime and required extensions:

```powershell
php scripts/check-environment.php
```

To check the configured MySQL connection from the command line:

```powershell
php scripts/check-database.php
```

## First administrator account

- With the local PHP server and MySQL running, open `http://127.0.0.1:8000/admin/setup`.
- The ignored local `.env` file contains the one-time `ADMIN_SETUP_TOKEN`. Use it to create the real administrator account with a password of at least 12 characters.
- Setup closes automatically when an administrator exists. There is no public registration form. Sign in at `/admin/login`.
- Remove `ADMIN_SETUP_TOKEN` from `.env` after creating the account. Do not commit `.env` or share the administrator password.

## cPanel deployment

Follow [DEPLOYMENT.md](DEPLOYMENT.md) for the private/public directory layout, database import and migrations, production `.env` setup, secure first-admin creation, go-live checks, backups, restores, and updates. Do not deploy until the host can serve the project's `public/` directory as the domain document root.

The public pages, booking/contact inquiries, and Phase 7 database integration are implemented. Phase 8 adds first-admin setup, login, the responsive admin dashboard, booking/customer/contact management, catalog/content/settings CRUD, safe image uploads, and activity history. Public and admin views use Tailwind utility classes in HTML; the committed `site.css` is the compiled stylesheet required by shared hosting. Add the business's confirmed phone/WhatsApp/email details and approved destination/package/testimonial content before launch.

Customers can open **Track inquiry** in the public navigation and enter the booking reference from their inquiry confirmation plus the phone number used on that inquiry. They will see the latest status of all inquiries associated with that customer record. No customer password or OTP is required; the booking reference is a high-entropy access credential, so customers should keep it private.
