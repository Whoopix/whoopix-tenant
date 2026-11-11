<?php
return [
    'name' => 'Add in_menu column to mod_modules',
    'statements' => [
        "ALTER TABLE `mod_modules`
            ADD COLUMN `in_menu` TINYINT(1) NOT NULL DEFAULT 0",
    ],
];
