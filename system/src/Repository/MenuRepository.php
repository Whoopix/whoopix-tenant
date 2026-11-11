<?php

declare(strict_types=1);

namespace App\Repository;

final class MenuRepository extends AbstractRepository
{
    protected string $table = 'mod_menu';

    /** @return array<int,object> */
    public function treeForLang(int $langId): array
    {
        // `order` is a reserved word — backtick-quoted.
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}`
             WHERE `lang_id` = :lid
             ORDER BY `parent`, `order`",
            ['lid' => $langId],
        );
    }

    /** @return array<int,object> */
    public function children(int $parentId, int $langId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}`
             WHERE `parent` = :p AND `lang_id` = :lid
             ORDER BY `order`",
            ['p' => $parentId, 'lid' => $langId],
        );
    }
}
