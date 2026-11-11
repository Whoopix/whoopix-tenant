<?php
return [
    'name' => 'Setup mod_pages1 and mod_pages1_list tables',
    'statements' => [
        "DROP TABLE IF EXISTS `mod_pages1`",
        "DROP TABLE IF EXISTS `mod_pages1_list`",

        "CREATE TABLE `mod_pages1_list` (
            `id`       INT(11)      NOT NULL AUTO_INCREMENT,
            `title`    VARCHAR(255) NOT NULL DEFAULT '',
            `template` INT(11)      NOT NULL DEFAULT 1,
            `image`    VARCHAR(500) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `mod_pages1` (
            `id`          INT(11)      NOT NULL AUTO_INCREMENT,
            `page_id`     INT(11)      NOT NULL DEFAULT 0,
            `lang_id`     INT(11)      NOT NULL DEFAULT 0,
            `title`       VARCHAR(255) NOT NULL DEFAULT '',
            `ptitle`      VARCHAR(255) NOT NULL DEFAULT '',
            `text`        LONGTEXT,
            `keywords`    VARCHAR(500) NOT NULL DEFAULT '',
            `description` VARCHAR(500) NOT NULL DEFAULT '',
            `plink`       VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
            KEY `idx_page_lang` (`page_id`, `lang_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];
