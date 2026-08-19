-- Candidate Recruitment Portal Database Schema
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB (XAMPP default)

CREATE DATABASE IF NOT EXISTS `recruitment_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `recruitment_portal`;

-- Users Table (Handles Candidates & Recruiters Authentication)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('candidate', 'recruiter') NOT NULL DEFAULT 'candidate',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baseline Candidate Profiles Table (Prepared for subsequent SRS Profile features)
CREATE TABLE IF NOT EXISTS `candidate_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `headline` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `experience_years` INT DEFAULT 0,
    `education` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_candidate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
