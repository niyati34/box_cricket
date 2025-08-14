-- Database: box_cricket

-- Create database (run once if not exists)
-- CREATE DATABASE IF NOT EXISTS box_cricket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE box_cricket;

SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- Users table: customers, admins, superadmins
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin','superadmin') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grounds (box cricket turfs)
CREATE TABLE IF NOT EXISTS grounds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    admin_id INT UNSIGNED NULL,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ground_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Time slots per ground
CREATE TABLE IF NOT EXISTS time_slots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ground_id INT UNSIGNED NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_slots_ground FOREIGN KEY (ground_id) REFERENCES grounds(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_slot (ground_id, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ground_id INT UNSIGNED NOT NULL,
    slot_id INT UNSIGNED NOT NULL,
    play_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_ground FOREIGN KEY (ground_id) REFERENCES grounds(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_slot FOREIGN KEY (slot_id) REFERENCES time_slots(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_booking (ground_id, slot_id, play_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed a superadmin (change email/password after import)
INSERT INTO users (name, email, phone, password_hash, role, is_active)
VALUES ('Super Admin', 'tannadev05@gmail.com', '0000000000',
        '$2y$10$5tzRjjhVFZu4HE3.BCvamue2sO7rTuwb5kMAHjd3RQ/2hb1Q2IlA6', -- password: dev@123
        'superadmin', 1)
ON DUPLICATE KEY UPDATE email = email;

-- Optional sample data removed to avoid foreign key issues during repeated imports

