-- Customer portal sign-in rate limiting. Safe to apply repeatedly.
CREATE TABLE IF NOT EXISTS customer_portal_login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  reference_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_customer_portal_reference_time (reference_hash, attempted_at),
  KEY idx_customer_portal_ip_time (ip_hash, attempted_at),
  KEY idx_customer_portal_attempt_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
