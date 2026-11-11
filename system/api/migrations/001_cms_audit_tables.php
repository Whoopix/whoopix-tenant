<?php
return [
    'name' => 'Create CMS audit tables (cms_requests, cms_logs)',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `cms_requests` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `request_id` VARCHAR(128) NOT NULL,
            `client_id` VARCHAR(128) NOT NULL DEFAULT '',
            `type` VARCHAR(64) NOT NULL DEFAULT '',
            `received_at` DATETIME NOT NULL,
            `status` VARCHAR(32) NOT NULL DEFAULT 'success',
            `error` TEXT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_request_id` (`request_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `cms_logs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `request_id` VARCHAR(128) NOT NULL,
            `client_id` VARCHAR(128) NOT NULL DEFAULT '',
            `type` VARCHAR(64) NOT NULL DEFAULT '',
            `action` VARCHAR(64) NULL,
            `resource_name` VARCHAR(255) NULL,
            `rows_affected` INT NOT NULL DEFAULT 0,
            `status` VARCHAR(32) NOT NULL DEFAULT 'success',
            `error` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_request_id` (`request_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];
