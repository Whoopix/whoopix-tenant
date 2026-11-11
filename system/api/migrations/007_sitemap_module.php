<?php
return [
    'name' => 'Create sitemap module tables (mod_sitemap_config, mod_sitemap_urls)',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `mod_sitemap_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `auto_generate` tinyint(1) DEFAULT 1,
            `include_images` tinyint(1) DEFAULT 1,
            `include_videos` tinyint(1) DEFAULT 0,
            `default_priority` decimal(2,1) DEFAULT 0.5,
            `default_changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'weekly',
            `generated_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_sitemap_urls` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `url` varchar(500) NOT NULL,
            `priority` decimal(2,1) DEFAULT 0.5,
            `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'weekly',
            `lastmod` datetime DEFAULT NULL,
            `lang` varchar(10) DEFAULT NULL,
            `enabled` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `enabled` (`enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "INSERT INTO `mod_sitemap_config` (`id`,`auto_generate`,`include_images`,`include_videos`,`default_priority`,`default_changefreq`)
         VALUES (1, 1, 1, 0, 0.5, 'weekly')
         ON DUPLICATE KEY UPDATE `id` = `id`",
    ],
];
