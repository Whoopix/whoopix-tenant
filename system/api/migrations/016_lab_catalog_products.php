<?php
/**
 * Lab catalog integration — enable products module, site_kind=catalog for CMS sidebar.
 * Products/tests are managed via mod_products_categories_list + mod_products_list.
 */
return [
    'name' => 'Lab catalog: enable products module + site_kind catalog',
    'statements' => [

        "ALTER TABLE `mod_modules` ADD COLUMN IF NOT EXISTS `in_menu` TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE `mod_modules` ADD COLUMN IF NOT EXISTS `full_mod` VARCHAR(64) NOT NULL DEFAULT ''",

        "INSERT IGNORE INTO `mod_modules` (`module`, `name_he`, `name_en`, `status`, `order`, `in_menu`)
            VALUES ('products', 'קטלוג מוצרים', 'Products', 1, 20, 0)",

        "UPDATE `mod_modules` SET `status` = 1, `in_menu` = 0, `name_he` = 'קטלוג מוצרים'
            WHERE `module` = 'products'",

        "INSERT INTO `mod_settings` (`module`, `setting_key`, `setting_value`)
            VALUES ('site', 'site_kind', 'catalog')
            ON DUPLICATE KEY UPDATE `setting_value` = 'catalog'",
    ],
];
