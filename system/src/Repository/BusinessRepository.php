<?php

declare(strict_types=1);

namespace App\Repository;

final class BusinessRepository extends AbstractRepository
{
    protected string $table = 'mod_biz';

    /** @return array<int,object> */
    public function findByCategory(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `cat_id` = :cid ORDER BY `name`",
            ['cid' => $categoryId],
        );
    }

    /** @return array<int,object> */
    public function findActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `active` = 1 ORDER BY `name`",
        );
    }

    public function findByEmail(string $email): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `email` = :e LIMIT 1",
            ['e' => $email],
        );
    }

    public function findByCredentials(string $email, string $pass): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `email` = :e AND `pass` = :p LIMIT 1",
            ['e' => $email, 'p' => $pass],
        );
    }
}
