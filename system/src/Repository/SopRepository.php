<?php

declare(strict_types=1);

namespace App\Repository;

final class SopRepository extends AbstractRepository
{
    protected string $table = 'mod_sop';

    /** @return array<int,object> */
    public function findByCategory(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `cat_id` = :cid ORDER BY `order`",
            ['cid' => $categoryId],
        );
    }

    /** @return array<int,object> */
    public function children(int $parentId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `parent` = :p ORDER BY `order`",
            ['p' => $parentId],
        );
    }
}
