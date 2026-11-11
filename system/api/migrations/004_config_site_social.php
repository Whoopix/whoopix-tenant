<?php
return [
    'name' => 'Add social/float columns to config_site',
    'statements' => [
        "ALTER TABLE `config_site` ADD COLUMN IF NOT EXISTS `facebook` text DEFAULT NULL AFTER `description`",
        "ALTER TABLE `config_site` ADD COLUMN IF NOT EXISTS `youtube`  text DEFAULT NULL AFTER `facebook`",
        "ALTER TABLE `config_site` ADD COLUMN IF NOT EXISTS `whatsapp` varchar(50) DEFAULT NULL AFTER `youtube`",
        "ALTER TABLE `config_site` ADD COLUMN IF NOT EXISTS `float`    tinyint(1) NOT NULL DEFAULT 0 AFTER `whatsapp`",
    ],
];
