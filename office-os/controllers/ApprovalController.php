<?php

declare(strict_types=1);

class ApprovalController
{
    public static function fetchAll(PDO $pdo, ?int $companyId = null): array
    {
        $sql = "
            SELECT a.*, u.name AS requester_name, u.department
            FROM approvals a
            JOIN users u ON a.requester_id = u.id
        ";

        $params = [];
        if ($companyId !== null) {
            $sql .= ' WHERE a.company_id = ?';
            $params[] = $companyId;
        }

        $sql .= ' ORDER BY a.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function process(PDO $pdo, int $id, string $status, ?int $companyId = null): bool
    {
        $sql = 'UPDATE approvals SET status = ?, updated_at = NOW() WHERE id = ?';
        $params = [$status, $id];

        if ($companyId !== null) {
            $sql .= ' AND company_id = ?';
            $params[] = $companyId;
        }

        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }
}
