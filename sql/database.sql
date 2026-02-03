-- Bank Queue Management System Database Schema (No Priority)
CREATE DATABASE IF NOT EXISTS bank_queue_db;
USE bank_queue_db;

DROP TABLE IF EXISTS tokens;
DROP TABLE IF EXISTS counters;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN','STAFF') DEFAULT 'STAFF',
    counter_id INT NULL,
    status ENUM('ONLINE','BREAK','OFFLINE') DEFAULT 'OFFLINE',
    is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    prefix VARCHAR(5) NOT NULL,
    avg_service_time INT NOT NULL DEFAULT 5
);

CREATE TABLE counters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    counter_id INT NULL,
    token_number INT NOT NULL,
    token_code VARCHAR(10) NOT NULL,
    status ENUM('WAITING','CALLING','SERVING','COMPLETED','CANCELLED')
        DEFAULT 'WAITING',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    called_at DATETIME NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (counter_id) REFERENCES counters(id)
);

INSERT INTO services (name, prefix, avg_service_time) VALUES
('Deposit', 'D', 5),
('Withdrawal', 'W', 4),
('Loan', 'L', 10),
('Account Opening', 'A', 12),
('Card Request', 'C', 7);

INSERT INTO counters (service_id, name, location) VALUES
(1, 'Deposit Counter 1', 'Ground Floor'),
(1, 'Deposit Counter 2', 'Ground Floor'),
(2, 'Withdrawal Counter 1', 'Ground Floor'),
(3, 'Loan Counter 1', 'First Floor'),
(4, 'Account Opening Counter 1', 'First Floor'),
(5, 'Card Desk', 'Ground Floor');
