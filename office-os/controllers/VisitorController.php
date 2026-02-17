<?php

declare(strict_types=1);

class VisitorController
{
    public static function fetchAll(PDO $pdo, ?int $companyId = null): array
    {
        $sql = 'SELECT * FROM visitors';
        $params = [];

        if ($companyId !== null) {
            $sql .= ' WHERE company_id = ?';
            $params[] = $companyId;
        }

        $sql .= ' ORDER BY arrival_time DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
