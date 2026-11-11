<?php
return [
    'name' => 'Add auto-increment id as primary key to mod_language_label',
    'statements' => [
        "ALTER TABLE `mod_language_label`
            ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST",
    ],
];
