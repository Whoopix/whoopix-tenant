<?php
return [
    'name' => 'Alter mod_blog_posts — fix status type, featured_image, add missing columns',
    'statements' => [
        "ALTER TABLE `mod_blog_posts` MODIFY COLUMN `status` varchar(50) NOT NULL DEFAULT 'draft'",
        "ALTER TABLE `mod_blog_posts` MODIFY COLUMN `featured_image` text DEFAULT NULL",
        "ALTER TABLE `mod_blog_posts` ADD COLUMN IF NOT EXISTS `meta_keywords`   varchar(500) DEFAULT NULL AFTER `meta_description`",
        "ALTER TABLE `mod_blog_posts` ADD COLUMN IF NOT EXISTS `link_keywords`   text DEFAULT NULL AFTER `meta_keywords`",
        "ALTER TABLE `mod_blog_posts` ADD COLUMN IF NOT EXISTS `ai_generated`    tinyint(1) NOT NULL DEFAULT 0 AFTER `link_keywords`",
        "ALTER TABLE `mod_blog_posts` ADD COLUMN IF NOT EXISTS `scheduled_date`  datetime DEFAULT NULL AFTER `ai_generated`",
    ],
];
