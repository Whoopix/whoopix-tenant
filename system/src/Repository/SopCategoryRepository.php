<?php

declare(strict_types=1);

namespace App\Repository;

final class SopCategoryRepository extends AbstractRepository
{
    protected string $table = 'mod_sop_cats';

    /** @return array<int,object> */
    public function ordered(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY `order`",
        );
    }
}
