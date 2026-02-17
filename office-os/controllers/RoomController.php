<?php

declare(strict_types=1);

class RoomController
{
    public static function intelligentFetch(PDO $pdo, ?int $companyId = null): array
    {
        $sql = "
            SELECT *,
            GREATEST(0, (health_score - (usage_count * 0.1))) AS current_health
            FROM rooms
        ";

        $params = [];
        if ($companyId !== null) {
            $sql .= ' WHERE company_id = ?';
            $params[] = $companyId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
