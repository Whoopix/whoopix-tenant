<?php

declare(strict_types=1);

/**
 * Legacy mysql_* shim routed through App\Core\Database (PDO).
 * Lets unmigrated files keep working during the migration.
 * Remove after all callers are converted to repositories.
 */

use App\Core\Database;
use App\Legacy\MysqlResult;

if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', 1);
if (!defined('MYSQL_NUM'))   define('MYSQL_NUM', 2);
if (!defined('MYSQL_BOTH'))  define('MYSQL_BOTH', 3);

if (!function_exists('mysql_query')) {
    function mysql_query(string $sql, mixed $link = null): MysqlResult|false
    {
        try {
            $pdo = Database::getInstance()->pdo();
            $stmt = $pdo->query($sql);
            if ($stmt === false) return false;
            return new MysqlResult($stmt);
        } catch (\Throwable $e) {
            $GLOBALS['__mysql_last_error'] = $e->getMessage();
            $GLOBALS['__mysql_last_errno'] = (int) $e->getCode();
            return false;
        }
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc(mixed $result): array|false|null
    {
        return $result instanceof MysqlResult ? $result->fetchAssoc() : false;
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array(mixed $result, int $mode = MYSQL_BOTH): array|false|null
    {
        return $result instanceof MysqlResult ? $result->fetchArray($mode) : false;
    }
}

if (!function_exists('mysql_fetch_row')) {
    function mysql_fetch_row(mixed $result): array|false|null
    {
        return $result instanceof MysqlResult ? $result->fetchRow() : false;
    }
}

if (!function_exists('mysql_fetch_object')) {
    function mysql_fetch_object(mixed $result): object|false|null
    {
        return $result instanceof MysqlResult ? $result->fetchObject() : false;
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows(mixed $result): int|false
    {
        return $result instanceof MysqlResult ? $result->numRows() : false;
    }
}

if (!function_exists('mysql_affected_rows')) {
    function mysql_affected_rows(mixed $link = null): int
    {
        return (int) (Database::getInstance()->pdo()->query('SELECT ROW_COUNT()')?->fetchColumn() ?? 0);
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id(mixed $link = null): int|string
    {
        $id = Database::getInstance()->pdo()->lastInsertId();
        return is_numeric($id) ? (int) $id : $id;
    }
}

if (!function_exists('mysql_result')) {
    function mysql_result(mixed $result, int $row, int|string $field = 0): mixed
    {
        return $result instanceof MysqlResult ? $result->result($row, $field) : false;
    }
}

if (!function_exists('mysql_data_seek')) {
    function mysql_data_seek(mixed $result, int $row): bool
    {
        return $result instanceof MysqlResult ? $result->seek($row) : false;
    }
}

if (!function_exists('mysql_free_result')) {
    function mysql_free_result(mixed $result): bool
    {
        return true;
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string(string $str, mixed $link = null): string
    {
        $pdo = Database::getInstance()->pdo();
        $quoted = $pdo->quote($str);
        // Strip the surrounding quotes that PDO::quote() adds to match
        // legacy mysql_real_escape_string semantics.
        return substr($quoted, 1, -1);
    }
}

if (!function_exists('mysql_escape_string')) {
    function mysql_escape_string(string $str): string
    {
        return mysql_real_escape_string($str);
    }
}

if (!function_exists('mysql_connect')) {
    function mysql_connect(mixed ...$args): bool
    {
        Database::getInstance();
        return true;
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db(string $db, mixed $link = null): bool
    {
        return true; // selected via DSN in Database singleton
    }
}

if (!function_exists('mysql_close')) {
    function mysql_close(mixed $link = null): bool
    {
        return true;
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error(mixed $link = null): string
    {
        return (string) ($GLOBALS['__mysql_last_error'] ?? '');
    }
}

if (!function_exists('mysql_errno')) {
    function mysql_errno(mixed $link = null): int
    {
        return (int) ($GLOBALS['__mysql_last_errno'] ?? 0);
    }
}

if (!function_exists('mysql_set_charset')) {
    function mysql_set_charset(string $charset, mixed $link = null): bool
    {
        try {
            Database::getInstance()->pdo()->exec("SET NAMES {$charset}");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
