INSERT INTO service_categories (code,name_fr,name_en,active) VALUES
('MEETING_ROOM','Location salle de réunion','Meeting Room Rental',1),
('CONFERENCE_ROOM','Location salle de conférence','Conference Room Rental',1),
('ACTIVITY','Activité','Activity',1)
ON DUPLICATE KEY UPDATE
name_fr=VALUES(name_fr),
name_en=VALUES(name_en),
active=1;
