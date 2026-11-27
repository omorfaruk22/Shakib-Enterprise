# SAKIB ENTERPRISE — Travel Agency Website

A complete, production-ready travel agency & tourism website for **SAKIB ENTERPRISE**, Cox's Bazar Sadar, Bangladesh — "Your Trusted Travel Partner".

## 🧰 Tech Stack
- **Backend:** Raw PHP 8.2+ (OOP, PDO, Sessions) — no Laravel/framework required
- **Database:** MySQL 8 / MariaDB (InnoDB, utf8mb4, 17 tables)
- **Frontend:** HTML5, Tailwind CSS (Play CDN), Font Awesome 6, Vanilla JavaScript
- **Deployment:** Shared hosting (cPanel/Apache) ready — no Node/build step, no VPS required

## ✨ Features
- 8 public pages: Home, Air/Ship/Bus Ticket, Tours, Holidays, About, Contact
- Tour & Holiday package detail pages with booking inquiry form
- 5-category booking inquiry system with unique references (SE-YYYY-XXXXXX)
- Booking status workflow: Pending → Contacted → Processing → Confirmed → Completed / Cancelled / Rejected
- Customer login/register with booking history dashboard
- Admin login with 3 roles: Super Admin, Admin, Manager (with permission levels)
- Full CMS from admin panel: services, packages, destinations, ship routes, testimonials, website settings (all text/sections editable from admin, no code changes needed)
- WhatsApp floating chat + click-to-chat links
- CSRF protection, XSS escaping, PDO prepared statements, login rate limiting, secure upload validation
- Fully responsive (mobile/tablet/desktop)
- SEO friendly: sitemap.xml, canonical URLs, meta tags, OG tags
- XML database installation script + one-click `install.php`

## 🚀 Quick Start (Local Development)
1. Create a MySQL database (e.g. `sakib_enterprise`, utf8mb4)
2. Copy `.env.example` to `.env` and fill DB credentials
3. Use a new, empty database. The installer creates the schema and first admin account. If you import `database/sakib_enterprise.sql` manually, import it only into an empty database.
4. Run PHP built-in server:
   ```
   php -S localhost:8080 -t public/ public/router.php
   ```
5. Visit `http://localhost:8080/install.php` to create your first Super Admin
6. Delete `public/install.php` and `install.php` after setup
7. Visit `http://localhost:8080/admin/login` to manage content

## 📁 Project Structure
```
sakib-enterprise/
├── app/                  # Core, Models, Controllers, Services, Middleware
├── config/               # app, database, security configs
├── database/             # SQL installation file
├── public/               # Document root (index.php, assets, uploads, .htaccess)
├── resources/views/      # All templates: layouts, components, pages, admin
├── routes/               # web.php (public) + admin.php (admin)
├── storage/              # logs, cache
├── bootstrap.php         # App bootstrap (env loader, autoloader, session, error handler)
├── install.php           # One-time installer
└── .env.example          # Environment template
```

## 🛠️ cPanel / Shared Hosting Deployment
1. Upload the application outside the public web root and set the domain document root to the project's `public/` folder
2. Create a MySQL database and user via cPanel
3. Copy `.env.example` to `.env` and set production DB credentials
4. Set `APP_DEBUG=false` and `SESSION_SECURE=true` in `.env`
5. Use an empty database, then visit `https://your-domain.com/install.php` to create tables and the first admin account
6. **Delete `public/install.php` and `install.php`** immediately after setup
7. Ensure `storage/` and `public/assets/uploads/` are writable (755 or 777)

## 🔐 Admin Roles
| Role | Permissions |
|------|-------------|
| **Super Admin** | Full access including admin user management |
| **Admin** | Full content/booking/customer/settings access |
| **Manager** | Bookings/Customers/Messages only — cannot change settings or manage admins |

## 👥 Customer Accounts
Customers can register with phone/email, view their booking history, and track inquiry status — no email verification required (optional to add later).

## 📱 WhatsApp Integration
Configure your WhatsApp number from Admin → Settings (digits only, with country code, e.g. `8801700000000`). Floating button appears automatically.

---
Developed for SAKIB ENTERPRISE, Cox's Bazar Sadar, Bangladesh.
