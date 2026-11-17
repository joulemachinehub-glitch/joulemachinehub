<?php
// Joule Machine Hub - Production Database Connection Handler
// Configured for Hostinger MySQL Database: u948237985_joulehub

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'u948237985_joulehub');
if (!defined('DB_USER')) define('DB_USER', 'u948237985_joulehub');
if (!defined('DB_PASS')) define('DB_PASS', 'Bala$#2001');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Returns active PDO connection to MySQL database, or null if unreachable.
 * Automatically initializes tables on first successful connection.
 */
function getDbConnection() {
    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }
    $attempted = true;

    // Check if PDO MySQL extension is available
    if (!extension_loaded('pdo_mysql')) {
        return null;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Auto-initialize tables
        initDatabaseTables($pdo);

        return $pdo;
    } catch (Throwable $e) {
        error_log("Database connection error: " . $e->getMessage());
        $pdo = null;
        return null;
    }
}

/**
 * Initialize MySQL tables: 'users', 'leads', and 'seller_products'
 */
function initDatabaseTables($pdo) {
    if (!$pdo) return;

    // 1. Users Table (for registration and authentication)
    $usersTable = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` VARCHAR(50) NOT NULL UNIQUE,
        `name` VARCHAR(150) NOT NULL,
        `company` VARCHAR(200) DEFAULT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `phone` VARCHAR(50) DEFAULT NULL,
        `role` ENUM('buyer', 'seller') DEFAULT 'seller',
        `city` VARCHAR(100) DEFAULT NULL,
        `gst` VARCHAR(50) DEFAULT NULL,
        `password` VARCHAR(255) NOT NULL,
        `joined` VARCHAR(50) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`email`),
        INDEX (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // 2. Leads Table (for all buyer inquiries, RFQs, and messages)
    $leadsTable = "
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
    ";

    // 3. Seller Products Table (Complete machinery specifications & up to 20 images)
    $productsTable = "
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
        `price_inr` DECIMAL(14,2) DEFAULT 0,
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
    ";

    // 4. Central Machinery Catalog Table (Mirrors all 42+ units in database)
    $machinesTable = "
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
    ";

    // 5. Membership Plans Table
    $plansTable = "
    CREATE TABLE IF NOT EXISTS `membership_plans` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `plan_key` VARCHAR(50) NOT NULL UNIQUE,
        `name` VARCHAR(100) NOT NULL,
        `price_inr` DECIMAL(10,2) NOT NULL DEFAULT 0,
        `price_usd` DECIMAL(10,2) NOT NULL DEFAULT 0,
        `listing_limit` INT NOT NULL DEFAULT 2,
        `photo_limit` INT NOT NULL DEFAULT 5,
        `has_video` TINYINT(1) NOT NULL DEFAULT 0,
        `featured_limit` INT NOT NULL DEFAULT 0,
        `analytics_tier` VARCHAR(50) NOT NULL DEFAULT 'basic',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // 6. User Subscriptions Table
    $subscriptionsTable = "
    CREATE TABLE IF NOT EXISTS `user_subscriptions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` VARCHAR(50) NOT NULL,
        `plan_key` VARCHAR(50) NOT NULL,
        `plan_name` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
        `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
        `gateway` VARCHAR(50) DEFAULT 'razorpay',
        `txn_id` VARCHAR(100) DEFAULT NULL,
        `status` ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
        `start_date` DATETIME NOT NULL,
        `end_date` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`user_id`),
        INDEX (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // 7. Payments & Billing Transactions Table
    $paymentsTable = "
    CREATE TABLE IF NOT EXISTS `payments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_id` VARCHAR(50) NOT NULL UNIQUE,
        `user_id` VARCHAR(50) NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `type` ENUM('membership', 'featured_listing', 'lead_credits') DEFAULT 'membership',
        `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
        `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
        `gateway` VARCHAR(50) DEFAULT 'razorpay',
        `txn_id` VARCHAR(100) DEFAULT NULL,
        `status` ENUM('paid', 'pending', 'failed', 'refunded') DEFAULT 'paid',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`user_id`),
        INDEX (`invoice_id`),
        INDEX (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // 8. Password Resets Table
    $passwordResetsTable = "
    CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(150) NOT NULL,
        `token` VARCHAR(100) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`token`),
        INDEX (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    try {
        $pdo->exec($usersTable);
        $pdo->exec($leadsTable);
        $pdo->exec($productsTable);
        $pdo->exec($machinesTable);
        $pdo->exec($plansTable);
        $pdo->exec($subscriptionsTable);
        $pdo->exec($paymentsTable);
        $pdo->exec($passwordResetsTable);

        // Pre-seed default plans if empty
        $planSeed = "
        INSERT INTO `membership_plans` (`plan_key`, `name`, `price_inr`, `price_usd`, `listing_limit`, `photo_limit`, `has_video`, `featured_limit`, `analytics_tier`)
        VALUES 
            ('free', 'Free Membership', 0.00, 0.00, 2, 5, 0, 0, 'basic'),
            ('basic', 'Basic Seller Plan', 999.00, 15.00, 10, 10, 1, 2, 'standard'),
            ('premium', 'Premium Pro Plan', 2499.00, 35.00, 50, 15, 1, 10, 'advanced'),
            ('enterprise', 'Enterprise Unlimited', 9999.00, 120.00, 500, 20, 1, 50, 'custom')
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
        ";
        $pdo->exec($planSeed);

        // Pre-seed Admin User
        $adminInsert = "
        INSERT INTO `users` (`user_id`, `name`, `company`, `email`, `phone`, `role`, `city`, `password`, `joined`) 
        VALUES ('usr-admin-joule', 'Joule Super Admin', 'Joule Machine Hub Global Desk', 'joulemachinehub@gmail.com', '', 'seller', 'Chennai', 'Bala$#2001', 'March 2016')
        ON DUPLICATE KEY UPDATE `password` = 'Bala$#2001';
        ";
        $pdo->exec($adminInsert);
    } catch (Throwable $e) {
        error_log("Database table initialization error: " . $e->getMessage());
    }
}
