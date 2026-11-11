<?php

declare(strict_types=1);

namespace App\Repository;

final class MemberRepository extends AbstractRepository
{
    protected string $table = 'mod_members';

    public function findByEmail(string $email): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `email` = :email LIMIT 1",
            ['email' => $email],
        );
    }

    public function findByUsername(string $username): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `username` = :u LIMIT 1",
            ['u' => $username],
        );
    }

    /**
     * Legacy credentials check. The `pass` column stores the value
     * exactly as legacy code wrote it (historically sha1/md5 — no
     * hash upgrade is done here).
     */
    public function findByCredentials(string $login, string $pass): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}`
             WHERE (`email` = :login OR `username` = :login)
               AND `pass` = :pass
             LIMIT 1",
            ['login' => $login, 'pass' => $pass],
        );
    }
}
