<?php
/**
 * Team members module — mod_team_members table + mod_modules registration + seed.
 */
return [
    'name' => 'Team module: mod_team_members table + module registration + homepage seed',
    'statements' => [

        "CREATE TABLE IF NOT EXISTS `mod_team_members` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `role_title` varchar(255) NOT NULL DEFAULT '',
            `photo` varchar(255) DEFAULT NULL,
            `social_url` varchar(512) DEFAULT NULL,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `active` (`active`),
            KEY `sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "INSERT IGNORE INTO `mod_modules` (`module`, `name_he`, `name_en`, `status`, `order`)
            VALUES ('team', 'צוות', 'Team', 1, 26)",

        "UPDATE `mod_modules` SET `status` = 1, `name_he` = 'צוות', `name_en` = 'Team', `order` = 26
            WHERE `module` = 'team'",

        "INSERT INTO `mod_team_members` (`name`, `role_title`, `photo`, `social_url`, `sort_order`, `active`, `created_at`, `updated_at`)
            SELECT 'Jamie Oliver', 'טכנאי הרדמה', 'resources/design/images/homepage-2/team/team-img-01.jpg', NULL, 1, 1, NOW(), NOW()
            FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mod_team_members` LIMIT 1)",

        "INSERT INTO `mod_team_members` (`name`, `role_title`, `photo`, `social_url`, `sort_order`, `active`, `created_at`, `updated_at`)
            SELECT 'David Green', 'פלבטומיסט', 'resources/design/images/homepage-2/team/team-img-02.jpg', NULL, 2, 1, NOW(), NOW()
            FROM DUAL WHERE (SELECT COUNT(*) FROM `mod_team_members`) < 2",

        "INSERT INTO `mod_team_members` (`name`, `role_title`, `photo`, `social_url`, `sort_order`, `active`, `created_at`, `updated_at`)
            SELECT 'Mariam Ness', 'טכנאי היסטולוגיה', 'resources/design/images/homepage-2/team/team-img-03.jpg', NULL, 3, 1, NOW(), NOW()
            FROM DUAL WHERE (SELECT COUNT(*) FROM `mod_team_members`) < 3",

        "INSERT INTO `mod_team_members` (`name`, `role_title`, `photo`, `social_url`, `sort_order`, `active`, `created_at`, `updated_at`)
            SELECT 'Roman Amata', 'צ\'יף טכנולוגי', 'resources/design/images/homepage-2/team/team-img-04.jpg', NULL, 4, 1, NOW(), NOW()
            FROM DUAL WHERE (SELECT COUNT(*) FROM `mod_team_members`) < 4",
    ],
];
