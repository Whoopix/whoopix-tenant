<?php
return [
    'name' => 'Create blog module tables (mod_blog_posts, mod_blog_categories)',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `mod_blog_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(100) NOT NULL,
            `description` text,
            `parent_id` int(11) DEFAULT 0,
            `lang` varchar(10) DEFAULT 'he',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`,`lang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mod_blog_posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `content` longtext NOT NULL,
            `excerpt` text,
            `category_id` int(11) DEFAULT 0,
            `lang` varchar(10) DEFAULT 'he',
            `status` varchar(50) NOT NULL DEFAULT 'draft',
            `featured_image` text DEFAULT NULL,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_description` text,
            `meta_keywords` varchar(500) DEFAULT NULL,
            `link_keywords` text DEFAULT NULL,
            `ai_generated` tinyint(1) DEFAULT 0,
            `scheduled_date` datetime DEFAULT NULL,
            `views` int(11) DEFAULT 0,
            `author_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`,`lang`),
            KEY `category_id` (`category_id`),
            KEY `status` (`status`),
            KEY `lang` (`lang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "INSERT INTO `mod_blog_categories` (`name`,`slug`,`description`,`parent_id`,`lang`,`created_at`)
         VALUES ('General','general','General blog posts',0,'he',NOW())
         ON DUPLICATE KEY UPDATE `name` = `name`",
    ],
];
