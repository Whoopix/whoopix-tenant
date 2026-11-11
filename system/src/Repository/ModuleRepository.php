<?php

declare(strict_types=1);

namespace App\Repository;

final class ModuleRepository extends AbstractRepository
{
    protected string $table = 'mod_modules';

    /** @return array<int,object> */
    public function findActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `status` = 1 ORDER BY `order`",
        );
    }

    public function findByKey(string $module): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `module` = :m LIMIT 1",
            ['m' => $module],
        );
    }
}
