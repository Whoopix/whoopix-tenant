<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Thin procedural helpers wrapping the PDO singleton.
 * Kept in Legacy/ because they exist to ease the transition
 * from mysql_* code. New code should use repositories.
 */

if (!function_exists('__db')) {
    function __db(): Database
    {
        return Database::getInstance();
    }
}

if (!function_exists('db_query')) {
    /**
     * Drop-in replacement for mysql_query with the same caller shape.
     * Returns a PDOStatement so existing `while ($r = $q->fetch(...))` loops work.
     */
    function db_query(string $sql, array $params = []): \PDOStatement|false
    {
        try {
            $pdo = __db()->pdo();
            if ($params === []) {
                $stmt = $pdo->query($sql);
                return $stmt === false ? false : $stmt;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\Throwable $e) {
            $GLOBALS['__db_last_error'] = $e->getMessage();
            return false;
        }
    }
}

if (!function_exists('db_fetch_assoc')) {
    function db_fetch_assoc(\PDOStatement|false|null $stmt): array|false
    {
        if (!$stmt instanceof \PDOStatement) return false;
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }
}

if (!function_exists('db_fetch_array')) {
    function db_fetch_array(\PDOStatement|false|null $stmt, int $mode = \PDO::FETCH_BOTH): array|false
    {
        if (!$stmt instanceof \PDOStatement) return false;
        $row = $stmt->fetch($mode);
        return $row === false ? false : $row;
    }
}

if (!function_exists('db_fetch_row')) {
    function db_fetch_row(\PDOStatement|false|null $stmt): array|false
    {
        if (!$stmt instanceof \PDOStatement) return false;
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        return $row === false ? false : $row;
    }
}

if (!function_exists('db_fetch_object')) {
    function db_fetch_object(\PDOStatement|false|null $stmt): object|false
    {
        if (!$stmt instanceof \PDOStatement) return false;
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        return $row === false ? false : $row;
    }
}

if (!function_exists('db_num_rows')) {
    /**
     * PDO::rowCount on MySQL returns the number of rows in the
     * result set when the default buffered query mode is used
     * (ATTR_EMULATE_PREPARES=false still returns buffered rows
     * for SELECT on mysqlnd).
     */
    function db_num_rows(\PDOStatement|false|null $stmt): int
    {
        if (!$stmt instanceof \PDOStatement) return 0;
        return $stmt->rowCount();
    }
}

if (!function_exists('db_insert_id')) {
    function db_insert_id(): int|string
    {
        $id = __db()->pdo()->lastInsertId();
        return is_numeric($id) ? (int) $id : $id;
    }
}

if (!function_exists('db_affected_rows')) {
    function db_affected_rows(\PDOStatement|false|null $stmt = null): int
    {
        if ($stmt instanceof \PDOStatement) return $stmt->rowCount();
        return 0;
    }
}

if (!function_exists('db_escape')) {
    /**
     * Legacy escape helper. NEW CODE SHOULD USE PREPARED STATEMENTS.
     * Returned value is already suitable for inline SQL interpolation
     * (no surrounding quotes added).
     */
    function db_escape(?string $value): string
    {
        if ($value === null) return '';
        $quoted = __db()->pdo()->quote($value);
        return substr($quoted, 1, -1);
    }
}

if (!function_exists('db_error')) {
    function db_error(): string
    {
        return (string) ($GLOBALS['__db_last_error'] ?? '');
    }
}

if (!function_exists('db_free_result')) {
    function db_free_result(mixed $stmt = null): bool
    {
        if ($stmt instanceof \PDOStatement) {
            $stmt->closeCursor();
        }
        return true;
    }
}

if (!function_exists('db_data_seek')) {
    /**
     * Not supported directly in PDO. Emulated by buffering + reseek
     * via explicit fetch all + index. Callers that need this should
     * migrate to fetchAll() instead.
     */
    function db_data_seek(mixed $stmt, int $offset): bool
    {
        return false;
    }
}
