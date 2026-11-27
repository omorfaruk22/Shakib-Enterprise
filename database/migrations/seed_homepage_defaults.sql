-- ============================================================
-- Seed default content for new CMS tables (safely — no duplicates)
-- Uses INSERT IGNORE on unique slug-like columns won't work here,
-- so we use NOT EXISTS subqueries keyed on title+label.
-- ============================================================

INSERT INTO why_us_cards (icon,title,description,is_active,sort_order)
  SELECT 'fa-solid fa-shield-halved','Trusted & Reliable','Thousands of happy travelers choose us every year for their Cox''s Bazar and Saint Martin journeys.',1,0
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM why_us_cards WHERE title='Trusted & Reliable');
INSERT INTO why_us_cards (icon,title,description,is_active,sort_order)
  SELECT 'fa-solid fa-tag','Best Price Guarantee','Competitive pricing on air, ship and bus tickets with no hidden charges.',1,1
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM why_us_cards WHERE title='Best Price Guarantee');
INSERT INTO why_us_cards (icon,title,description,is_active,sort_order)
  SELECT 'fa-solid fa-headset','24/7 Support','Our team is here to help before, during and after your trip.',1,2
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM why_us_cards WHERE title='24/7 Support');
INSERT INTO why_us_cards (icon,title,description,is_active,sort_order)
  SELECT 'fa-solid fa-star','Premium Quality','Handpicked hotels, transport and experienced guides for a premium experience.',1,3
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM why_us_cards WHERE title='Premium Quality');

INSERT INTO process_steps (step_number,title,description,is_active,sort_order)
  SELECT 1,'Send Inquiry','Fill out the booking form on our website with your travel details and preferred dates.',1,0
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM process_steps WHERE title='Send Inquiry');
INSERT INTO process_steps (step_number,title,description,is_active,sort_order)
  SELECT 2,'We Contact You','Our team calls you back to confirm details, check availability and share the best pricing.',1,1
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM process_steps WHERE title='We Contact You');
INSERT INTO process_steps (step_number,title,description,is_active,sort_order)
  SELECT 3,'Confirm & Travel','Complete payment and documents, then enjoy your trip with complete peace of mind.',1,2
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM process_steps WHERE title='Confirm & Travel');

INSERT INTO company_stats (icon,number,label,is_active,sort_order)
  SELECT 'fa-solid fa-users','5000+','Happy Customers',1,0
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM company_stats WHERE label='Happy Customers');
INSERT INTO company_stats (icon,number,label,is_active,sort_order)
  SELECT 'fa-solid fa-route','100+','Tour Packages',1,1
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM company_stats WHERE label='Tour Packages');
INSERT INTO company_stats (icon,number,label,is_active,sort_order)
  SELECT 'fa-solid fa-calendar-check','10+','Years Experience',1,2
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM company_stats WHERE label='Years Experience');
INSERT INTO company_stats (icon,number,label,is_active,sort_order)
  SELECT 'fa-solid fa-location-dot','50+','Destinations',1,3
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM company_stats WHERE label='Destinations');
