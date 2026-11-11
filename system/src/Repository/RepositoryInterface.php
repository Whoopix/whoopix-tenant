<?php

declare(strict_types=1);

namespace App\Repository;

interface RepositoryInterface
{
    public function find(int|string $id): ?object;

    /**
     * @param array<string,mixed> $where
     * @return array<int,object>
     */
    public function findAll(array $where = [], ?string $orderBy = null, ?int $limit = null): array;

    /** @param array<string,mixed> $data */
    public function create(array $data): string;

    /**
     * @param array<string,mixed> $data
     */
    public function updateById(int|string $id, array $data): int;

    public function deleteById(int|string $id): int;

    /** @param array<string,mixed> $where */
    public function count(array $where = []): int;
}
