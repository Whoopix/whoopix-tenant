<?php

declare(strict_types=1);

namespace App\Repository;

final class AdminRepository extends AbstractRepository
{
    protected string $table = 'admins';

    public function findByUsername(string $username): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `username` = :u LIMIT 1",
            ['u' => $username],
        );
    }

    public function findByCredentials(string $username, string $passwordHash): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE `username` = :u AND `password` = :p LIMIT 1",
            ['u' => $username, 'p' => $passwordHash],
        );
    }
}
