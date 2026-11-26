-- Run once on databases created before status history was written for new inquiries.
-- Existing booking rows get a baseline snapshot; the booking status itself is unchanged.
INSERT INTO booking_status_history (booking_id, admin_id, old_status, new_status, note)
SELECT b.id, NULL, NULL, b.status, 'Status history baseline'
FROM bookings AS b
WHERE NOT EXISTS (
  SELECT 1
  FROM booking_status_history AS h
  WHERE h.booking_id = b.id
);
