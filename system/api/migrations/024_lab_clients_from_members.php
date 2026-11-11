<?php
/**
 * Bridge mod_members → mod_lab_clients for lab tenants (client / Lab).
 * Idempotent: INSERT IGNORE + UPDATE join on email.
 */
return [
    'name' => 'Lab clients bridge from mod_members',
    'statements' => [
        "INSERT IGNORE INTO `mod_lab_clients` (`name`, `email`, `phone`, `mobile`, `username`, `pass`, `stat`, `created_at`)
         SELECT
            CASE
                WHEN TRIM(COALESCE(m.`name`, '')) <> '' THEN TRIM(m.`name`)
                WHEN TRIM(COALESCE(m.`username`, '')) <> '' THEN TRIM(m.`username`)
                ELSE TRIM(m.`email`)
            END,
            TRIM(m.`email`),
            COALESCE(m.`phone`, ''),
            COALESCE(m.`mobile`, ''),
            CASE
                WHEN TRIM(COALESCE(m.`username`, '')) <> '' THEN TRIM(m.`username`)
                ELSE TRIM(m.`email`)
            END,
            COALESCE(m.`pass`, ''),
            CASE WHEN CAST(COALESCE(m.`stat`, 0) AS SIGNED) > 0 THEN 1 ELSE 0 END,
            NOW()
         FROM `mod_members` m
         WHERE TRIM(COALESCE(m.`email`, '')) <> ''
           AND CAST(COALESCE(m.`stat`, 0) AS SIGNED) > 0",

        "UPDATE `mod_lab_clients` c
         INNER JOIN `mod_members` m ON c.`email` = TRIM(m.`email`)
         SET
            c.`name` = CASE WHEN TRIM(COALESCE(m.`name`, '')) <> '' THEN TRIM(m.`name`) ELSE c.`name` END,
            c.`phone` = CASE WHEN TRIM(COALESCE(m.`phone`, '')) <> '' THEN TRIM(m.`phone`) ELSE c.`phone` END,
            c.`mobile` = CASE WHEN TRIM(COALESCE(m.`mobile`, '')) <> '' THEN TRIM(m.`mobile`) ELSE c.`mobile` END,
            c.`username` = CASE WHEN TRIM(COALESCE(m.`username`, '')) <> '' THEN TRIM(m.`username`) ELSE c.`username` END,
            c.`pass` = CASE
                WHEN TRIM(COALESCE(m.`pass`, '')) <> '' AND TRIM(COALESCE(c.`pass`, '')) = '' THEN m.`pass`
                ELSE c.`pass`
            END,
            c.`stat` = CASE WHEN CAST(COALESCE(m.`stat`, 0) AS SIGNED) > 0 THEN 1 ELSE c.`stat` END
         WHERE TRIM(COALESCE(m.`email`, '')) <> ''",
    ],
];
