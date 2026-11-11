<?php

declare(strict_types=1);

namespace App\Repository;

final class LicenseRepository extends AbstractRepository
{
    protected string $table = 'license';

    public function primary(): ?object
    {
        return $this->find(1);
    }
}
