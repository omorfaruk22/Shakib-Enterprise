# Sakib Enterprise Route Map

Routes use raw PHP and PDO and work on Apache shared hosting. Clean paths use the included rewrite rules. Every state-changing public or admin action uses POST, server-side validation, and CSRF protection. Successful form posts use redirects.

## Public routes

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/` | Homepage with database-backed services, destinations, packages, testimonials and settings |
| GET | `/services` | Active public services listing |
| GET | `/air-tickets` | Air-ticket information |
| GET | `/ship-tickets` | Ship-ticket information and available routes |
| GET | `/bus-tickets` | Bus-ticket information |
| GET | `/tour-travel` | Tour information |
| GET | `/holiday-packages` | Holiday-package information |
| GET | `/services/{slug}` | Active service details |
| GET | `/destinations` | Active destinations |
| GET | `/destinations/{slug}` | Active destination details |
| GET | `/packages` | Active tour and holiday packages; supports `?type=tour` or `?type=holiday` |
| GET | `/packages/{slug}` | Active package details |
| GET | `/pages/{slug}` | Published website page |
| GET | `/about-us` | Published About Us content with safe fallback |
| GET | `/contact-us` | Contact details and general inquiry form |
| POST | `/contact-us` | Validate and store a contact message |
| GET | `/booking/{service}` | Service-specific travel inquiry form |
| POST | `/booking/submit` | Validate and store a travel inquiry and customer record transactionally |
| GET | `/booking/success/{reference}` | Private, non-cacheable inquiry confirmation; not a ticket confirmation |
| GET | `/health` | Non-sensitive application liveness check |
| GET | `/health/database` | Local/private database diagnostic; do not expose publicly in production |

## Admin access and routes

There is no public registration form. `/admin/setup` creates the first administrator only when `ADMIN_SETUP_TOKEN` is configured and no administrator exists. Setup closes after the first account is created. In production, first-admin setup requires HTTPS. Login attempts and setup attempts are rate limited. Admin sessions expire after inactivity and all admin write routes require a valid CSRF token.

| Method | Path | Purpose |
| --- | --- | --- |
| GET/POST | `/admin/setup` | One-time first-admin form; available only before an admin account exists |
| GET | `/admin/login` | Admin sign-in form |
| POST | `/admin/login` | Verify credentials, throttle failures and rotate the session ID |
| POST | `/admin/logout` | Record logout, clear the session and expire the session cookie |
| GET | `/admin` | Dashboard with live database counts and recent activity |
| GET | `/admin/bookings` | Searchable, filterable and paginated travel inquiries |
| GET | `/admin/bookings/export.csv` | Export filtered inquiries with spreadsheet-formula escaping |
| GET | `/admin/bookings/{id}` | Inquiry details, customer, internal notes and status history |
| POST | `/admin/bookings/{id}/details` | Update travel details |
| POST | `/admin/bookings/{id}/status` | Change status and add a status-history event |
| POST | `/admin/bookings/{id}/notes` | Add an internal admin note |
| POST | `/admin/bookings/{id}/delete` | Delete a confirmed inquiry and its notes/history; retain its customer |
| GET | `/admin/customers` | Searchable customer list and inquiry counts |
| GET | `/admin/customers/{id}` | Customer details and recent inquiry history |
| POST | `/admin/customers/{id}/update` | Update customer name, phone or email |
| POST | `/admin/customers/{id}/delete` | Delete a customer only when no inquiry history exists |
| GET | `/admin/messages` | Searchable, filterable contact-message list |
| GET | `/admin/messages/export.csv` | Export filtered contact messages with spreadsheet-formula escaping |
| GET | `/admin/messages/{id}` | Contact-message details and admin reply note |
| POST | `/admin/messages/{id}/update` | Update message status and internal note |
| POST | `/admin/messages/{id}/delete` | Delete a confirmed contact message |
| GET | `/admin/catalog/{resource}` | Search and manage the `services`, `destinations`, `packages` or `ship-routes` resource |
| POST | `/admin/catalog/{resource}/save` | Create or update a service, destination, package or ship route |
| POST | `/admin/catalog/{resource}/{id}/delete` | Delete a confirmed catalog record when database references allow it |
| GET | `/admin/content` | Redirect to website-page management |
| GET | `/admin/content/pages` | Search and manage published or draft website pages |
| POST | `/admin/content/pages/save` | Create or update a website page |
| POST | `/admin/content/pages/{id}/delete` | Delete a confirmed website page |
| GET | `/admin/testimonials` | Search and manage testimonials |
| POST | `/admin/testimonials/save` | Create or update a testimonial and approval state |
| POST | `/admin/testimonials/{id}/delete` | Delete a confirmed testimonial |
| GET/POST | `/admin/settings` | View or update business contact, homepage, social and map settings |
| GET | `/admin/media` | View the media library |
| POST | `/admin/media/upload` | Upload a validated JPEG, PNG or WebP image (5 MB maximum) |
| POST | `/admin/media/{id}/delete` | Delete a confirmed media record and generated file |
| GET | `/admin/activity` | Search and review admin activity logs |

## Access and error handling

- Unauthenticated admin requests redirect to `/admin/login` with a validated local return path.
- Admin pages send private, no-store cache headers and render a `noindex` directive.
- Invalid CSRF tokens return 403; invalid form data returns a safe message without exposing database details.
- Unknown routes and missing records return a branded 404. Production errors do not expose stack traces or credentials.

## Planned public routes

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/whatsapp` | Redirect to a configured click-to-chat link |
| GET | `/sitemap.xml` | Sitemap for published pages and public catalog records |
| GET | `/robots.txt` | Search-crawler rules |
