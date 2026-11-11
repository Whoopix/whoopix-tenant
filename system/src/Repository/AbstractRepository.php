<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Database\ConnectionInterface;

/**
 * Base repository. Open/Closed:
 *   - extend for new entities by setting $table / $primaryKey
 *   - add entity-specific queries in child classes
 *   - never modify this base for per-entity behavior
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected readonly ConnectionInterface $db;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    protected function db(): ConnectionInterface
    {
        return $this->db;
    }

    public function find(int|string $id): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1",
            ['id' => $id],
        );
    }

    public function findAll(array $where = [], ?string $orderBy = null, ?int $limit = null): array
    {
        [$clause, $params] = $this->buildWhere($where);
        $sql = "SELECT * FROM `{$this->table}`{$clause}";
        if ($orderBy !== null) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
        }
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): string
    {
        return $this->db->insert($this->table, $data);
    }

    public function updateById(int|string $id, array $data): int
    {
        return $this->db->update($this->table, $data, [$this->primaryKey => $id]);
    }

    public function deleteById(int|string $id): int
    {
        return $this->db->delete($this->table, [$this->primaryKey => $id]);
    }

    public function count(array $where = []): int
    {
        [$clause, $params] = $this->buildWhere($where);
        $val = $this->db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}`{$clause}", $params);
        return (int) $val;
    }

    /**
     * @param array<string,mixed> $where
     * @return array{0:string,1:array<string,mixed>}
     */
    protected function buildWhere(array $where): array
    {
        if ($where === []) {
            return ['', []];
        }
        $cond = [];
        $params = [];
        foreach ($where as $col => $val) {
            $cond[] = "`{$col}` = :{$col}";
            $params[$col] = $val;
        }
        return [' WHERE ' . implode(' AND ', $cond), $params];
    }
}
