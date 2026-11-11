<?php
return [
    'name' => 'Add pic and slide columns to mod_pages_list',
    'statements' => [
        "ALTER TABLE `mod_pages_list` ADD COLUMN IF NOT EXISTS `pic`   VARCHAR(255) NOT NULL DEFAULT '' AFTER `title`",
        "ALTER TABLE `mod_pages_list` ADD COLUMN IF NOT EXISTS `slide` TINYINT(1) NOT NULL DEFAULT 0 AFTER `access`",
    ],
];
