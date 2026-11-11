<?php
return [
    'name' => 'Rename page_id to pages1_id in mod_pages1 (fix FK naming)',
    'statements' => [
        "ALTER TABLE `mod_pages1` CHANGE `page_id` `pages1_id` INT(11) NOT NULL DEFAULT 0",
    ],
];
