INSERT INTO service_categories (code, name_fr, name_en, active)
VALUES ('VEHICLE_RENTAL', 'Location véhicule', 'Vehicle Rental', 1)
ON DUPLICATE KEY UPDATE
name_fr=VALUES(name_fr),
name_en=VALUES(name_en),
active=1;
