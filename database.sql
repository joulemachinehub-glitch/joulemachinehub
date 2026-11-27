-- Joule Machine Hub - Hostinger MySQL Database Schema
-- Database Name: u948237985_joulehub
-- Hostinger MySQL User: u948237985_joulehub

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `users` (Buyer & Seller Accounts)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(50) DEFAULT NULL,
  `role` ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
  `city` VARCHAR(100) DEFAULT NULL,
  `gst` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 1,
  `email_verified_at` DATETIME DEFAULT NULL,
  `joined` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Dumping default demo accounts for table `users`
-- --------------------------------------------------------

INSERT INTO `users` (`user_id`, `name`, `company`, `email`, `phone`, `role`, `city`, `gst`, `password`, `joined`) 
VALUES
('usr-admin-joule', 'Joule Super Admin', 'Joule Machine Hub Global Desk', 'joulemachinehub@gmail.com', '', 'admin', 'Chennai', '33AAAAA0000A1Z5', 'pbkdf2_sha256:100000:46217b291de490976a2d03005e0b747d:44e3af0d8cb7614b99df6dffb94cf3fae4415900a3459466c724603ab59ec612', 'March 2016'),
('usr-seller-01', 'Rajesh Kumar', 'Sri Balaji Precision Tech & Automation', 'seller@balajitech.com', '+91 98450 12389', 'seller', 'Nagoya', '29AAAAA0000A1Z5', 'seller123', 'March 2018'),
('usr-buyer-01', 'Vikram Deshmukh', 'Kirloskar Subcontractors Ltd', 'buyer@joulehub.com', '+91 98220 54120', 'buyer', 'Tokyo', '27BBBBB1111B2Z6', 'buyer123', 'January 2022')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role` = VALUES(`role`);

-- --------------------------------------------------------
-- Table structure for table `leads` (Inquiries, Machine Listings, RFQs)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lead_ref` VARCHAR(50) NOT NULL,
  `form_type` VARCHAR(50) NOT NULL,
  `machine_title` VARCHAR(255) DEFAULT NULL,
  `machine_id` VARCHAR(50) DEFAULT NULL,
  `machine_price` VARCHAR(100) DEFAULT NULL,
  `seller_name` VARCHAR(150) DEFAULT NULL,
  `name` VARCHAR(150) DEFAULT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `budget` VARCHAR(100) DEFAULT NULL,
  `timeline` VARCHAR(100) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `brand` VARCHAR(100) DEFAULT NULL,
  `year` VARCHAR(50) DEFAULT NULL,
  `condition_state` VARCHAR(100) DEFAULT NULL,
  `raw_json` LONGTEXT DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`form_type`),
  INDEX (`phone`),
  INDEX (`email`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `seller_products` (Seller Uploaded Machinery)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `seller_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_ref` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(150) NOT NULL,
  `model` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `equipment_type` VARCHAR(150) NOT NULL,
  `year` VARCHAR(50) NOT NULL,
  `date_of_mfg` VARCHAR(100) NOT NULL,
  `currency_origin` VARCHAR(20) NOT NULL,
  `value_raw` VARCHAR(100) NOT NULL,
  `price_usd` DECIMAL(12,2) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `plant_address` TEXT NOT NULL,
  `condition_state` VARCHAR(100) NOT NULL,
  `hours_run` VARCHAR(100) NOT NULL,
  `power_test` VARCHAR(100) NOT NULL,
  `capacity` VARCHAR(100) NOT NULL,
  `stroke_length` VARCHAR(100) NOT NULL,
  `speed_spm` VARCHAR(100) NOT NULL,
  `die_height` VARCHAR(100) NOT NULL,
  `motor_power` VARCHAR(100) NOT NULL,
  `machine_weight` VARCHAR(100) NOT NULL,
  `dimensions` VARCHAR(100) NOT NULL,
  `short_desc` TEXT NOT NULL,
  `detailed_desc` LONGTEXT NOT NULL,
  `features_json` TEXT NOT NULL,
  `images_json` LONGTEXT NOT NULL,
  `seller_company` VARCHAR(200) NOT NULL,
  `contact_person` VARCHAR(150) NOT NULL,
  `seller_phone` VARCHAR(50) NOT NULL,
  `seller_email` VARCHAR(150) NOT NULL,
  `status` ENUM('active', 'pending_approval', 'sold') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  INDEX (`category`),
  INDEX (`country`),
  INDEX (`city`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Dumping demo product for table `seller_products`
-- --------------------------------------------------------

INSERT INTO `seller_products` (
  `product_ref`, `user_id`, `title`, `brand`, `model`, `category`, `equipment_type`, 
  `year`, `date_of_mfg`, `currency_origin`, `value_raw`, `price_usd`, `country`, 
  `city`, `plant_address`, `condition_state`, `hours_run`, `power_test`, 
  `capacity`, `stroke_length`, `speed_spm`, `die_height`, `motor_power`, 
  `machine_weight`, `dimensions`, `short_desc`, `detailed_desc`, `features_json`, 
  `images_json`, `seller_company`, `contact_person`, `seller_phone`, `seller_email`, `status`
) VALUES (
  'JMH-SEL-000011', 'usr-seller-01', 'Used Chun Zu CBF-35S Cold Bolt Former (Cold Header)', 'Chun Zu Machinery Industry Co., Ltd.', 'CBF-35S', 'Cold Formers & Headers', 'Cold Bolt Former Series',
  '2002', 'May 2002', 'USD', '60,000.00', 60000.00, 'Taiwan',
  'Kaohsiung', 'No. 88, Industrial 2nd Road, Precision Fastener Industrial Park', 'Good - In Daily Production', '9,200 Operating Hours', 'Available Under Power for Dry Run',
  'CBF-35S Multi-Station Cold Header', '120 mm', '180 - 240 SPM', '320 mm', '30 kW',
  '18,500 kg', '3800 mm x 2100 mm x 1950 mm', 'High-speed Taiwanese cold bolt header with complete tooling sets for high tensile fasteners.', 'Maintained in temperature controlled fastener plant. Includes wire uncoiler, straightener, feed rolls, P.K.O. ejection mechanism and full electrical enclosure with touch interface.', '["Multi-station high speed transfer fingers", "Heavy cast iron frame with vibration damping", "Centralized automatic forced lubrication pump", "Safety interlocked acoustic sound enclosure"]',
  '["assets/images/machines/11/1.jpg"]', 'Sri Balaji Precision Tech & Automation', 'Rajesh Kumar', '+91 98450 12389', 'seller@balajitech.com', 'active'
) ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- --------------------------------------------------------
-- Table structure for table `machines` (Global Catalog Storage)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `machines` (
  `id` VARCHAR(50) PRIMARY KEY,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(150) NOT NULL,
  `model` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `equipment_type` VARCHAR(150) DEFAULT NULL,
  `year` INT DEFAULT NULL,
  `price_inr` DECIMAL(14,2) DEFAULT 0,
  `price_usd` DECIMAL(12,2) DEFAULT 0,
  `currency_origin` VARCHAR(20) DEFAULT 'USD',
  `value_raw` VARCHAR(100) DEFAULT NULL,
  `condition_state` VARCHAR(100) DEFAULT NULL,
  `hours_run` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `country_code` VARCHAR(10) DEFAULT NULL,
  `flag` VARCHAR(20) DEFAULT NULL,
  `images_json` LONGTEXT DEFAULT NULL,
  `specs_json` LONGTEXT DEFAULT NULL,
  `features_json` LONGTEXT DEFAULT NULL,
  `short_desc` TEXT DEFAULT NULL,
  `detailed_desc` LONGTEXT DEFAULT NULL,
  `seller_json` TEXT DEFAULT NULL,
  `status` ENUM('active', 'sold', 'draft') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category`),
  INDEX (`country`),
  INDEX (`city`),
  INDEX (`brand`),
  INDEX (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
