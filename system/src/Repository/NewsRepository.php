<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * `mod_news` is the per-language translation table. Publish
 * metadata (date, image, etc.) lives in `mod_news_list`; use
 * the list repo if you need ordering by time.
 */
final class NewsRepository extends AbstractRepository
{
    protected string $table = 'mod_news';

    /** @return array<int,object> */
    public function forLang(int $langId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `lang_id` = :lid ORDER BY `id` DESC",
            ['lid' => $langId],
        );
    }

    public function findOne(int $newsId, int $langId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `news_id` = :nid AND `lang_id` = :lid LIMIT 1",
            ['nid' => $newsId, 'lid' => $langId],
        );
    }
}
