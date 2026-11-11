<?php
/**
 * Team members — bilingual columns (HE + EN) + contact fields.
 */
return [
    'name' => 'Team module: bilingual fields on mod_team_members',
    'statements' => [
        "ALTER TABLE `mod_team_members`
            ADD COLUMN IF NOT EXISTS `name_en` varchar(255) NOT NULL DEFAULT '' AFTER `name`,
            ADD COLUMN IF NOT EXISTS `role_title_en` varchar(255) NOT NULL DEFAULT '' AFTER `role_title`,
            ADD COLUMN IF NOT EXISTS `bio_he` text DEFAULT NULL AFTER `role_title_en`,
            ADD COLUMN IF NOT EXISTS `bio_en` text DEFAULT NULL AFTER `bio_he`,
            ADD COLUMN IF NOT EXISTS `email` varchar(255) NOT NULL DEFAULT '' AFTER `photo`,
            ADD COLUMN IF NOT EXISTS `phone` varchar(64) NOT NULL DEFAULT '' AFTER `email`",
    ],
];
