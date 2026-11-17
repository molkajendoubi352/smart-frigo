-- smartfrigo.sql
CREATE DATABASE IF NOT EXISTS smartfrigo;
USE smartfrigo;

-- Users
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    email    VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    initials VARCHAR(4)   NOT NULL
);

-- Fridges — serial is the primary key
CREATE TABLE fridges (
    serial      VARCHAR(50)  PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    min_temp    DECIMAL(4,1) NOT NULL,
    max_temp    DECIMAL(4,1) NOT NULL,
    location    VARCHAR(100),
    current_temp DECIMAL(4,1),
    status      ENUM('online','offline') DEFAULT 'online'
);

-- Links users to fridges (many to many)
CREATE TABLE user_fridges (
    user_id      INT         NOT NULL,
    fridge_serial VARCHAR(50) NOT NULL,
    PRIMARY KEY (user_id, fridge_serial),
    FOREIGN KEY (user_id)       REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (fridge_serial) REFERENCES fridges(serial) ON DELETE CASCADE
);

-- Products — fridge_serial references fridges.serial
CREATE TABLE products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    category     VARCHAR(50),
    fridge_serial VARCHAR(50) NOT NULL,
    quantity     VARCHAR(50),
    expiry_date  DATE NOT NULL,
    emoji        VARCHAR(4),
    FOREIGN KEY (fridge_serial) REFERENCES fridges(serial) ON DELETE CASCADE
);

-- Alerts
CREATE TABLE alerts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    type         ENUM('critical','warning','info') NOT NULL,
    title        VARCHAR(200) NOT NULL,
    description  TEXT,
    fridge_serial VARCHAR(50),
    dismissed    BOOLEAN DEFAULT FALSE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fridge_serial) REFERENCES fridges(serial) ON DELETE SET NULL
);

-- Temperature History
CREATE TABLE temp_history (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    fridge_serial VARCHAR(50) NOT NULL,
    temperature  DECIMAL(4,1) NOT NULL,
    recorded_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fridge_serial) REFERENCES fridges(serial) ON DELETE CASCADE
);
ALTER TABLE temp_history 
  RENAME TO history;

ALTER TABLE history 
  ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'temperature' AFTER fridge_serial,
  ADD COLUMN note VARCHAR(255) NULL AFTER type;
ALTER TABLE history MODIFY temperature DECIMAL(4,1) NULL;