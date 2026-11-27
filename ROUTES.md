# SAKIB ENTERPRISE — Routes
**Front Controller:** `public/index.php` → `bootstrap.php` → `routes/web.php` + `routes/admin.php`  
**Router:** Lightweight regex router (`app/Core/Router.php`). Clean URLs via `.htaccess` → `index.php?route=...`  
**Base URL:** Configured via `APP_URL` in `.env` (e.g., `https://sakibenterprise.com.bd`). All links use `url()` helper.

## Public Routes

| Method | URL | Controller@Method | Description |
|---|---|---|---|
| GET | `/` | HomeController@index | Homepage (dynamic sections) |
| GET | `/air-ticket` | ServiceController@airTicket | Air ticket info + form |
| POST | `/air-ticket/inquiry` | BookingController@storeAir | Save air inquiry → success |
| GET | `/ship-ticket` | ServiceController@shipTicket | Ship info + routes + form |
| POST | `/ship-ticket/inquiry` | BookingController@storeShip | Save ship inquiry |
| GET | `/bus-ticket` | ServiceController@busTicket | Bus info + form |
| POST | `/bus-ticket/inquiry` | BookingController@storeBus | Save bus inquiry |
| GET | `/tours` | TourController@index | Tour listing (search/filter/paginate) |
| GET | `/tours/{slug}` | TourController@show | Tour details + booking form + related |
| POST | `/tours/{slug}/inquiry` | BookingController@storeTour | Save tour inquiry |
| GET | `/holiday-packages` | HolidayController@index | Holiday listing |
| GET | `/holiday-packages/{slug}` | HolidayController@show | Holiday details + booking form |
| POST | `/holiday-packages/{slug}/inquiry` | BookingController@storeHoliday | Save holiday inquiry |
| GET | `/about` | PageController@about | About (DB content) |
| GET | `/contact` | PageController@contact | Contact info + form + maps |
| POST | `/contact` | ContactController@store | Save contact message |
| GET | `/booking-success` | BookingController@success | Success page `?ref=SE-...` |
| GET | `/sitemap.xml` | SeoController@sitemap | Dynamic sitemap |
| GET | `/robots.txt` | static `public/robots.txt` | Robots |
| GET | `/install` | `install.php` | One-time admin setup (disable after) |

## Admin Routes (prefix `/admin`, all require auth except login)

| Method | URL | Controller@Method | Description |
|---|---|---|---|
| GET | `/admin/login` | Admin\AuthController@showLogin | Login form |
| POST | `/admin/login` | Admin\AuthController@login | Authenticate + rate limit |
| POST | `/admin/logout` | Admin\AuthController@logout | Logout + regenerate session |
| GET | `/admin/dashboard` | Admin\DashboardController@index | Stats + recent + summaries |
| GET | `/admin/bookings` | Admin\BookingController@index | List + search/filter/paginate |
| GET | `/admin/bookings/export` | Admin\BookingController@exportCsv | CSV export (filtered) |
| GET | `/admin/bookings/{id}` | Admin\BookingController@show | Details + notes + history |
| POST | `/admin/bookings/{id}/status` | Admin\BookingController@updateStatus | Change status (+history) |
| POST | `/admin/bookings/{id}/note` | Admin\BookingController@addNote | Add internal note |
| POST | `/admin/bookings/{id}/delete` | Admin\BookingController@destroy | Delete (confirm) |
| GET | `/admin/customers` | Admin\CustomerController@index | List + search |
| GET | `/admin/customers/{id}` | Admin\CustomerController@show | Details + booking history |
| GET | `/admin/services` | Admin\ServiceController@index | List |
| GET/POST | `/admin/services/create` | Admin\ServiceController@create/store | Create |
| GET/POST | `/admin/services/{id}/edit` | Admin\ServiceController@edit/update | Edit |
| POST | `/admin/services/{id}/delete` | Admin\ServiceController@destroy | Delete |
| GET/POST | `/admin/tour-packages[/create]` etc. | Admin\PackageController (type=tour) | Tour CRUD |
| GET/POST | `/admin/holiday-packages[/create]` etc. | Admin\PackageController (type=holiday) | Holiday CRUD |
| GET/POST | `/admin/destinations[/create]` etc. | Admin\DestinationController | Destination CRUD |
| GET/POST | `/admin/ship-routes[/create]` etc. | Admin\ShipRouteController | Ship route CRUD |
| GET/POST | `/admin/testimonials[/create]` etc. | Admin\TestimonialController | Testimonial CRUD |
| GET | `/admin/contacts` | Admin\ContactController@index | Messages list |
| GET | `/admin/contacts/{id}` | Admin\ContactController@show | Message details |
| POST | `/admin/contacts/{id}/read` | Admin\ContactController@markRead | Mark read/unread |
| POST | `/admin/contacts/{id}/reply` | Admin\ContactController@markReplied | Mark replied |
| POST | `/admin/contacts/{id}/note` | Admin\ContactController@addNote | Internal note |
| POST | `/admin/contacts/{id}/delete` | Admin\ContactController@destroy | Delete |
| GET/POST | `/admin/settings` | Admin\SettingController@index/update | CMS settings (grouped) |
| GET | `/admin/activity-logs` | Admin\DashboardController@logs | Activity log list |
| GET/POST | `/admin/profile` | Admin\AuthController@profile/updateProfile | Change name/password |

## URL Examples
- `/tours/coxs-bazar-3-days-tour`
- `/holiday-packages/saint-martin-holiday`
- `/admin/bookings?status=pending&type=air&search=017`
- `/booking-success?ref=SE-2026-8K3P9Q`

## Security Notes
- All POST routes require valid CSRF token (`_token` field).
- Admin routes wrapped in `AuthMiddleware` (session check + timeout).
- Booking/contact POSTs rate-limited by IP (simple throttle) + honeypot field.
- File uploads only via admin POSTs with MIME/size validation.

---
*Phase 01 Deliverable — ROUTES.md*
