<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/RoomController.php';
require_once __DIR__ . '/../controllers/ApprovalController.php';
require_once __DIR__ . '/../controllers/VisitorController.php';

$pdo = getPDO();
$action = $_GET['action'] ?? '';

function getCurrentUser(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT id, company_id FROM users WHERE id = ?');
    $stmt->execute([$userId]);

    return $stmt->fetch() ?: null;
}

function writeAuditLog(PDO $pdo, int $userId, string $action): void
{
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
}

switch ($action) {
    case 'login':
        startSessionIfNeeded();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['company_id'] = isset($user['company_id']) ? (int) $user['company_id'] : null;
            writeAuditLog($pdo, (int) $user['id'], 'User login');

            echo json_encode(['success' => true]);
            break;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        break;

    case 'logout':
        startSessionIfNeeded();

        if (isset($_SESSION['user_id'])) {
            writeAuditLog($pdo, (int) $_SESSION['user_id'], 'User logout');
        }

        $_SESSION = [];
        session_destroy();

        echo json_encode(['success' => true]);
        break;

    case 'intelligent_fetch':
        checkAuth();

        $user = getCurrentUser($pdo, (int) $_SESSION['user_id']);
        $companyId = $user['company_id'] ?? null;

        $rooms = RoomController::intelligentFetch($pdo, $companyId !== null ? (int) $companyId : null);
        $approvals = ApprovalController::fetchAll($pdo, $companyId !== null ? (int) $companyId : null);
        $visitors = VisitorController::fetchAll($pdo, $companyId !== null ? (int) $companyId : null);

        writeAuditLog($pdo, (int) $_SESSION['user_id'], 'Fetched dashboard intelligence');

        echo json_encode([
            'success' => true,
            'rooms' => $rooms,
            'approvals' => $approvals,
            'visitors' => $visitors,
        ]);
        break;

    case 'process_approval':
        checkAuth();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $status = $_POST['status'] ?? '';

        if ($id <= 0 || !in_array($status, ['Approved', 'Rejected'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid approval payload']);
            exit;
        }

        $companyId = isset($_SESSION['company_id']) ? (int) $_SESSION['company_id'] : null;
        $updated = ApprovalController::process($pdo, $id, $status, $companyId);

        if ($updated) {
            writeAuditLog($pdo, (int) $_SESSION['user_id'], "Approval #{$id} marked {$status}");
            echo json_encode(['success' => true]);
            break;
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update approval']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
