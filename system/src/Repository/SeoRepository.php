<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * SEO overrides keyed by module + action.
 */
final class SeoRepository extends AbstractRepository
{
    protected string $table = 'mod_seo';

    public function findByModAct(string $mod, string $act, int $langId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `mod` = :m AND `act` = :a AND `lang_id` = :lid
             LIMIT 1",
            ['m' => $mod, 'a' => $act, 'lid' => $langId],
        );
    }
}
