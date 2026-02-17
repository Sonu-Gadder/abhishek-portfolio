<?php

declare(strict_types=1);

function startSessionIfNeeded(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function checkAuth(): void
{
    startSessionIfNeeded();

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}
