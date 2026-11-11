<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOStatement;

interface ConnectionInterface
{
    public function pdo(): PDO;

    /** @param array<string,mixed> $params */
    public function run(string $sql, array $params = []): PDOStatement;

    /** @param array<string,mixed> $params */
    public function fetch(string $sql, array $params = []): ?object;

    /**
     * @param array<string,mixed> $params
     * @return array<int,object>
     */
    public function fetchAll(string $sql, array $params = []): array;

    /** @param array<string,mixed> $params */
    public function fetchColumn(string $sql, array $params = []): mixed;

    /** @param array<string,mixed> $data */
    public function insert(string $table, array $data): string;

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $where
     */
    public function update(string $table, array $data, array $where): int;

    /** @param array<string,mixed> $where */
    public function delete(string $table, array $where): int;

    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
}
