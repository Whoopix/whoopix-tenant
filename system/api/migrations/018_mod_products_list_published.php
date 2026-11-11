<?php
/**
 * Add published flag to mod_products_list for lab catalog filtering.
 */
return [
    'name' => 'Lab catalog: mod_products_list.published column',
    'statements' => [
        "ALTER TABLE `mod_products_list` ADD COLUMN `published` TINYINT(4) NOT NULL DEFAULT 1",
    ],
];
