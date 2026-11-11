<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * config_logs columns: id, ip, time (unix ts as text), user, log.
 */
final class LogRepository extends AbstractRepository
{
    protected string $table = 'config_logs';

    public function record(string $user, string $log, ?string $ip = null): string
    {
        return $this->create([
            'ip'   => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? ''),
            'time' => (string) time(),
            'user' => $user,
            'log'  => $log,
        ]);
    }

    /** @return array<int,object> */
    public function forUser(string $user, int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `user` = :u ORDER BY `id` DESC LIMIT " . (int) $limit,
            ['u' => $user],
        );
    }
}
