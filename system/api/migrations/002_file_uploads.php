<?php
return [
    'name' => 'Create file_uploads metadata table',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `file_uploads` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `filename` VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL DEFAULT '',
            `type` VARCHAR(64) NOT NULL DEFAULT '',
            `extension` VARCHAR(20) NOT NULL DEFAULT '',
            `size_kb` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_type` (`type`),
            KEY `idx_filename` (`filename`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];
