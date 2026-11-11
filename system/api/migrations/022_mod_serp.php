<?php
return [
    'name' => 'SERP module tables + mod_modules registration',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `mod_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT,
            `module` VARCHAR(50) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_key_module` (`setting_key`, `module`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_serp_keywords` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `keyword` varchar(255) NOT NULL,
            `search_engine` varchar(20) DEFAULT 'google',
            `location` varchar(100) DEFAULT 'Israel',
            `target_url` varchar(500) DEFAULT NULL,
            `enabled` tinyint(1) DEFAULT '1',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `enabled` (`enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_serp_positions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `keyword_id` int(11) NOT NULL,
            `position` int(11) DEFAULT NULL,
            `url` varchar(500) DEFAULT NULL,
            `checked_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `keyword_id` (`keyword_id`),
            KEY `checked_at` (`checked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_serp_rankings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `keyword_id` int(11) NOT NULL,
            `position` int(11) DEFAULT NULL,
            `url` varchar(500) DEFAULT NULL,
            `checked_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `keyword_id` (`keyword_id`),
            KEY `checked_at` (`checked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_serp_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `api_provider` varchar(50) DEFAULT 'dataforseo',
            `api_username` varchar(255) DEFAULT NULL,
            `api_password` varchar(255) DEFAULT NULL,
            `check_frequency` int(11) DEFAULT '86400',
            `last_check` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "INSERT INTO `mod_serp_config` (`id`, `api_provider`, `check_frequency`)
         VALUES (1, 'dataforseo', 86400)
         ON DUPLICATE KEY UPDATE `id` = `id`",

        "INSERT IGNORE INTO `mod_settings` (`setting_key`, `setting_value`, `module`) VALUES
         ('auto_discover_enabled', '1', 'serp'),
         ('auto_check_enabled', '1', 'serp'),
         ('auto_check_interval', 'daily', 'serp'),
         ('last_auto_check', '', 'serp'),
         ('last_auto_discover', '', 'serp')",

        "INSERT INTO `mod_modules` (`module`, `status`, `order`, `name_he`, `name_en`)
         VALUES ('serp', 1, 53, 'מעקב דירוגים', 'SERP Tracking')
         ON DUPLICATE KEY UPDATE `status` = 1, `name_he` = 'מעקב דירוגים', `name_en` = 'SERP Tracking'",
    ],
];
