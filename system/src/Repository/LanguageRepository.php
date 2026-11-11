<?php

declare(strict_types=1);

namespace App\Repository;

final class LanguageRepository extends AbstractRepository
{
    protected string $table = 'mod_languages';

    public function findByCode(string $code): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `code` = :c LIMIT 1",
            ['c' => $code],
        );
    }

    public function defaultLanguage(): ?object
    {
        // `default` is a reserved word.
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `default` = 1 LIMIT 1",
        );
    }
}
