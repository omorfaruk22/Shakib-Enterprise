# SAKIB ENTERPRISE

Raw PHP travel-agency website for ordinary PHP/MySQL shared hosting.

## Requirements

- PHP 8.2 or newer with PDO and `pdo_mysql` enabled.
- MySQL 8+ / InnoDB / `utf8mb4`.
- Apache with `.htaccess`/`mod_rewrite` enabled for clean routes. If rewrite is unavailable, PHP pages can also use explicit query-string routing.
- Tailwind CSS is compiled locally to `public/assets/css/site.css`; hosting needs no Node.js or build process.
- Public layout styling is written with Tailwind utility classes directly in the PHP templates' HTML `class` attributes. See `TAILWIND.md` to rebuild the static stylesheet.

## Local setup

The local MySQL 8.4.9 instance for this computer binds only to `127.0.0.1`. Its data directory is under the Windows user profile, and its app credentials are in the ignored `.env` file. It does not run as a Windows service, so start it after a Windows restart:

```powershell
Set-Location -LiteralPath "D:\Omor Faruk\Project\Shakib EnterPrice"
powershell -ExecutionPolicy Bypass -File scripts\start-local-mysql.ps1
```

The project schema has already been imported locally. If rebuilding the database, create an empty database named `sakib_enterprise` first, then import `database/sakib_enterprise.sql`; this SQL file creates tables only and does not insert a default admin.

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

## cPanel deployment outline

- Point the domain's document root at the project's `public/` directory when the host allows it.
- Keep `.env`, `app/`, `config/`, `database/`, `routes/`, `scripts/`, and `storage/` outside the public document root.
- Import the SQL file using phpMyAdmin, set the real credentials in a protected `.env` file, upload the generated static CSS and assets, and verify PHP/MySQL compatibility.
- Do not use a public default admin credential. The first-admin setup will be implemented in Phase 8.

The public homepage and Phase 5 content pages are implemented: service listings/details, package listings/details, destination listings/details, About Us, and Contact Us. Public styling uses Tailwind utility classes in the page markup; `site.css` is the compiled static file required by browsers and shared hosting. Booking submission, contact-message storage, and the admin dashboard are scheduled for later phases. Add the business's confirmed phone/WhatsApp/email details and approved destination/package/testimonial content before launch.
