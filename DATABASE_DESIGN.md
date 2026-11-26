# Sakib Enterprise Database Design

## 1. Conventions

- MySQL 8+, InnoDB, `utf8mb4` character set and a Unicode collation.
- Use unsigned integer primary keys unless a later deployment constraint calls for another type.
- Store times in UTC where practical and render using the configured business timezone.
- Add `created_at` and `updated_at` to mutable business records; use soft deletion for content where restoring/auditing is useful.
- Add indexes for common list filters/searches and enforce relationships with foreign keys.
- Avoid storing card/payment credentials. No payment processing is in the initial release.

## 2. Proposed Tables

### `admins`

- `id` PK; `name`; `email` UNIQUE; `password_hash`; `role`; `is_active`; `last_login_at` nullable; `created_at`; `updated_at`.
- Registration is not public. Create the first admin through a one-time protected setup flow, then disable/remove that setup route.

### `admin_login_attempts`

- `id` PK; normalized `login_identifier`; `ip_hash` or appropriately minimized IP data; `attempted_at`; `was_successful`.
- Index `(login_identifier, attempted_at)` and `(ip_hash, attempted_at)` for rate limiting; expire old records by retention policy.

### `customers`

- `id` PK; `full_name`; `phone_normalized` indexed; `email` nullable; `created_at`; `updated_at`.
- Match likely repeat customers by normalized phone with careful duplicate resolution; do not silently merge uncertain identities.

### `services`

- `id` PK; `slug` UNIQUE; `name`; `description`; `image_path` nullable; `is_active`; `sort_order`; timestamps.

### `destinations`

- `id` PK; `slug` UNIQUE; `name`; `description`; `image_path` nullable; `is_featured`; `is_active`; timestamps.

### `packages`

- One table with `package_type` constrained to `tour` or `holiday`; `id` PK; `destination_id` FK; `slug` UNIQUE; `title`; `summary`; `description`; `duration_days`; `price` DECIMAL nullable; `currency` default BDT; `inclusions`; `exclusions`; `itinerary` JSON; `image_path`; `is_featured`; `is_active`; timestamps.
- The initial schema uses this shared table because both package types have the same inquiry and content-management fields. Admin views can still separate tour and holiday listings by `package_type`.

### `ship_routes`

- `id` PK; `origin`; `destination`; `description` nullable; `is_active`; `sort_order`; timestamps.

### `bookings`

- `id` PK; `booking_reference` UNIQUE; `customer_id` FK; `service_type` constrained to the five supported categories; `service_id` nullable FK where relevant; `package_id` nullable FK (only where applicable); `ship_route_id` nullable FK; `departure_location` nullable; `destination` nullable; `journey_date` nullable; `return_date` nullable; `passenger_count` nullable; `passenger_type` nullable; `travel_class` nullable; `customer_message` nullable; `status` constrained to the seven supported statuses; timestamps.
- Use nullable service-specific columns because not all booking categories share every field; validate required fields by service type.
- Index `(status, created_at)`, `(service_type, created_at)`, `journey_date`, and `customer_id`.

### `booking_notes`

- `id` PK; `booking_id` FK; `admin_id` FK; `note`; `created_at`.
- Internal notes are separate from customer-submitted messages.

### `booking_status_history`

- `id` PK; `booking_id` FK; `admin_id` nullable FK for system-created events; `old_status`; `new_status`; `note` nullable; `created_at`.
- Insert status change and update booking in one transaction.

### `contact_messages`

- `id` PK; `name`; `phone` nullable; `email` nullable; `subject` nullable; `message`; `status` (unread/read/replied); `admin_note` nullable; timestamps.
- Index `(status, created_at)`.

### `testimonials`

- `id` PK; `customer_display_name`; `quote`; `rating` nullable and only if verified; `is_approved`; `sort_order`; timestamps.
- Only genuine approved testimonials may appear publicly.

### `website_settings`

- `id` PK; `setting_key` UNIQUE; `setting_value` text; `updated_by` nullable FK to admins; `updated_at`.
- Store non-secret display settings only; keep DB credentials and secrets outside public web root or in protected host configuration.

### `website_pages`

- `id` PK; `slug` UNIQUE; `title`; `body`; `meta_title`; `meta_description`; `is_published`; timestamps.

### `media`

- `id` PK; `storage_path`; `original_name` sanitized; `mime_type`; `file_size`; `alt_text`; `uploaded_by` FK; `created_at`.
- Store generated filenames; block script execution in upload directories.

### `admin_activity_logs`

- `id` PK; `admin_id` nullable FK; `action`; `entity_type`; `entity_id` nullable; `created_at`.
- Avoid logging passwords, session identifiers, or unnecessary customer data.

## 3. Relationships

- One customer has many bookings.
- One destination has many tour/holiday packages.
- One booking has many notes and status-history entries.
- One admin creates many booking notes, status changes, uploaded media, and activity records.
- Services/routes/packages are optional booking references depending on service type; retain the submitted category and relevant destination/date details on the booking even if catalog content later changes.

## 4. Integrity and Operations

- Use foreign keys with deliberate delete behavior. Prefer restricting deletion of records referenced by bookings; deactivate catalog records instead.
- Generate unique booking references with a collision-checked random/token strategy and a database unique constraint.
- Use transactions for booking creation with customer upsert and for booking status/history changes.
- Create indexes from actual search/filter routes; avoid indexing every field.
- Provide an installation SQL file and a safe admin setup procedure. Do not expose SQL dumps or configuration through public URLs.

