# Sakib Enterprise Website Requirements

## 1. Project Summary

SAKIB ENTERPRISE is a travel agency and tourism business located in Cox's Bazar Sadar, Bangladesh. The project is a database-driven business website for presenting travel services and packages, receiving booking inquiries, and giving staff a secure way to manage inquiries and website content. The initial release is inquiry-based; it does not issue tickets or process payments.

## 2. Platform and Constraints

- Deploy on ordinary PHP/MySQL shared hosting using cPanel and Apache.
- Use Raw PHP 8.2+, object-oriented PHP where appropriate, PHP Sessions, PDO prepared statements, and MySQL 8+ with InnoDB and `utf8mb4`.
- Customer pages use HTML5, Tailwind CSS, and vanilla JavaScript; icons may use small inline SVGs or Font Awesome/Lucide only where useful.
- Compile Tailwind to a static CSS file before upload. Use the standalone CLI locally if needed; production shared hosting does not need Node.js or a CSS build process.
- No Laravel, Node.js, React, Next.js, Vue.js, MongoDB, VPS, SSH requirement, or separate API server.
- The application must be deployable by uploading files and importing/configuring the MySQL database.
- Keep dependencies minimal and avoid relying on build steps or long-running processes on the host.

## 3. User Types

### Visitor / Customer

- Browse public pages, services, destinations, and active packages.
- Submit service-specific booking inquiries and general contact messages.
- Contact the business using configured phone/WhatsApp details.
- Receive a booking reference and a clear notice that the submission is an inquiry, not an issued ticket or confirmed booking.

### Administrator

- Sign in through a protected admin area; no public admin registration.
- Review and manage bookings, customers, contact messages, services, packages, destinations, ship routes, testimonials, and site settings according to access permissions.
- Update booking status and internal notes; review status history.
- Customers can use a booking reference and the phone number submitted with that inquiry to view the latest status of their own inquiries.
- Configure business contact details and public website content without editing source files.

## 4. Functional Requirements

### Public Website

- Home page with hero, introduction, services, destinations, featured packages, trust/why-us content, booking CTA, testimonials, contact/WhatsApp CTA, and footer.
- Air Ticket, Ship Ticket, Bus Ticket, Tour & Travel, Holiday Packages, About Us, and Contact Us pages.
- Individual package and destination detail pages.
- Public content and listing data are loaded from MySQL where applicable; no invented contact data, ratings, reviews, live flight availability, or unsupported claims.
- Contact details, social links, opening hours, maps embed, SEO metadata, hero content, and logo/favicon are configurable. Unprovided details remain clearly labeled setup placeholders.

### Booking Inquiry

- Support Air Ticket, Ship Ticket, Bus Ticket, Tour Package, and Holiday Package inquiry categories.
- Collect shared customer details and the relevant service-specific travel information; optional fields remain nullable where not applicable.
- Validate on the server, prevent accidental duplicate submissions, save the inquiry, generate a unique reference, and show a success/error state.
- Admin can search/filter by category, status, and date; view details; update status; add internal notes; edit permitted information; delete only after confirmation; inspect status history; and export records to CSV.
- Statuses: Pending, Contacted, Processing, Confirmed, Completed, Cancelled, Rejected.
- A submitted inquiry is not a ticket issuance, payment, or confirmation. No fake real-time availability or ticket confirmation.

### Admin and Content Management

- Secure admin login/logout, password hashing/verification, session regeneration, session timeout, rate limiting, CSRF protection, and protected routes.
- Dashboard statistics and recent activity are derived from actual database records.
- CRUD and active/featured controls for services, destinations, tour packages, holiday packages, and ship routes.
- Manage booking inquiries, normalized customer records, contact messages, testimonials, website pages/settings, and admin activity logs.
- Upload only approved image formats with server-verified MIME/content, size limits, random filenames, and execution disabled in upload storage.

### WhatsApp and Contact

- Use a configured WhatsApp number for click-to-chat links, including properly URL-encoded optional inquiry text.
- Clearly label WhatsApp as click-to-chat, not automated WhatsApp API messaging.
- Store contact form submissions in MySQL and let admins mark them read/unread/replied, add notes, or delete them.

## 5. Non-Functional Requirements

- Responsive layout for desktop, laptop, tablet, Android/iPhone, including small screens and no horizontal overflow.
- Brand colors: navy `#102B70`, red `#D52D35`, light blue `#EAF2FC`, white, and neutral text colors.
- Clear typography, accessible contrast, consistent spacing, restrained animation, friendly validation/errors, success messages, loading/empty states, and reusable layouts.
- Security: PDO prepared statements, output escaping, CSRF tokens, server-side validation, least-privilege admin authorization, secure cookies/sessions, safe uploads, protected configuration/log/storage, and production-safe error handling.
- Performance: optimized/lazy-loaded images, pagination, indexed queries, minimal dependencies, and suitable caching for public content.
- SEO foundations: semantic HTML, page-specific title/description, canonical/Open Graph metadata, sitemap, robots.txt, alt text, SEO-friendly URLs, and accurate Cox's Bazar local-business information.
- Maintainable separation between routing, presentation, validation, persistence, and business logic within a shared-hosting-compatible structure.

## 6. Business Rules and Boundaries

- Initial booking is an inquiry workflow. Staff contact the customer and update the record; the site must not imply a ticket has been issued.
- Payment integration, live flight search, automated SMS/email, customer accounts/history, and advanced analytics are future scope only and require legitimate providers/authorization.
- Do not invent phone numbers, email addresses, WhatsApp number, office hours, ratings, testimonials, or provider availability.
- Protect customer contact/travel data and restrict it to authorized administrators.

## 7. Known Inputs Needed Before Production

The project can be developed with editable placeholders. Before production launch, obtain and verify:

- Official business logo/favicon, if available.
- Phone numbers, email, WhatsApp number, office address, opening hours, map embed, and social links.
- Approved service descriptions, routes, destinations, package prices/durations/itineraries/inclusions/exclusions, and genuine testimonials.
- Hosting account PHP/MySQL versions, database credentials, document-root layout, and supported Apache rewrite settings.
- Authorized initial admin identity and password setup method.

