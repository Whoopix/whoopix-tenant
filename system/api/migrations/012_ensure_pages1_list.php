<?php
return [
    'name' => 'Ensure mod_pages1_list table exists',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `mod_pages1_list` (
            `id`       INT(11)      NOT NULL AUTO_INCREMENT,
            `title`    VARCHAR(255) NOT NULL DEFAULT '',
            `template` INT(11)      NOT NULL DEFAULT 1,
            `image`    VARCHAR(500) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];
