<?php

declare(strict_types=1);

namespace App\Repository;

final class EventTypeRepository extends AbstractRepository
{
    protected string $table = 'mod_eventstypes';

    /** @return array<int,object> */
    public function ordered(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY `order`",
        );
    }
}
