# Sakib Enterprise Route Map

Routes are implemented as PHP pages/controllers compatible with Apache shared hosting. Clean paths may use `.htaccess` rewrites when supported; otherwise use equivalent `index.php?page=...` links. All write actions use POST, CSRF protection, server validation, and redirects after successful submission.

## 1. Public Routes

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/` | Homepage with configurable and database-backed sections |
| GET | `/services` | Active public services listing |
| GET | `/air-tickets` | Air ticket service information and inquiry preparation details |
| GET | `/ship-tickets` | Ship ticket service information and inquiry preparation details |
| GET | `/bus-tickets` | Bus ticket service information and inquiry preparation details |
| GET | `/tour-travel` | Tour service information and inquiry preparation details |
| GET | `/holiday-packages` | Holiday service information and inquiry preparation details |
| GET | `/services/{slug}` | Other active service details |
| GET | `/destinations` | Active destination listing |
| GET | `/destinations/{slug}` | Active destination details |
| GET | `/packages` | Active package listing; optional `?type=tour` or `?type=holiday` filter |
| GET | `/packages/{slug}` | Active tour or holiday package details |
| GET | `/about-us` | Published About Us content with safe fallback |
| GET | `/contact-us` | Configured business contact information and inquiry context |
| GET | `/health` | Non-sensitive application liveness check |
| GET | `/health/database` | Local/private DB connectivity diagnostic; do not expose publicly in production |

## 2 Planned Public Routes (later phases)

| Method | Path | Purpose |
| --- | --- | --- |
| GET/POST | `/contact-us` | Store and validate contact messages |
| GET | `/booking/{service}` | Service-specific inquiry form, optionally prefilled from a package |
| POST | `/booking/submit` | Validate, save inquiry, generate reference, redirect with result |
| GET | `/booking/success/{reference}` | Confirmation that inquiry was received, not a ticket confirmation |
| GET | `/whatsapp` | Redirect to configured click-to-chat link with encoded text |
| GET | `/sitemap.xml` | Sitemap for published public pages/content |
| GET | `/robots.txt` | Search-crawler rules |

## 3. Admin Routes

All `/admin/*` routes require an authenticated active admin. Apply CSRF checks to state-changing requests and authorization checks to every action.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/admin/login` | Admin login form |
| POST | `/admin/login` | Validate credentials, throttle attempts, regenerate session |
| POST | `/admin/logout` | End session and clear secure cookie |
| GET | `/admin` | Dashboard with real MySQL counts and recent items |
| GET | `/admin/bookings` | Searchable/filterable/paginated inquiry list |
| GET | `/admin/bookings/{id}` | Inquiry details, notes, and status history |
| POST | `/admin/bookings/{id}/update` | Edit permitted fields and/or status; write history transactionally |
| POST | `/admin/bookings/{id}/notes` | Add an internal note |
| POST | `/admin/bookings/{id}/delete` | Confirmed deletion with audit handling |
| GET | `/admin/bookings/export.csv` | Export filtered booking records with safe CSV escaping |
| GET | `/admin/customers` | Searchable customer list and booking history |
| GET | `/admin/customers/{id}` | Customer details and inquiry history |
| GET/POST | `/admin/services` | List/create services |
| GET/POST | `/admin/services/{id}/edit` | Edit service |
| POST | `/admin/services/{id}/delete` | Deactivate or confirmed delete if unreferenced |
| GET/POST | `/admin/packages` | List/create tour and holiday packages |
| GET/POST | `/admin/packages/{id}/edit` | Edit package |
| POST | `/admin/packages/{id}/delete` | Deactivate or confirmed delete if unreferenced |
| GET/POST | `/admin/destinations` | Manage destinations |
| GET/POST | `/admin/ship-routes` | Manage active ship routes |
| GET/POST | `/admin/messages` | View/filter contact messages and update read/replied state |
| GET/POST | `/admin/content` | Edit homepage, About Us, footer, testimonials, SEO, and settings |
| GET/POST | `/admin/settings` | Configure contact details, WhatsApp, maps, social links, and hours |
| GET | `/admin/activity` | Review activity logs |

## 4. Error and Access Routes

- Render branded 404 and 500 pages without exposing stack traces or credentials.
- Redirect unauthenticated admin requests to `/admin/login` with a safe local return path.
- Return field-level validation errors and preserve safe form inputs when possible.
- Reject invalid identifiers, unsupported methods, invalid CSRF tokens, and unauthorized state changes.

