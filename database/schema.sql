CREATE TABLE currencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    symbol VARCHAR(10) NULL,
    decimals TINYINT NOT NULL DEFAULT 2,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE exchange_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    base_currency_id BIGINT UNSIGNED NOT NULL,
    target_currency_id BIGINT UNSIGNED NOT NULL,
    rate DECIMAL(18,6) NOT NULL,
    valid_from DATE NULL,
    valid_to DATE NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (base_currency_id) REFERENCES currencies(id),
    FOREIGN KEY (target_currency_id) REFERENCES currencies(id)
);

CREATE TABLE destinations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    default_currency_id BIGINT UNSIGNED NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (default_currency_id) REFERENCES currencies(id)
);

CREATE TABLE hotels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    stars TINYINT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    address TEXT NULL,
    notes TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);

CREATE TABLE accommodation_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    divisor DECIMAL(6,2) NOT NULL DEFAULT 1,
    sort_order INT DEFAULT 0,
    active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE hotel_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NOT NULL,
    accommodation_type_id BIGINT UNSIGNED NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    valid_from DATE NULL,
    valid_to DATE NULL,
    meal_plan VARCHAR(20) DEFAULT 'BB',
    notes TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (accommodation_type_id) REFERENCES accommodation_types(id),
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);

CREATE TABLE service_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NULL,
    name_fr VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    pricing_type ENUM('PER_PAX','PER_UNIT','PER_GROUP') NOT NULL DEFAULT 'PER_UNIT',
    capacity INT NULL,
    duration_hours DECIMAL(5,2) NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (category_id) REFERENCES service_categories(id)
);

CREATE TABLE service_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    valid_from DATE NULL,
    valid_to DATE NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);

CREATE TABLE agencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_name VARCHAR(150) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    whatsapp VARCHAR(50) NULL,
    country VARCHAR(100) DEFAULT 'Algeria',
    city VARCHAR(100) NULL,
    preferred_language ENUM('fr','en') DEFAULT 'fr',
    preferred_currency_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (preferred_currency_id) REFERENCES currencies(id)
);

CREATE TABLE quotes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(50) NOT NULL UNIQUE,
    agency_id BIGINT UNSIGNED NULL,
    destination_id BIGINT UNSIGNED NOT NULL,
    hotel_id BIGINT UNSIGNED NULL,
    language ENUM('fr','en') NOT NULL DEFAULT 'fr',
    arrival_date DATE NOT NULL,
    departure_date DATE NOT NULL,
    nights INT NOT NULL,
    adults INT NOT NULL DEFAULT 0,
    children_wb INT NOT NULL DEFAULT 0,
    children_wob INT NOT NULL DEFAULT 0,
    infants INT NOT NULL DEFAULT 0,
    source_currency_id BIGINT UNSIGNED NOT NULL,
    selling_currency_id BIGINT UNSIGNED NOT NULL,
    exchange_rate DECIMAL(18,6) NOT NULL DEFAULT 1,
    status ENUM('draft','sent','accepted','rejected','cancelled') NOT NULL DEFAULT 'draft',
    internal_notes TEXT NULL,
    client_notes TEXT NULL,
    valid_until DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES agencies(id),
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (source_currency_id) REFERENCES currencies(id),
    FOREIGN KEY (selling_currency_id) REFERENCES currencies(id)
);

CREATE TABLE quote_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    accommodation_type_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    purchase_rate DECIMAL(18,2) NOT NULL DEFAULT 0,
    nights INT NOT NULL DEFAULT 1,
    total_purchase DECIMAL(18,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    FOREIGN KEY (accommodation_type_id) REFERENCES accommodation_types(id)
);

CREATE TABLE quote_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    description_fr VARCHAR(255) NOT NULL,
    description_en VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    capacity INT NULL,
    purchase_unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    purchase_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE quote_prices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    accommodation_type_id BIGINT UNSIGNED NOT NULL,
    hotel_cost_per_pax DECIMAL(18,2) NOT NULL DEFAULT 0,
    services_cost_per_pax DECIMAL(18,2) NOT NULL DEFAULT 0,
    total_cost_per_pax DECIMAL(18,2) NOT NULL DEFAULT 0,
    profit_per_pax DECIMAL(18,2) NOT NULL DEFAULT 0,
    selling_price_per_pax DECIMAL(18,2) NOT NULL DEFAULT 0,
    currency_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    FOREIGN KEY (accommodation_type_id) REFERENCES accommodation_types(id),
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);
