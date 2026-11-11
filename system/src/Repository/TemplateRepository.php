<?php

declare(strict_types=1);

namespace App\Repository;

final class TemplateRepository extends AbstractRepository
{
    protected string $table = 'templates';

    public function findByFile(string $file): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `file` = :f LIMIT 1",
            ['f' => $file],
        );
    }
}
