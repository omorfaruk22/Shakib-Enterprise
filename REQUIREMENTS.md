# SAKIB ENTERPRISE — Requirements Document
**Project:** Professional Travel Agency & Tourism Business Website  
**Location:** Cox's Bazar Sadar, Bangladesh  
**Tagline:** Your Trusted Travel Partner  
**Date:** 2026-11-27  
**Version:** 1.0 — Phase 01  
**Stack Update (User Request):** Tailwind CSS (instead of Bootstrap) + Raw PHP 8.2+ + MySQL 8+ for Shared Hosting

---

## 1. Business Overview

SAKIB ENTERPRISE is a travel agency in Cox's Bazar Sadar serving Bangladesh-based travelers. The website must establish trust, present services, display tour/holiday packages, collect booking inquiries, provide WhatsApp/contact options, and allow admin operations — all on standard PHP/MySQL shared hosting (cPanel/Apache) without VPS, Node, or frameworks.

**Primary Goals:**
1. Professional online identity
2. Present air/ship/bus ticket + tour/holiday services
3. Display packages with details pages
4. Collect structured booking inquiries into MySQL
5. WhatsApp click-to-chat using admin-configured number
6. Full admin dashboard + CMS
7. Expandable architecture

## 2. Technology Stack (Final)

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Vanilla JS, **Tailwind CSS (CDN + custom config)**, Font Awesome 6 |
| Backend | Raw PHP 8.2+ (OOP), Sessions, PDO prepared statements |
| Database | MySQL 8+ / MariaDB, InnoDB, utf8mb4 |
| Hosting | Shared hosting, cPanel, Apache, PHP, MySQL |
| Forbidden | Laravel, Node, React, Next, Vue, MongoDB, VPS/SSH/API server |

**Tailwind Decision:** User explicitly requested Tailwind CSS. Implementation uses Tailwind Play CDN (`https://cdn.tailwindcss.com`) with inline `tailwind.config` for brand colors, plus local `public/assets/css/app.css` for custom components/animations. This keeps shared-hosting deployment simple (no build step required). A compiled-CSS build path is documented in README for future optimization.

**Brand Colors:**
- Navy `#102B70` (primary)
- Red `#D52D35` (secondary/CTA)
- Light Blue `#EAF2FC` (background)
- White, Dark Navy `#0A1F4D`, Light Gray, Neutral text

**Typography:** Poppins (headings) + Inter (body) via Google Fonts.

## 3. Functional Requirements

### 3.1 Public Website (8 primary pages + details)
- **Home:** Hero ("Explore the World with SAKIB ENTERPRISE"), intro, CTA, services, destinations, featured tours, holiday packages, why choose us, booking process, testimonials, trust section, contact info, WhatsApp CTA, footer. All dynamic content from DB/settings.
- **Air Ticket:** Info + inquiry form (from, to, journey/return date, passengers, type, class, name, phone, email optional, message)
- **Ship Ticket:** Info + routes (from DB) + inquiry form (route, date, passengers, customer info)
- **Bus Ticket:** Info + inquiry form (from, to, date, passengers, name, phone, message)
- **Tour & Travel:** Listing + details per tour (images, duration, price, description, itinerary, inclusions/exclusions, booking button, related)
- **Holiday Packages:** Listing + details per package (same fields as tours)
- **About Us:** Overview, mission, vision, why choose us — editable from admin (`website_pages` + settings)
- **Contact Us:** Address, phones, email, WhatsApp, Google Maps embed, contact form, hours — all from settings

### 3.2 Booking Inquiry System (Core)
- 5 categories: Air, Ship, Bus, Tour, Holiday
- Workflow: select service → travel info → personal info → review → submit → validate → save → generate unique reference (e.g., `SE-2026-XXXXXX`) → success message → admin dashboard → contact customer → update status
- Statuses: Pending, Contacted, Processing, Confirmed, Completed, Cancelled, Rejected
- Admin: view/search/filter (type/status/date), full details, update status, internal notes, edit permitted fields, delete with confirmation, history, CSV export
- **Business Rule:** Inquiry only — never claim ticket issued, no fake live availability/confirmations. Success page states "Inquiry received, we will contact you."

### 3.3 Admin Authentication
- Login/logout, `password_hash`/`password_verify`, session regeneration, rate limiting (5 attempts / 15 min per IP+email), CSRF, protected routes, session timeout (30 min idle), secure cookies (httponly, samesite, secure in HTTPS), no default public admin — created via `install.php` (deleted/disabled after setup)

### 3.4 Admin Dashboard
- Stats: total/pending/processing/confirmed/completed/cancelled bookings, tour/holiday packages, services, contact messages
- Components: stat cards, recent bookings/messages, status overview, service-wise summary, date summary, quick actions — all from DB

### 3.5 CMS
- Settings: hero title/desc/CTA, about/mission/vision, contact info, address, phones, email, WhatsApp, social links, footer, logo/favicon, SEO meta, maps embed, hours
- Services CRUD + image upload + active toggle
- Tour/Holiday Packages CRUD + image + price/duration/itinerary/inclusions/exclusions/featured/active
- Destinations CRUD + image + featured/active
- Ship Routes CRUD
- Testimonials CRUD

### 3.6 Customers & Contacts
- Customers auto-created from inquiries, deduped by normalized phone (`8801XXXXXXXXX`), list/search/filter, booking history
- Contact messages: view/search, read/unread/replied, internal notes, delete

### 3.7 WhatsApp
- Floating button, header, contact page, booking shortcut — all `https://wa.me/{number}?text={encoded}` from settings. No fake API claims. Hidden if number is placeholder/empty.

### 3.8 SEO / Performance / Responsive
- SEO URLs, page titles/descriptions, OG, canonical, sitemap.xml, robots.txt, semantic HTML, alt text, JSON-LD LocalBusiness (Cox's Bazar), lazy images, pagination, indexed queries
- Fully responsive: desktop/laptop/tablet/Android/iPhone, mobile nav, touch buttons, no horizontal overflow

## 4. Non-Functional Requirements
- **Security:** PDO prepared, XSS escaping, CSRF, hashing, secure sessions, input validation, upload MIME/size/random-name validation, `.htaccess` protections, production error logging (no detail exposure)
- **Performance:** Indexed DB, efficient queries, lazy loading, minimal deps, reusable components
- **Maintainability:** OOP, separation of concerns, reusable views, comments, organized folders
- **Compatibility:** Must run after uploading files + importing SQL on shared hosting

## 5. Out of Scope (Future, Not Faked)
Online payment, real-time flight search, automated email/SMS, customer accounts, advanced analytics — architecture-ready but not implemented.

## 6. Missing Business Information (Placeholders Required)
Per spec §19, do NOT invent real contact data. Placeholders used until admin provides real values:
- Phone: `+880 1XXX-XXXXXX` (placeholder)
- WhatsApp: empty/placeholder (button hidden until configured)
- Email: `info@sakibenterprise.com.bd` (placeholder, editable)
- Address: `Cox's Bazar Sadar, Cox's Bazar, Bangladesh` (editable)
- Maps embed: placeholder iframe URL (editable)
- Social links: `#` placeholders (editable)
- Logo: text-based "SE" SVG mark (no invented logo file)
- Opening hours: `Sat–Thu: 9:00 AM – 9:00 PM` placeholder (editable)

All placeholders are in `website_settings` seed and editable via Admin → Settings.

## 7. Acceptance Criteria (Summary)
See Master Prompt §21 (25 items). This project implements all 25 in code + SQL + docs.

## 8. Risks & Mitigations
| Risk | Mitigation |
|---|---|
| Tailwind CDN requires internet | Local `app.css` fallback + README compiled-build instructions; CDN is standard for shared hosting |
| Shared hosting doc-root confusion | `public/` as doc root + root `.htaccess` fallback + cPanel guide |
| No MySQL in dev sandbox | MariaDB installed locally for testing; SQL is MySQL 8 compatible |
| Fake data perception | Seed data clearly marked as sample packages; business contact uses placeholders |

---
*Phase 01 Deliverable — REQUIREMENTS.md*
