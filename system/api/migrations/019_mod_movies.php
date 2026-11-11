<?php
/**
 * Movies / videos module — mod_movies table + mod_modules registration + SEO routes.
 * Removes conflicting CMS pages (27/28) that stole movies URLs.
 */
return [
    'name' => 'Movies module: mod_movies table + module registration + SEO cleanup',
    'statements' => [

        "ALTER TABLE `mod_modules` ADD COLUMN IF NOT EXISTS `in_menu` TINYINT(1) NOT NULL DEFAULT 0",

        "CREATE TABLE IF NOT EXISTS `mod_movies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL DEFAULT '',
            `description` text,
            `thumbnail` varchar(255) DEFAULT NULL,
            `video_file` varchar(255) NOT NULL DEFAULT '',
            `published` tinyint(1) NOT NULL DEFAULT 1,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `published` (`published`),
            KEY `sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "INSERT IGNORE INTO `mod_modules` (`module`, `full_mod`, `name_he`, `name_en`, `status`, `order`, `in_menu`)
            VALUES ('movies', 'mod_movies', 'סרטונים', 'Videos', 1, 25, 0)",

        "UPDATE `mod_modules` SET `status` = 1, `full_mod` = 'mod_movies', `name_he` = 'סרטונים', `name_en` = 'Videos', `order` = 25, `in_menu` = 0
            WHERE `module` = 'movies'",

        "DELETE FROM `mod_seo` WHERE `mod` = 'pages' AND (`plink` IN ('movies', 'סרטונים', 'סרטונים/') OR `id` IN (27, 28))",

        "UPDATE `mod_seo` SET `mod` = 'movies', `act` = '', `id` = 0, `ptitle` = 'סרטונים', `description` = 'סרטוני המרפאה'
            WHERE `plink` = 'movies'",

        "INSERT IGNORE INTO `mod_seo` (`id`, `mod`, `act`, `plink`, `lang_id`, `ptitle`, `description`)
            VALUES (0, 'movies', '', 'movies', 1, 'סרטונים', 'סרטוני המרפאה')",

        "UPDATE `mod_seo` SET `mod` = 'movies', `act` = '', `id` = 0, `ptitle` = 'סרטונים', `description` = 'סרטוני המרפאה'
            WHERE `plink` = 'סרטונים'",

        "INSERT IGNORE INTO `mod_seo` (`id`, `mod`, `act`, `plink`, `lang_id`, `ptitle`, `description`)
            VALUES (0, 'movies', '', 'סרטונים', 1, 'סרטונים', 'סרטוני המרפאה')",

        "UPDATE `mod_seo` SET `mod` = 'movies', `act` = '', `id` = 0, `ptitle` = 'סרטונים', `description` = 'סרטוני המרפאה'
            WHERE `plink` = 'סרטונים/'",

        "INSERT IGNORE INTO `mod_seo` (`id`, `mod`, `act`, `plink`, `lang_id`, `ptitle`, `description`)
            VALUES (0, 'movies', '', 'סרטונים/', 1, 'סרטונים', 'סרטוני המרפאה')",

        "DELETE FROM `mod_pages` WHERE `page_id` IN (27, 28)",
        "DELETE FROM `mod_pages_list` WHERE `id` IN (27, 28)",

        "UPDATE `mod_menu` SET `link` = ',movies' WHERE `link` IN (',pages,27', ',pages,28', 'movies', 'סרטונים')",
    ],
];
