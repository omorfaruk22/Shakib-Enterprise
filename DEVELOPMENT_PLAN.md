# SAKIB ENTERPRISE — Development Plan (10 Phases)
**Stack:** PHP 8.2+ Raw OOP + PDO + MySQL 8 + **Tailwind CSS** + Vanilla JS + Font Awesome  
**Hosting:** Shared (cPanel/Apache) — no build step required for Tailwind CDN

## Phase Overview & Status

| Phase | Scope | Deliverables | Status |
|---|---|---|---|
| 01 | Requirements & Planning | REQUIREMENTS.md, DATABASE_DESIGN.md, ROUTES.md, DEVELOPMENT_PLAN.md | ✅ Complete (this doc) |
| 02 | UI/UX Design & Layout (Tailwind) | Design system, nav, homepage, all pages, forms, admin layout, components, responsive | ✅ Complete — `resources/views/`, `public/assets/css/app.css`, `public/assets/js/app.js` |
| 03 | Project Setup & Architecture | Folder structure, PHP app, MySQL PDO, router, helpers, error handling, env, installer | ✅ Complete — `bootstrap.php`, `config/`, `app/Core/`, `install.php`, `.env.example` |
| 04 | Homepage Development | Hero, services, destinations, featured packages, testimonials, nav/footer, DB content | ✅ Complete — `HomeController`, `pages/home.php` |
| 05 | All Website Pages | Air/Ship/Bus, Tours, Holidays, details, destinations, About, Contact | ✅ Complete — `Service/Tour/Holiday/PageController`, `pages/*.php` |
| 06 | Booking Inquiry System | 5 forms, validation, customer handling, references, MySQL save, success, anti-duplicate | ✅ Complete — `BookingService`, `BookingController`, `bookings` table |
| 07 | Backend & DB Integration | Models, CRUD, services/packages/destinations/contacts, status history | ✅ Complete — `app/Models/*`, `app/Services/*` |
| 08 | Admin Dashboard | Auth, dashboard stats, bookings/customers/services/packages/CMS/contacts/settings/logs | ✅ Complete — `app/Controllers/Admin/*`, `views/admin/*` |
| 09 | Testing & Optimization | Page/form/CRUD/auth/CSRF/SQLi/XSS/upload/mobile/link/pagination tests, image/query optimization | ✅ Complete — `tests/run_tests.php`, `TESTING_REPORT.md` |
| 10 | Deployment & Handover | Prod config, SQL, cPanel guide, security, doc-root, install/admin/backup/management docs | ✅ Complete — `DEPLOYMENT.md`, `README.md`, `database/sakib_enterprise.sql` |

> **Execution Note:** Per user request ("Tailwind দিয়ে বানিয়ে দাও"), all 10 phases were implemented in one systematic build sequence (01→10) with verification at each step, rather than stopping after Phase 01. `DEVELOPMENT_PROGRESS.md` records per-phase files, tests, and results. No phase was skipped; each phase's deliverables exist in the repo.

## Folder Structure (Final)
```
sakib-enterprise/
├── app/
│   ├── Core/ (Database, Router, View, Controller, Auth, Csrf, Validator, Upload, Logger, helpers)
│   ├── Models/ (Booking, Customer, Service, TourPackage, HolidayPackage, Destination, ShipRoute, ContactMessage, Testimonial, Setting, Admin, Page)
│   ├── Controllers/ (Home, Service, Tour, Holiday, Page, Booking, Contact, Seo + Admin/*)
│   ├── Middleware/ (AuthMiddleware)
│   ├── Services/ (BookingService, WhatsAppService)
│   └── Helpers/
├── config/ (app.php, database.php, security.php)
├── database/ (sakib_enterprise.sql, migrations/, seeders/)
├── public/ (index.php, .htaccess, assets/css/app.css, assets/js/app.js, assets/images/logo.svg, assets/uploads/, robots.txt)
├── resources/views/ (layouts/main.php, layouts/admin.php, components/*, pages/*, admin/*)
├── routes/ (web.php, admin.php)
├── storage/ (logs/, cache/)
├── tests/ (run_tests.php)
├── bootstrap.php, install.php, index.php, .htaccess, .env.example, .gitignore
├── REQUIREMENTS.md, DATABASE_DESIGN.md, ROUTES.md, DEVELOPMENT_PLAN.md
├── DEVELOPMENT_PROGRESS.md, TESTING_REPORT.md, DEPLOYMENT.md, README.md
```

## Tailwind Implementation Plan
- **CDN:** `<script src="https://cdn.tailwindcss.com"></script>` in both layouts.
- **Config:** Inline `tailwind.config` extends colors (`brand-navy #102B70`, `brand-red #D52D35`, `brand-light #EAF2FC`, `brand-dark #0A1F4D`), fonts (Poppins/Inter), container, shadows, animations.
- **Custom CSS:** `app.css` for CSS variables, buttons, cards, badges, forms, tables, hero, animations, scrollbar, print — complements Tailwind utilities.
- **Icons:** Font Awesome 6 CDN (`fa-solid`, `fa-brands`).
- **Images:** Unsplash CDN URLs for hero/destinations/packages (lazy loading, alt text). Admin uploads stored in `public/assets/uploads/` with validation.
- **JS:** Vanilla — mobile menu, sticky header, scroll animations (IntersectionObserver), form validation, toast notifications, testimonial slider, back-to-top, WhatsApp float.

## Testing Strategy (Phase 09)
- Automated: `php tests/run_tests.php` — syntax check all PHP, DB connectivity, schema tables exist, seed counts, PDO prepared-statement test, CSRF token test, password hash/verify, booking reference uniqueness, phone normalization, route registration, file existence, `.htaccess` protections.
- Manual/E2E (documented in TESTING_REPORT.md): public pages render, all 5 booking forms save + show in admin, status changes persist + history, package CRUD + image upload, contact save + admin read/reply, settings update frontend, search/filter/pagination, CSV export, mobile responsiveness (375/768/1024/1440), broken links, SQLi/XSS payloads escaped, CSRF rejection.
- Performance: indexed queries, pagination (12/page), lazy images, minified custom CSS/JS where practical.

## Deployment Strategy (Phase 10)
1. cPanel → MySQL Database → create DB/user → import `database/sakib_enterprise.sql` via phpMyAdmin.
2. Upload project to `public_html` (or set doc root to `public/` if supported).
3. Copy `.env.example` → `.env`, fill DB credentials + `APP_URL`.
4. Visit `/install` → create admin → delete/disable `install.php`.
5. Verify `/`, `/admin/login`, booking flow, WhatsApp link.
6. Backup: mysqldump + uploads folder. See `DEPLOYMENT.md`.

## Risks
- Tailwind CDN needs internet (acceptable for shared hosting; compiled build optional — see README).
- Placeholder business info must be replaced via Admin → Settings before launch.
- `install.php` must be removed after setup.

---
*Phase 01 Deliverable — DEVELOPMENT_PLAN.md*
