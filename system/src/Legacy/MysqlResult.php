<?php

declare(strict_types=1);

namespace App\Legacy;

use PDOStatement;

/**
 * Bridge between legacy mysql_* calls and PDO. Buffers rows so
 * mysql_num_rows / mysql_data_seek / multiple fetch passes work.
 */
final class MysqlResult
{
    /** @var array<int,array<int|string,mixed>> */
    private array $rows = [];
    private int $cursor = 0;
    private int $affected = 0;
    private bool $isSelect = false;

    public function __construct(PDOStatement $stmt)
    {
        $this->affected = $stmt->rowCount();
        if ($stmt->columnCount() > 0) {
            $this->isSelect = true;
            $this->rows = $stmt->fetchAll(\PDO::FETCH_BOTH) ?: [];
        }
    }

    public function numRows(): int
    {
        return $this->isSelect ? count($this->rows) : 0;
    }

    public function affectedRows(): int
    {
        return $this->affected;
    }

    /** @return array<int|string,mixed>|false */
    public function fetchAssoc(): array|false
    {
        $row = $this->rows[$this->cursor] ?? null;
        if ($row === null) return false;
        $this->cursor++;
        $assoc = [];
        foreach ($row as $k => $v) {
            if (is_string($k)) $assoc[$k] = $v;
        }
        return $assoc;
    }

    /** @return array<int|string,mixed>|false */
    public function fetchArray(int $mode = MYSQL_BOTH): array|false
    {
        $row = $this->rows[$this->cursor] ?? null;
        if ($row === null) return false;
        $this->cursor++;
        return match ($mode) {
            MYSQL_ASSOC => array_filter($row, 'is_string', ARRAY_FILTER_USE_KEY),
            MYSQL_NUM   => array_filter($row, 'is_int', ARRAY_FILTER_USE_KEY),
            default     => $row,
        };
    }

    /** @return array<int,mixed>|false */
    public function fetchRow(): array|false
    {
        $row = $this->rows[$this->cursor] ?? null;
        if ($row === null) return false;
        $this->cursor++;
        return array_values(array_filter($row, 'is_int', ARRAY_FILTER_USE_KEY));
    }

    public function fetchObject(): object|false
    {
        $row = $this->fetchAssoc();
        return $row === false ? false : (object) $row;
    }

    public function result(int $row, int|string $field = 0): mixed
    {
        if (!isset($this->rows[$row])) return false;
        return $this->rows[$row][$field] ?? false;
    }

    public function seek(int $row): bool
    {
        if ($row < 0 || $row >= count($this->rows)) return false;
        $this->cursor = $row;
        return true;
    }
}
