<?php
return [
    'name' => 'Create mod_modules_dynamic table',
    'statements' => [
        "CREATE TABLE IF NOT EXISTS `mod_modules_dynamic` (
            `id`        INT(11)      NOT NULL AUTO_INCREMENT,
            `mod_id`    INT(11)      NOT NULL,
            `col_id`    INT(11)      NOT NULL,
            `col_name`  VARCHAR(64)  NOT NULL DEFAULT '',
            `col_title` VARCHAR(255) NOT NULL DEFAULT '',
            `col_type`  INT(11)      NOT NULL DEFAULT 1,
            `lang_id`   INT(11)      NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `idx_mod_lang` (`mod_id`, `lang_id`),
            KEY `idx_col_id`   (`col_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];
