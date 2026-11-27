# SAKIB ENTERPRISE — Database Design
**Engine:** MySQL 8+ / MariaDB, InnoDB, utf8mb4_unicode_ci  
**File:** `database/sakib_enterprise.sql` (schema + seed)  
**Import:** phpMyAdmin or `mysql < database/sakib_enterprise.sql`

## Entity-Relationship Overview

```
admins 1---* admin_login_attempts
admins 1---* booking_notes
admins 1---* booking_status_history
admins 1---* admin_activity_logs
admins 1---* media

customers 1---* bookings
services 1---* bookings (nullable service_id)
destinations 1---* tour_packages
destinations 1---* holiday_packages
ship_routes 1---* bookings (nullable ship_route_id)

bookings 1---* booking_notes
bookings 1---* booking_status_history
```

## Tables (17)

### 1. `admins`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | |
| name | VARCHAR(100) | |
| email | VARCHAR(191) UNIQUE | login |
| password_hash | VARCHAR(255) | `password_hash()` |
| role | ENUM('super_admin','admin') DEFAULT 'admin' | |
| status | ENUM('active','inactive') DEFAULT 'active' | |
| last_login_at | TIMESTAMP NULL | |
| created_at / updated_at | TIMESTAMP | |

### 2. `admin_login_attempts`
id PK, email VARCHAR(191), ip_address VARCHAR(45), success TINYINT(1), attempted_at TIMESTAMP. Index (email, ip_address, attempted_at). Used for rate limiting.

### 3. `customers`
id PK, name VARCHAR(150), phone VARCHAR(30), phone_normalized VARCHAR(20) UNIQUE NULL (8801XXXXXXXXX), email VARCHAR(191) NULL, address VARCHAR(255) NULL, created_at/updated_at. Deduped on inquiry submit.

### 4. `services`
id PK, slug VARCHAR(100) UNIQUE, name VARCHAR(150), short_description VARCHAR(255), description TEXT, icon VARCHAR(50) (Font Awesome class), image VARCHAR(255) NULL, is_active TINYINT(1), sort_order INT, timestamps. Seed: air-ticket, ship-ticket, bus-ticket, tour-travel, holiday-packages.

### 5. `destinations`
id PK, name VARCHAR(150), slug VARCHAR(150) UNIQUE, description TEXT, image VARCHAR(255) NULL, is_featured TINYINT(1), is_active TINYINT(1), sort_order INT, timestamps. Seed: Cox's Bazar, Saint Martin, Sundarban, Sajek Valley, Bandarban, Kuakata, Rangamati, Sreemangal.

### 6. `tour_packages`
id PK, destination_id FK→destinations NULL ON DELETE SET NULL, title VARCHAR(200), slug VARCHAR(200) UNIQUE, short_description VARCHAR(500), description TEXT, duration_days INT, duration_nights INT, price DECIMAL(12,2), currency VARCHAR(10) DEFAULT 'BDT', image VARCHAR(255) NULL, itinerary TEXT, inclusions TEXT, exclusions TEXT, is_featured TINYINT(1), is_active TINYINT(1), sort_order INT, timestamps. Index (is_active, is_featured, slug).

### 7. `holiday_packages`
Identical structure to `tour_packages` (separate table per spec, allows independent management).

### 8. `ship_routes`
id PK, route_name VARCHAR(200), from_location VARCHAR(150), to_location VARCHAR(150), duration VARCHAR(100) NULL, schedule VARCHAR(255) NULL, price_note VARCHAR(255) NULL, is_active TINYINT(1), sort_order INT, timestamps. Seed: Cox's Bazar → Saint Martin, Cox's Bazar → Sonadia, Chattogram → Saint Martin, etc.

### 9. `bookings` (core)
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | |
| booking_reference | VARCHAR(20) UNIQUE | `SE-YYYY-XXXXXX` |
| customer_id | FK→customers | |
| service_type | ENUM('air','ship','bus','tour','holiday') | |
| service_id | FK→services NULL | |
| package_type | ENUM('tour','holiday') NULL | |
| package_id | BIGINT UNSIGNED NULL | (tour_packages.id or holiday_packages.id per package_type; no single FK due to two tables — validated in app) |
| ship_route_id | FK→ship_routes NULL | |
| departure_location | VARCHAR(150) NULL | |
| destination | VARCHAR(150) NULL | |
| journey_date | DATE NULL | |
| return_date | DATE NULL | |
| passenger_count | INT DEFAULT 1 | |
| passenger_type | ENUM('adult','child','infant','mixed') NULL | |
| travel_class | VARCHAR(50) NULL | Economy/Business etc. |
| customer_name | VARCHAR(150) | snapshot |
| phone | VARCHAR(30) | snapshot |
| email | VARCHAR(191) NULL | snapshot |
| address | VARCHAR(255) NULL | |
| notes | TEXT NULL | customer message |
| status | ENUM('pending','contacted','processing','confirmed','completed','cancelled','rejected') DEFAULT 'pending' | |
| created_at / updated_at | TIMESTAMP | |
Indexes: booking_reference UNIQUE, (status, created_at), (service_type, created_at), customer_id, journey_date.

### 10. `booking_notes`
id PK, booking_id FK→bookings CASCADE, admin_id FK→admins, note TEXT, created_at.

### 11. `booking_status_history`
id PK, booking_id FK→bookings CASCADE, old_status VARCHAR(20) NULL, new_status VARCHAR(20), changed_by FK→admins NULL, note VARCHAR(255) NULL, created_at.

### 12. `contact_messages`
id PK, name VARCHAR(150), phone VARCHAR(30) NULL, email VARCHAR(191) NULL, subject VARCHAR(200) NULL, message TEXT, is_read TINYINT(1), is_replied TINYINT(1), admin_notes TEXT NULL, created_at/updated_at. Index (is_read, created_at).

### 13. `testimonials`
id PK, customer_name VARCHAR(150), location VARCHAR(150) NULL, rating TINYINT, message TEXT, image VARCHAR(255) NULL, is_active TINYINT(1), sort_order INT, timestamps.

### 14. `website_settings` (key-value)
id PK, setting_key VARCHAR(100) UNIQUE, setting_value TEXT NULL, setting_type VARCHAR(20) DEFAULT 'string', updated_at. Keys: business_name, tagline, hero_title, hero_description, hero_cta_text, about_title, about_content, mission, vision, phone, whatsapp, email, address, maps_embed, facebook, youtube, instagram, linkedin, opening_hours, footer_text, meta_title, meta_description, logo_text, favicon. See SQL seed.

### 15. `website_pages`
id PK, slug VARCHAR(100) UNIQUE, title VARCHAR(200), content LONGTEXT, meta_title VARCHAR(200) NULL, meta_description VARCHAR(500) NULL, updated_at. Seed: `about`.

### 16. `media`
id PK, file_name VARCHAR(255), file_path VARCHAR(255), mime_type VARCHAR(100), file_size INT, uploaded_by FK→admins NULL, created_at.

### 17. `admin_activity_logs`
id PK, admin_id FK→admins NULL, action VARCHAR(100), entity_type VARCHAR(50) NULL, entity_id BIGINT NULL, details TEXT NULL, ip_address VARCHAR(45) NULL, created_at. Index (admin_id, created_at).

## Design Decisions
- **Normalized customers:** phone_normalized UNIQUE prevents duplicates; bookings keep snapshot name/phone/email so history survives customer edits.
- **Package polymorphism:** `package_type` + `package_id` avoids duplicating booking logic; app validates ID exists in correct table. Alternative (two FK columns) rejected as nullable clutter.
- **Status history + notes separate:** audit trail + internal communication without polluting bookings row.
- **Settings as key-value:** CMS without schema changes; cached per-request in `Setting` model.
- **Timestamps everywhere:** `created_at`/`updated_at` for sorting, summaries, and audit.
- **FK ON DELETE:** SET NULL for optional relations (destination, service), CASCADE for notes/history (booking deletion removes its trail only after admin confirmation).
- **Transactions:** Booking creation (customer upsert + booking insert + history insert) wrapped in PDO transaction in `BookingService`.

## Seed Data
- 5 services, 8 destinations, 6 tour packages, 6 holiday packages, 4 ship routes, 4 testimonials, ~25 settings, 1 about page. **No default admin** — created via `install.php` with `password_hash()`. Sample package prices/durations are illustrative and editable in admin.

## Indexes Summary
All FKs indexed; UNIQUE on slugs, booking_reference, phone_normalized, setting_key, admin email; composite indexes on bookings(status, created_at) and login_attempts(email, ip, time) for dashboard + rate limiting.

---
*Phase 01 Deliverable — DATABASE_DESIGN.md*
