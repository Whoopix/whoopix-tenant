<?php

declare(strict_types=1);

namespace App\Repository;

final class ContactConfigRepository extends AbstractRepository
{
    protected string $table = 'contact_config';

    public function saleEmail(): string
    {
        return (string) $this->db->fetchColumn(
            "SELECT `email` FROM `{$this->table}` WHERE `id` = 1",
        );
    }
}
