<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * `mod_blocks` is the per-language variant; `block_id` groups
 * translations of the same block.
 */
final class BlockRepository extends AbstractRepository
{
    protected string $table = 'mod_blocks';

    public function findByBlockAndLang(int $blockId, int $langId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `block_id` = :bid AND `lang_id` = :lid LIMIT 1",
            ['bid' => $blockId, 'lid' => $langId],
        );
    }
}
