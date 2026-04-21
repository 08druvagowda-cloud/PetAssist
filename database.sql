-- =============================================
-- PetAssist Database Schema
-- Version: 1.0
-- =============================================

CREATE DATABASE IF NOT EXISTS petassist;
USE petassist;

-- =============================================
-- Users Table
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Pets Table
-- =============================================
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(100) NOT NULL,
    type ENUM('Dog', 'Cow', 'Sheep') NOT NULL,
    breed VARCHAR(100) DEFAULT NULL,
    dob DATE NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Vaccine Types Table (Seed Data Included)
-- =============================================
CREATE TABLE IF NOT EXISTS vaccine_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    interval_days INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO vaccine_types (name, interval_days) VALUES
('Rabies', 365),
('Parvovirus', 365),
('DHPP (Distemper)', 365),
('Bordetella', 180),
('Leptospirosis', 365),
('FMD (Foot & Mouth)', 180),
('Brucellosis', 365),
('Clostridial Diseases', 180),
('Canine Influenza', 365),
('Lyme Disease', 365);

-- =============================================
-- Vaccinations Table
-- =============================================
CREATE TABLE IF NOT EXISTS vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    vaccine_type_id INT NOT NULL,
    last_date DATE NOT NULL,
    next_due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (vaccine_type_id) REFERENCES vaccine_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Health Logs Table
-- =============================================
CREATE TABLE IF NOT EXISTS health_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    food ENUM('Good', 'Normal', 'Low') NOT NULL,
    activity ENUM('High', 'Medium', 'Low') NOT NULL,
    behavior ENUM('Happy', 'Normal', 'Aggressive') NOT NULL,
    status ENUM('Healthy', 'Warning', 'Critical') NOT NULL,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Appointments Table
-- =============================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    issue VARCHAR(255) NOT NULL,
    appointment_date DATE NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Support Requests Table
-- =============================================
CREATE TABLE IF NOT EXISTS support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    issue_type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Lost & Found Table
-- =============================================
CREATE TABLE IF NOT EXISTS lost_found (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('Lost', 'Found') NOT NULL,
    pet_details VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    contact VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Default Admin User
-- Password: admin123 (hashed with password_hash)
-- =============================================
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@petassist.com', '$2y$10$TqEn4tVyEbqwXLEA.Jd.deCl4pwmi0crJ0IIJ/fh9Dql2I3zadllK', '9999999999', 'admin');
