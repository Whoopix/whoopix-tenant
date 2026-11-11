<?php

declare(strict_types=1);

namespace App\Repository;

final class FeedbackRepository extends AbstractRepository
{
    protected string $table = 'mod_feedback_list';

    /** @return array<int,object> */
    public function forProduct(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `product_id` = :p ORDER BY `id` DESC",
            ['p' => $productId],
        );
    }

    /** @return array<int,object> */
    public function byMember(int $memberId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `member_id` = :m ORDER BY `id` DESC",
            ['m' => $memberId],
        );
    }
}
