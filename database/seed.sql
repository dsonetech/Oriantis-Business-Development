INSERT INTO currencies (code, name_fr, name_en, symbol, decimals, active) VALUES
('QAR', 'Riyal qatari', 'Qatari Riyal', 'QAR', 2, 1),
('USD', 'Dollar américain', 'US Dollar', '$', 2, 1),
('EUR', 'Euro', 'Euro', '€', 2, 1),
('DZD', 'Dinar algérien', 'Algerian Dinar', 'DA', 2, 1)
ON DUPLICATE KEY UPDATE code = VALUES(code);

INSERT INTO destinations (code, name_fr, name_en, default_currency_id, active)
SELECT 'DOH', 'Doha', 'Doha', id, 1
FROM currencies
WHERE code = 'QAR'
ON DUPLICATE KEY UPDATE
name_fr = VALUES(name_fr),
name_en = VALUES(name_en),
default_currency_id = VALUES(default_currency_id),
active = 1;

INSERT INTO accommodation_types (code, name_fr, name_en, divisor, sort_order, active) VALUES
('SGL', 'Single', 'Single', 1, 10, 1),
('DBL', 'Double', 'Double', 2, 20, 1),
('TRP', 'Triple', 'Triple', 3, 30, 1),
('CHD_WB', 'Enfant +6 avec lit', 'Child +6 with bed', 1, 40, 1),
('CHD_WOB', 'Enfant -6 sans lit', 'Child -6 without bed', 1, 50, 1),
('INF', 'Bébé', 'Infant', 1, 60, 1)
ON DUPLICATE KEY UPDATE
name_fr = VALUES(name_fr),
name_en = VALUES(name_en),
divisor = VALUES(divisor),
sort_order = VALUES(sort_order),
active = 1;

INSERT INTO exchange_rates (base_currency_id, target_currency_id, rate, active)
SELECT b.id, t.id, 3.650000, 1 FROM currencies b, currencies t
WHERE b.code = 'USD' AND t.code = 'QAR'
AND NOT EXISTS (
    SELECT 1 FROM exchange_rates er WHERE er.base_currency_id = b.id AND er.target_currency_id = t.id AND er.active = 1
);

INSERT INTO exchange_rates (base_currency_id, target_currency_id, rate, active)
SELECT b.id, t.id, 4.150000, 1 FROM currencies b, currencies t
WHERE b.code = 'EUR' AND t.code = 'QAR'
AND NOT EXISTS (
    SELECT 1 FROM exchange_rates er WHERE er.base_currency_id = b.id AND er.target_currency_id = t.id AND er.active = 1
);

INSERT INTO exchange_rates (base_currency_id, target_currency_id, rate, active)
SELECT b.id, t.id, 252.000000, 1 FROM currencies b, currencies t
WHERE b.code = 'USD' AND t.code = 'DZD'
AND NOT EXISTS (
    SELECT 1 FROM exchange_rates er WHERE er.base_currency_id = b.id AND er.target_currency_id = t.id AND er.active = 1
);

INSERT INTO exchange_rates (base_currency_id, target_currency_id, rate, active)
SELECT b.id, t.id, 280.000000, 1 FROM currencies b, currencies t
WHERE b.code = 'EUR' AND t.code = 'DZD'
AND NOT EXISTS (
    SELECT 1 FROM exchange_rates er WHERE er.base_currency_id = b.id AND er.target_currency_id = t.id AND er.active = 1
);


INSERT INTO service_categories (code, name_fr, name_en, active) VALUES
('VISA', 'Visa', 'Visa', 1),
('TRANSFER', 'Transfert', 'Transfer', 1),
('VEHICLE_RENTAL', 'Location véhicule', 'Vehicle Rental', 1),
('CITY_TOUR', 'City Tour', 'City Tour', 1),
('SAFARI', 'Safari', 'Safari', 1),
('SAFARI_LUXE', 'Safari Luxe', 'Safari Luxe', 1),
('BOAT', 'Bateau / Croisière', 'Boat / Cruise', 1),
('GUIDE', 'Guide', 'Guide', 1),
('MEETING_ROOM', 'Location salle de réunion', 'Meeting Room Rental', 1),
('CONFERENCE_ROOM', 'Location salle de conférence', 'Conference Room Rental', 1),
('ACTIVITY', 'Activité', 'Activity', 1),
('OTHER', 'Autres', 'Others', 1)
ON DUPLICATE KEY UPDATE
name_fr = VALUES(name_fr),
name_en = VALUES(name_en),
active = 1;
