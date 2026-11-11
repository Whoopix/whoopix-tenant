<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * `mod_pages` is a per-language translation row; `page_id` groups
 * translations and `lang_id` picks the language variant.
 */
final class PageRepository extends AbstractRepository
{
    protected string $table = 'mod_pages';

    public function findByPageAndLang(int $pageId, int $langId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `page_id` = :pid AND `lang_id` = :lid
             LIMIT 1",
            ['pid' => $pageId, 'lid' => $langId],
        );
    }

    public function findByLink(string $plink, int $langId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `plink` = :l AND `lang_id` = :lid
             LIMIT 1",
            ['l' => $plink, 'lid' => $langId],
        );
    }
}
