<?php
/**
 * Laboratory test management — clients, products, pricing, tests, quote requests.
 * Tenant-scoped mod_* tables on the per-site DB (client.whoopix.ai).
 */
return [
    'name' => 'Lab management (mod_lab_clients, products, prices, tests, quotes)',
    'statements' => [

        "CREATE TABLE IF NOT EXISTS `mod_lab_clients` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(150) NOT NULL DEFAULT '',
            `company`    VARCHAR(150) NOT NULL DEFAULT '',
            `email`      VARCHAR(150) NOT NULL DEFAULT '',
            `phone`      VARCHAR(50) NOT NULL DEFAULT '',
            `mobile`     VARCHAR(50) NOT NULL DEFAULT '',
            `username`   VARCHAR(100) NOT NULL DEFAULT '',
            `pass`       VARCHAR(64) NOT NULL DEFAULT '',
            `address`    VARCHAR(255) NOT NULL DEFAULT '',
            `notes`      TEXT,
            `stat`       TINYINT(4) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_email` (`email`),
            KEY `idx_stat` (`stat`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_lab_products` (
            `id`          INT(11) NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(200) NOT NULL DEFAULT '',
            `code`        VARCHAR(50) NOT NULL DEFAULT '',
            `description` TEXT,
            `default_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `stat`        TINYINT(4) NOT NULL DEFAULT 1,
            `sort_order`  INT(11) NOT NULL DEFAULT 0,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_stat` (`stat`),
            KEY `idx_code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_lab_client_prices` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `client_id`  INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_client_product` (`client_id`,`product_id`),
            KEY `idx_client` (`client_id`),
            KEY `idx_product` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_lab_tests` (
            `id`           INT(11) NOT NULL AUTO_INCREMENT,
            `test_number`  VARCHAR(32) NOT NULL DEFAULT '',
            `client_id`    INT(11) NOT NULL,
            `product_id`   INT(11) NOT NULL,
            `status`       VARCHAR(32) NOT NULL DEFAULT 'ordered',
            `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `notes`        TEXT,
            `patient_ref`  VARCHAR(100) NOT NULL DEFAULT '',
            `approved_at`  DATETIME NULL,
            `sent_abroad_at` DATETIME NULL,
            `returned_at`  DATETIME NULL,
            `completed_at` DATETIME NULL,
            `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_test_number` (`test_number`),
            KEY `idx_client` (`client_id`),
            KEY `idx_product` (`product_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_lab_test_statuses` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `test_id`    INT(11) NOT NULL,
            `status`     VARCHAR(32) NOT NULL DEFAULT '',
            `note`       VARCHAR(255) NOT NULL DEFAULT '',
            `admin_user` VARCHAR(100) NOT NULL DEFAULT '',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_test` (`test_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_lab_quote_requests` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `client_id`  INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL DEFAULT 0,
            `product_name` VARCHAR(200) NOT NULL DEFAULT '',
            `message`    TEXT,
            `status`     VARCHAR(20) NOT NULL DEFAULT 'pending',
            `quoted_price` DECIMAL(10,2) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_client` (`client_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Frontend module registration for ,lab URLs (columns vary by tenant)
        "INSERT IGNORE INTO `mod_modules` (`module`, `name_he`, `name_en`, `status`, `order`)
            VALUES ('lab', 'מעבדה', 'Laboratory', 1, 50)",
    ],
];
