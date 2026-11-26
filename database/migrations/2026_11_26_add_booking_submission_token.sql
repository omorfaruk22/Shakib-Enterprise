-- Apply once to an existing database to make public inquiry submission idempotent.
ALTER TABLE bookings
  ADD COLUMN submission_token_hash CHAR(64) NULL AFTER booking_reference,
  ADD UNIQUE KEY uq_bookings_submission_token_hash (submission_token_hash);
