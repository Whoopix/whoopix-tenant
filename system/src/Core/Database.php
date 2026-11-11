<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\ConnectionInterface;
use PDO;
use PDOStatement;
use RuntimeException;

final class Database implements ConnectionInterface
{
    private static ?self $instance = null;
    private readonly PDO $pdo;

    private function __construct(Config $cfg)
    {
        $this->pdo = new PDO(
            $cfg->dsn(),
            $cfg->user,
            $cfg->pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$cfg->charset}",
            ],
        );
    }

    public static function getInstance(?Config $cfg = null): self
    {
        return self::$instance ??= new self($cfg ?? Config::fromLegacy());
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?object
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        $val = $this->run($sql, $params)->fetchColumn();
        return $val === false ? null : $val;
    }

    public function insert(string $table, array $data): string
    {
        if ($data === []) {
            throw new RuntimeException('insert(): empty data');
        }
        $cols = array_keys($data);
        $placeholders = array_map(static fn(string $c) => ":{$c}", $cols);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`,`', $cols),
            implode(',', $placeholders),
        );
        $this->run($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        if ($data === [] || $where === []) {
            throw new RuntimeException('update(): data and where required');
        }
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            $set[] = "`{$col}` = :set_{$col}";
            $params["set_{$col}"] = $val;
        }
        $cond = [];
        foreach ($where as $col => $val) {
            $cond[] = "`{$col}` = :where_{$col}";
            $params["where_{$col}"] = $val;
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $set),
            implode(' AND ', $cond),
        );
        return $this->run($sql, $params)->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        if ($where === []) {
            throw new RuntimeException('delete(): where required');
        }
        $cond = [];
        $params = [];
        foreach ($where as $col => $val) {
            $cond[] = "`{$col}` = :{$col}";
            $params[$col] = $val;
        }
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, implode(' AND ', $cond));
        return $this->run($sql, $params)->rowCount();
    }

    public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->commit(); }
    public function rollBack(): bool { return $this->pdo->rollBack(); }

    private function __clone() {}
    public function __wakeup(): void { throw new RuntimeException('Cannot unserialize singleton'); }
}
